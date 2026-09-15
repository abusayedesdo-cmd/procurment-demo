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
    // Laravel date/datetime casts serialize as full ISO timestamps
    // (e.g. "2026-09-12T00:00:00.000000Z"). Plain 'date' columns always
    // carry a 00:00:00 time component — show date only for those. Real
    // 'datetime' columns (e.g. held_at, submitted_at) keep their time,
    // shown with a small gap after the date instead of the raw "T".
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/.test(value)) {
        const [datePart, timePart] = value.split('T');
        const hhmm = timePart.slice(0, 5);
        return hhmm === '00:00'
            ? datePart
            : `${datePart}<span style="margin-left:.5em">${hhmm}</span>`;
    }
    return value;
}

async function initResourcePage(config) {
    const root = document.getElementById('resourceRoot');
    root.innerHTML = '<div class="state-panel">Loading…</div>';

    const errorBox = document.createElement('div');
    errorBox.className = 'error-box';
    errorBox.style.display = 'none';

    const successBox = document.createElement('div');
    successBox.className = 'success-box';
    successBox.style.cssText = 'display:none; background:#F0FDF4; color:#15803D; border:1px solid #BBF7D0; border-radius:8px; padding:.7rem 1rem; margin-bottom:1rem; font-size:.85rem; font-weight:600;';

    const selectCache = {};

    // Modules that declare `splitLogAndForm: true` (today: RFQ/OTM) get a
    // different layout: the "History" page (`?history=1`, historyMode
    // true) shows ONLY the Log table, no form; every other visit shows
    // ONLY the current active-PR record (+ its Preview/Download actions)
    // plus the Add New form — never the full system-wide Log table.
    const historyMode = !!(window.moduleContext && window.moduleContext.historyMode);
    const isSplit = config.splitLogAndForm === true;

    function showError(err) {
        successBox.style.display = 'none';
        errorBox.textContent = err.message || String(err);
        errorBox.style.display = 'block';
    }

    function showSuccess(msg) {
        errorBox.style.display = 'none';
        successBox.textContent = msg;
        successBox.style.display = 'block';
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

    // When a module declares `listFilterField` (e.g. 'procurement_case_id')
    // and the server resolved an active PR for this request (see
    // ModulePageController + ActivePrContext), scope the Log table to
    // just that PR's own records instead of the whole system's history.
    // window.moduleContext.filterValue is null when there's no active PR,
    // or the caller asked for everything via `?history=1` — either way the
    // list is unfiltered, same as before.
    function getListFilterQuery() {
        if (!config.listFilterField) return '';
        const val = window.moduleContext && window.moduleContext.filterValue;
        return val ? `&${config.listFilterField}=${encodeURIComponent(val)}` : '';
    }

    function renderListFilterNotice() {
        const notice = document.getElementById('listFilterNotice');
        if (!notice) return;
        const ctx = window.moduleContext;
        if (!config.listFilterField || !ctx || !ctx.filterValue) { notice.innerHTML = ''; return; }
        const label = ctx.label || 'this record';
        notice.innerHTML = `
            <div style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:8px; padding:.5rem .8rem; margin-bottom:.9rem; font-size:.82rem; display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap;">
                <span>Showing records for <b>${label}</b> only.</span>
                <a href="${ctx.historyUrl}" style="color:#1D4ED8; font-weight:700; text-decoration:none; white-space:nowrap;">View Full History &rarr;</a>
            </div>`;
    }

    // Renders the compact "current record" card used by split modules on
    // their main (non-History) page — same data, no full table chrome,
    // since there's normally at most one matching record (this PR's RFQ).
    function renderCurrentRecordRows(rows) {
        if (!rows.length) {
            return `<p class="muted" style="margin:0;">No record yet for this PR — create one below.</p>`;
        }
        return rows.map(row => {
            const fields = config.listColumns.map(c => `
                <div>
                    <div class="muted" style="font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.15rem;">${c.label}</div>
                    <div style="font-size:.9rem; font-weight:600;">${formatCell(getByPath(row, c.key))}</div>
                </div>`).join('');
            const actions = config.rowActions
                ? `<div style="display:flex; flex-wrap:wrap; gap:.5rem; margin-top:.85rem;">${config.rowActions.map(a => renderRowAction(a, row)).join(' ')}</div>`
                : '';
            return `
                <div style="border:1px solid var(--line); border-radius:10px; padding:1rem 1.1rem; margin-bottom:.85rem;">
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px,1fr)); gap:.75rem;">${fields}</div>
                    ${actions}
                </div>`;
        }).join('');
    }

    async function loadRecords() {
        const ctx = window.moduleContext;

        if (isSplit && !historyMode) {
            const body = document.getElementById('currentRecordBody');
            if (!body) return;
            if (!ctx || !ctx.filterValue) {
                body.innerHTML = `<p class="muted" style="margin:0;">No active PR selected — pick one from PR Receive to see its record here.</p>`;
                return;
            }
            body.innerHTML = '<div class="muted">Loading…</div>';
            try {
                const { data } = await api.get(`${config.apiPath}?per_page=50${getListFilterQuery()}`);
                body.innerHTML = renderCurrentRecordRows(data);
            } catch (err) {
                showError(err);
            }
            return;
        }

        const tbody = document.getElementById('listBody');
        if (!tbody) return;
        const colCount = config.listColumns.length + (config.rowActions ? 1 : 0);
        tbody.innerHTML = `<tr><td colspan="${colCount}" class="muted">Loading…</td></tr>`;
        renderListFilterNotice();
        try {
            const { data } = await api.get(`${config.apiPath}?per_page=50${getListFilterQuery()}`);
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

    // Builds a human-readable label for a select field's chosen value, e.g.
    // "Thakurgao Sub-Committee" instead of just its id — used to confirm
    // what a submit actually did (see handleSubmit below).
    function selectedLabel(fieldName, id) {
        const field = config.formFields.find(f => f.name === fieldName);
        if (!field || id == null) return null;
        const rec = (selectCache[fieldName] || []).find(r => String(r.id) === String(id));
        return rec ? optionLabel(field, rec) : null;
    }

    async function handleSubmit(e) {
        e.preventDefault();
        errorBox.style.display = 'none';
        successBox.style.display = 'none';

        const payload = {};
        config.formFields.forEach(f => { payload[f.name] = fieldValue(f); });

        try {
            await api.post(config.apiPath, payload);

            // Generic "what just happened" confirmation. Rich only for
            // modules that actually have these two fields (today: Sub-
            // Committee Transfer) — everything else just gets "Saved.".
            const toLabel = selectedLabel('to_committee_id', payload.to_committee_id);
            const planLabel = selectedLabel('procurement_plan_id', payload.procurement_plan_id);
            showSuccess(
                toLabel
                    ? `Sent — ${planLabel ?? 'this record'} has been transferred to ${toLabel}.`
                    : 'Saved.'
            );

            document.getElementById('resourceForm').reset();
            applyQueryPrefill(); // keep any URL-driven prefill/lock after the reset
            await loadRecords();
        } catch (err) {
            showError(err);
        }
    }

    // ---- 2. Only NOW draw the page — every select already has its data. ----
    await loadSelectOptions();

    const visibleFields = config.formFields.filter(f => f.type !== 'currentUser');
    const sectionTitleStyle = 'margin:0 0 1rem; font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--accent-dark);';

    // Some modules (e.g. Committees, Committee Members) are formed by the
    // Super Admin only — everyone can still read the list above, but the
    // create form itself is hidden for non-admin users. The API enforces
    // this too (role:admin on store/update/destroy); this is just so the
    // form doesn't sit there inviting a 403. A few others (e.g. Stock
    // Register) are pure running records — no form at all, for anyone.
    const isAdmin = window.currentUserRole === 'admin';
    const isReadOnly = config.readOnly === true;
    const addFormBlock = (isReadOnly || (config.adminOnly && !isAdmin))
        ? `<div class="card">
               <h3 style="${sectionTitleStyle}">Add New</h3>
               <p class="muted" style="margin:0;">${isReadOnly ? (config.readOnlyNote || 'This is a running record — entries are posted automatically and are not created here.') : (config.adminOnlyNote || 'This is managed by the Super Admin.')}</p>
           </div>`
        : `<div class="card">
               <h3 style="${sectionTitleStyle}">Add New</h3>
               <div id="prefillNotice"></div>
               <form id="resourceForm">
                   <div class="form-grid">${visibleFields.map(renderField).join('')}</div>
                   <button type="submit" class="btn primary" style="margin-top:1rem;">Save</button>
               </form>
           </div>`;

    const logBlock = `
        <details class="card" open>
            <summary style="${sectionTitleStyle} cursor:pointer; list-style:none;">Log</summary>
            <div id="listFilterNotice" style="margin-top:1rem;"></div>
            <div style="overflow-x:auto; margin-top:1rem;">
                <table>
                    <thead><tr>${config.listColumns.map(c => `<th>${c.label}</th>`).join('')}${config.rowActions ? '<th>Action</th>' : ''}</tr></thead>
                    <tbody id="listBody"><tr><td colspan="${config.listColumns.length + (config.rowActions ? 1 : 0)}" class="muted">Loading…</td></tr></tbody>
                </table>
            </div>
        </details>`;

    const currentRecordBlock = `
        <div class="card">
            <h3 style="${sectionTitleStyle}">Current Record</h3>
            <div id="currentRecordBody"><div class="muted">Loading…</div></div>
        </div>`;

    // Split modules: History page (`?history=1`) = Log only, no form.
    // Everywhere else = current record + form, never the full Log.
    // Non-split modules keep the original Log + form layout together.
    if (isSplit && historyMode) {
        root.innerHTML = `<div id="errorBoxPlaceholder"></div>${logBlock}`;
    } else if (isSplit) {
        root.innerHTML = `<div id="errorBoxPlaceholder"></div>${currentRecordBlock}${addFormBlock}`;
    } else {
        root.innerHTML = `
        <div id="errorBoxPlaceholder"></div>
        ${logBlock}
        ${addFormBlock}
    `;
    }

    // Pre-fills a form field from the URL, e.g. `?new=1&field_procurement_plan_id=42`
    // — used when arriving here from a specific record's own page (a case,
    // a PR) so the officer doesn't have to hunt for it again in the
    // dropdown. Locks the field once filled so it can't be changed by
    // accident; only applies to fields that actually exist on this module.
    // An optional `context_label` (e.g. a PR number) is shown above the
    // form so it's clear what this submission is for.
    function applyQueryPrefill() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('new') !== '1') return;

        config.formFields.forEach(field => {
            const raw = params.get(`field_${field.name}`);
            if (raw === null) return;
            const el = document.getElementById(`field_${field.name}`);
            if (!el) return;
            el.value = raw;
            if (field.type === 'select' || field.type === 'enum') el.disabled = true;
        });

        const label = params.get('context_label');
        const notice = document.getElementById('prefillNotice');
        if (label && notice) {
            notice.innerHTML = `<div style="background:#EFF6FF; color:#1D4ED8; border:1px solid #BFDBFE; border-radius:8px; padding:.5rem .8rem; margin-bottom:.9rem; font-size:.82rem;">Prefilled for <b>${label}</b> — the linked field below is locked to it.</div>`;
        }

        document.getElementById('resourceForm')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    document.getElementById('errorBoxPlaceholder').replaceWith(errorBox);
    errorBox.after(successBox);
    const formIsRendered = !(isSplit && historyMode);
    if (formIsRendered && !isReadOnly && (!config.adminOnly || isAdmin)) {
        document.getElementById('resourceForm').addEventListener('submit', handleSubmit);
        wireAutofill();
        applyQueryPrefill();
    }

    await loadRecords();
}