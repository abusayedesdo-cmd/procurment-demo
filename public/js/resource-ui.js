/**
 * Generic, config-driven list + create page for the simpler procurement
 * resources (Meeting, RFQ, Tender Schedule, Evaluation reports, Contract
 * Award, Work Order, etc). One engine, many configs — see
 * module-configs.js for the per-resource field definitions.
 *
 * Usage (called from resources/views/modules/show.blade.php):
 *   initResourcePage(MODULE_CONFIGS['meetings']);
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
    root.innerHTML = '<p class="muted">লোড হচ্ছে...</p>';

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
                        <option value="">-- বাছাই করুন --</option>${opts}
                    </select>
                </div>`;
        }

        if (field.type === 'enum') {
            const opts = field.options.map(o => `<option value="${o}">${o}</option>`).join('');
            return `
                <div class="form-field">
                    <label for="${id}">${field.label}</label>
                    <select id="${id}" ${requiredAttr}>
                        <option value="">-- বাছাই করুন --</option>${opts}
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

    async function loadList() {
        const tbody = document.getElementById('listBody');
        const colCount = config.listColumns.length + (config.rowActions ? 1 : 0);
        tbody.innerHTML = `<tr><td colspan="${colCount}" class="muted">লোড হচ্ছে...</td></tr>`;
        try {
            const { data } = await api.get(`${config.apiPath}?per_page=50`);
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="${colCount}" class="muted">কোনো তথ্য পাওয়া যায়নি।</td></tr>`;
                return;
            }
            tbody.innerHTML = data.map(row => {
                const cells = config.listColumns.map(c => `<td>${formatCell(getByPath(row, c.key))}</td>`).join('');
                const actions = config.rowActions
                    ? `<td>${config.rowActions.map(a => `<a href="${a.hrefBuilder(row)}" class="btn secondary" style="padding:.3rem .6rem; font-size:.8rem;" target="_blank">${a.label}</a>`).join(' ')}</td>`
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

    root.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h1 style="margin:0;">${config.title}</h1>
            <a href="/modules" class="btn secondary">← সব মডিউল</a>
        </div>
        <div id="errorBoxPlaceholder"></div>

        <div class="card">
            <h3>তালিকা</h3>
            <table>
                <thead><tr>${config.listColumns.map(c => `<th>${c.label}</th>`).join('')}${config.rowActions ? '<th>Action</th>' : ''}</tr></thead>
                <tbody id="listBody"><tr><td colspan="${config.listColumns.length + (config.rowActions ? 1 : 0)}" class="muted">লোড হচ্ছে...</td></tr></tbody>
            </table>
        </div>

        <div class="card">
            <h3>নতুন যোগ করুন</h3>
            <form id="resourceForm">
                <div class="form-grid">${visibleFields.map(renderField).join('')}</div>
                <button type="submit" class="btn" style="margin-top:1rem;">সংরক্ষণ করুন</button>
            </form>
        </div>
    `;

    document.getElementById('errorBoxPlaceholder').replaceWith(errorBox);
    document.getElementById('resourceForm').addEventListener('submit', handleSubmit);

    await loadList();
}
