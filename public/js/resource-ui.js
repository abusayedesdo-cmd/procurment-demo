/**
 * Generic, config-driven list + create page for the simpler procurement
 * resources (Meeting, RFQ, Tender Schedule, Evaluation reports, Contract
 * Award, Work Order, etc). One engine, many configs — see
 * module-configs.js for the per-resource field definitions.
 *
 * Usage (called from resources/views/modules/show.blade.php):
 *   initResourcePage(MODULE_CONFIGS['meetings']);
 *
 * Note: the page header (module title + "All Modules" back link) is
 * rendered by modules/show.blade.php itself — this script only fills
 * #resourceRoot with the records table and the create form.
 */

function getByPath(obj, path) {
    return path.split('.').reduce((o, k) => (o == null ? undefined : o[k]), obj);
}

function formatCell(value) {
    if (value === null || value === undefined || value === '') return '<span class="muted">-</span>';
    if (typeof value === 'boolean') return value ? '✅' : '—';
    return value;
}

async function initResourcePage(config) {
    const root = document.getElementById('resourceRoot');
    root.innerHTML = '<div class="state-panel">Loading…</div>';

    const errorBox = document.createElement('div');
    errorBox.className = 'error-box';
    errorBox.style.display = 'none';

    const selectCache = {};

    function showError(err) {
        errorBox.textContent = err.message || String(err);
        errorBox.style.display = 'block';
    }

    // ---- 1. Load every 'select' field's options FIRST, before drawing
    //         anything — this is what removes the "empty dropdown" bug. ----
    async function loadSelectOptions() {
        const selectFields = config.formFields.filter(f => f.type === 'select');
        await Promise.all(selectFields.map(async f => {
            try {
                const sep = f.source.includes('?') ? '&' : '?';
                const { data } = await api.get(`${f.source}${sep}per_page=500`);
                selectCache[f.name] = data;
            } catch (e) {
                selectCache[f.name] = [];
            }
        }));
    }

    function optionLabel(field, record) {
        if (typeof field.labelField === 'function') return field.labelField(record);
        return getByPath(record, field.labelField) ?? `#${record.id}`;
    }

    // Fields that take up a full row on their own (not squeezed into the
    // narrow grid columns) — textarea and checkbox read badly at ~160px.
    function isFullWidth(field) {
        return field.type === 'textarea' || field.type === 'checkbox';
    }

    function renderField(field) {
        const id = `field_${field.name}`;
        const requiredAttr = field.required ? 'required' : '';

        if (field.type === 'select') {
            const opts = (selectCache[field.name] || [])
                .map(r => `<option value="${r.id}">${optionLabel(field, r)}</option>`).join('');
            return `
                <div class="form-field">
                    <label for="${id}">${field.label}</label>
                    <select id="${id}" ${requiredAttr}>
                        <option value="">-- Select --</option>${opts}
                    </select>
                </div>`;
        }

        if (field.type === 'enum') {
            const opts = field.options.map(o => `<option value="${o}">${o}</option>`).join('');
            return `
                <div class="form-field">
                    <label for="${id}">${field.label}</label>
                    <select id="${id}" ${requiredAttr}>
                        <option value="">-- Select --</option>${opts}
                    </select>
                </div>`;
        }

        if (field.type === 'textarea') {
            return `
                <div class="form-field full-width">
                    <label for="${id}">${field.label}</label>
                    <textarea id="${id}" rows="2" ${requiredAttr}></textarea>
                </div>`;
        }

        if (field.type === 'checkbox') {
            return `
                <div class="form-field full-width checkbox-field">
                    <label for="${id}"><input type="checkbox" id="${id}"> ${field.label}</label>
                </div>`;
        }

        const htmlType = { date: 'date', datetime: 'datetime-local', number: 'number' }[field.type] || 'text';
        return `
            <div class="form-field">
                <label for="${id}">${field.label}</label>
                <input type="${htmlType}" id="${id}" ${requiredAttr} ${field.step ? `step="${field.step}"` : ''}>
            </div>`;
    }

    function fieldValue(field) {
        if (field.type === 'currentUser') return window.currentUserId ?? null;
        const el = document.getElementById(`field_${field.name}`);
        if (field.type === 'checkbox') return el.checked;
        if (field.type === 'number') return el.value === '' ? null : parseFloat(el.value);
        if (field.type === 'datetime' && el.value) return el.value.replace('T', ' ') + ':00';
        return el.value === '' ? null : el.value;
    }

    // Wires up any `field.autofillFrom = { field, property }` relationships:
    // when the named source <select> changes, look up the chosen record in
    // its selectCache and copy `property` into this field. Runs after the
    // form is drawn, since it needs the rendered <select>/<input> elements.
    function wireAutofill() {
        config.formFields.forEach(field => {
            if (!field.autofillFrom) return;
            const sourceEl = document.getElementById(`field_${field.autofillFrom.field}`);
            const targetEl = document.getElementById(`field_${field.name}`);
            if (!sourceEl || !targetEl) return;
            sourceEl.addEventListener('change', () => {
                const records = selectCache[field.autofillFrom.field] || [];
                const record = records.find(r => String(r.id) === sourceEl.value);
                targetEl.value = record ? (getByPath(record, field.autofillFrom.property) ?? '') : '';
            });
        });
    }

    // Renders one row-action link. `a.download === true` forces a real
    // file download (adds the `download` attribute, no new tab); anything
    // else opens in a new tab so the browser can preview it inline
    // (PDF/image) — that's the Download vs Preview distinction.
    function renderRowAction(a, row) {
        const href = a.hrefBuilder(row);
        if (!href) return '';
        const attrs = a.download ? 'download' : 'target="_blank" rel="noopener"';
        return `<a href="${href}" class="btn secondary" style="padding:.3rem .6rem; font-size:.8rem;" ${attrs}>${a.label}</a>`;
    }

    async function loadList() {
        const tbody = document.getElementById('listBody');
        const colCount = config.listColumns.length + (config.rowActions ? 1 : 0);
        tbody.innerHTML = `<tr><td colspan="${colCount}" class="muted">Loading…</td></tr>`;
        try {
            const { data } = await api.get(`${config.apiPath}?per_page=50`);
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="${colCount}" class="muted">No records found.</td></tr>`;
                return;
            }
            tbody.innerHTML = data.map(row => {
                const cells = config.listColumns.map(c => `<td>${formatCell(getByPath(row, c.key))}</td>`).join('');
                const actions = config.rowActions
                    ? `<td>${config.rowActions.map(a => renderRowAction(a, row)).join(' ')}</td>`
                    : '';
                return `<tr>${cells}${actions}</tr>`;
            }).join('');
        } catch (err) {
            showError(err);
        }
    }

    async function handleSubmit(e) {
        e.preventDefault();
        errorBox.style.display = 'none';

        const payload = {};
        config.formFields.forEach(f => { payload[f.name] = fieldValue(f); });

        try {
            await api.post(config.apiPath, payload);
            document.getElementById('resourceForm').reset();
            await loadList();
        } catch (err) {
            showError(err);
        }
    }

    // ---- 2. Only NOW draw the page — every select already has its data. ----
    await loadSelectOptions();

    const visibleFields = config.formFields.filter(f => f.type !== 'currentUser');
    const sectionTitleStyle = 'margin:0 0 1rem; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--accent-dark);';

    root.innerHTML = `
        <div id="errorBoxPlaceholder"></div>

        <div class="card">
            <h3 style="${sectionTitleStyle}">Records</h3>
            <div style="overflow-x:auto;">
                <table>
                    <thead><tr>${config.listColumns.map(c => `<th>${c.label}</th>`).join('')}${config.rowActions ? '<th>Action</th>' : ''}</tr></thead>
                    <tbody id="listBody"><tr><td colspan="${config.listColumns.length + (config.rowActions ? 1 : 0)}" class="muted">Loading…</td></tr></tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h3 style="${sectionTitleStyle}">Add New</h3>
            <form id="resourceForm">
                <div class="form-grid">${visibleFields.map(renderField).join('')}</div>
                <button type="submit" class="btn primary" style="margin-top:1rem;">Save</button>
            </form>
        </div>
    `;

    document.getElementById('errorBoxPlaceholder').replaceWith(errorBox);
    document.getElementById('resourceForm').addEventListener('submit', handleSubmit);
    wireAutofill();

    await loadList();
}