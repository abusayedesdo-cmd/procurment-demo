/**
 * Super Admin > Data Manager.
 * Talks to /api/admin/tables/* (role:admin only). Fully generic — builds
 * its table list, grid columns, and create/edit form purely from the
 * column metadata the backend returns for whichever table is selected.
 */
(function () {
    const els = {
        tableSearch: document.getElementById('tableSearch'),
        tableList: document.getElementById('tableList'),
        mainPanel: document.getElementById('mainPanel'),
        errorBox: document.getElementById('errorBox'),
        noticeBox: document.getElementById('noticeBox'),
        modalBackdrop: document.getElementById('rowModalBackdrop'),
        modalTitle: document.getElementById('modalTitle'),
        modalSub: document.getElementById('modalSub'),
        modalError: document.getElementById('modalError'),
        form: document.getElementById('rowForm'),
        formFields: document.getElementById('formFields'),
        btnCancel: document.getElementById('btnCancel'),
    };

    let allTables = [];
    let currentTable = null;
    let currentColumns = [];
    let currentPk = 'id';
    let currentPage = 1;
    let currentSearch = '';
    let fkOptionsCache = {}; // { tableName: [{id, label}] }
    let editingRow = null; // null = create mode

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function showError(msg) {
        els.errorBox.textContent = msg;
        els.errorBox.style.display = 'block';
        els.noticeBox.style.display = 'none';
    }

    function showNotice(msg) {
        els.noticeBox.textContent = msg;
        els.noticeBox.style.display = 'block';
        els.errorBox.style.display = 'none';
    }

    function clearMessages() {
        els.errorBox.style.display = 'none';
        els.noticeBox.style.display = 'none';
    }

    // ---- Sidebar: table list ----
    async function loadTableList() {
        try {
            const { data } = await api.get('/admin/tables');
            allTables = data;
            renderTableList();

            if (!currentTable) {
                const params = new URLSearchParams(window.location.search);
                const preselect = params.get('table');
                if (preselect && allTables.some(t => t.name === preselect)) {
                    selectTable(preselect);
                }
            }
        } catch (err) {
            els.tableList.innerHTML = `<li class="muted" style="padding:.5rem .65rem;">Failed to load tables.</li>`;
            showError(err.message || String(err));
        }
    }

    function renderTableList() {
        const filter = els.tableSearch.value.trim().toLowerCase();
        const filtered = allTables.filter(t =>
            t.name.toLowerCase().includes(filter) || t.label.toLowerCase().includes(filter)
        );

        if (!filtered.length) {
            els.tableList.innerHTML = `<li class="muted" style="padding:.5rem .65rem;">No tables match.</li>`;
            return;
        }

        els.tableList.innerHTML = filtered.map(t => `
            <li>
                <button type="button" data-table="${t.name}" class="${currentTable === t.name ? 'active' : ''}">
                    <span>${escapeHtml(t.label)}</span>
                    <span class="count">${t.row_count}</span>
                </button>
            </li>
        `).join('');

        els.tableList.querySelectorAll('button[data-table]').forEach(btn => {
            btn.addEventListener('click', () => selectTable(btn.dataset.table));
        });
    }

    // ---- Select a table ----
    async function selectTable(table) {
        currentTable = table;
        currentPage = 1;
        currentSearch = '';
        clearMessages();
        renderTableList();
        renderPanelShell();

        try {
            const { data } = await api.get(`/admin/tables/${table}/columns`);
            currentColumns = data.columns;
            currentPk = data.primary_key;

            await preloadFkOptions();
            await loadRows();
        } catch (err) {
            showError(err.message || String(err));
        }
    }

    async function preloadFkOptions() {
        const fkTables = [...new Set(
            currentColumns.filter(c => c.foreign_key).map(c => c.foreign_key.table)
        )];

        await Promise.all(fkTables.map(async (t) => {
            if (fkOptionsCache[t]) return;
            try {
                const { data } = await api.get(`/admin/tables/${t}/options`);
                fkOptionsCache[t] = data;
            } catch (e) {
                fkOptionsCache[t] = [];
            }
        }));
    }

    function fkLabel(fkTable, id) {
        if (id === null || id === undefined) return '—';
        const list = fkOptionsCache[fkTable] || [];
        const match = list.find(o => String(o.id) === String(id));
        return match ? `${match.label ?? match.id} (#${id})` : `#${id}`;
    }

    // ---- Main panel shell (toolbar + table) ----
    function renderPanelShell() {
        const meta = allTables.find(t => t.name === currentTable);
        els.mainPanel.innerHTML = `
            <div class="panel-head">
                <div>
                    <h2>${escapeHtml(meta ? meta.label : currentTable)}</h2>
                    <div class="sub"><code>${currentTable}</code> table</div>
                </div>
                <button class="btn primary" id="btnNewRow">+ New Row</button>
            </div>
            <div class="toolbar">
                <input type="text" id="rowSearch" placeholder="Search text columns…">
            </div>
            <div class="panel">
                <div style="overflow-x:auto;">
                    <table class="data" id="rowsTable">
                        <thead><tr></tr></thead>
                        <tbody><tr><td class="muted">Loading…</td></tr></tbody>
                    </table>
                </div>
                <div class="pager" id="pager"></div>
            </div>
        `;

        document.getElementById('btnNewRow').addEventListener('click', openCreateModal);
        const searchInput = document.getElementById('rowSearch');
        let debounce;
        searchInput.addEventListener('input', () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                currentSearch = searchInput.value.trim();
                currentPage = 1;
                loadRows();
            }, 300);
        });
    }

    // ---- Rows ----
    function displayColumns() {
        // Show every column except pure noise; keep pk + timestamps visible (read-only context) but not deleted_at.
        return currentColumns.filter(c => c.name !== 'deleted_at');
    }

    async function loadRows() {
        const tbody = document.querySelector('#rowsTable tbody');
        const thead = document.querySelector('#rowsTable thead tr');
        if (!tbody) return;

        tbody.innerHTML = `<tr><td class="muted">Loading…</td></tr>`;

        const cols = displayColumns();
        thead.innerHTML = cols.map(c => `<th>${escapeHtml(c.name)}</th>`).join('') + '<th>Actions</th>';

        try {
            const params = new URLSearchParams({ page: currentPage, per_page: 25 });
            if (currentSearch) params.set('q', currentSearch);

            const { data, meta } = await api.get(`/admin/tables/${currentTable}/rows?${params.toString()}`);
            renderRows(data, cols);
            renderPager(meta);
        } catch (err) {
            tbody.innerHTML = `<tr><td class="muted">Failed to load rows.</td></tr>`;
            showError(err.message || String(err));
        }
    }

    function formatCell(col, value) {
        if (value === null || value === undefined) return '<span class="muted">—</span>';
        if (col.foreign_key) return escapeHtml(fkLabel(col.foreign_key.table, value));
        if (col.input_type === 'boolean') return value == 1 ? 'Yes' : 'No';
        return escapeHtml(value);
    }

    function renderRows(rows, cols) {
        const tbody = document.querySelector('#rowsTable tbody');

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="${cols.length + 1}" class="muted">No rows${currentSearch ? ' match your search' : ''}.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr data-id="${row[currentPk]}">
                ${cols.map(c => `<td title="${escapeHtml(row[c.name])}">${formatCell(c, row[c.name])}</td>`).join('')}
                <td>
                    <div class="row-actions">
                        <button class="btn outline sm" data-action="edit">Edit</button>
                        <button class="btn danger sm" data-action="delete">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.querySelectorAll('button[data-action]').forEach(btn => {
            btn.addEventListener('click', () => handleRowAction(btn));
        });
    }

    function renderPager(meta) {
        const pager = document.getElementById('pager');
        if (!pager || !meta) return;

        pager.innerHTML = `
            <span>${meta.total} row${meta.total === 1 ? '' : 's'} — page ${meta.current_page} of ${meta.last_page}</span>
            <div class="controls">
                <button class="btn outline sm" id="prevPage" ${meta.current_page <= 1 ? 'disabled' : ''}>Prev</button>
                <button class="btn outline sm" id="nextPage" ${meta.current_page >= meta.last_page ? 'disabled' : ''}>Next</button>
            </div>
        `;

        document.getElementById('prevPage').addEventListener('click', () => { currentPage--; loadRows(); });
        document.getElementById('nextPage').addEventListener('click', () => { currentPage++; loadRows(); });
    }

    async function handleRowAction(btn) {
        const tr = btn.closest('tr');
        const id = tr.dataset.id;
        const action = btn.dataset.action;
        clearMessages();

        if (action === 'edit') {
            try {
                const params = new URLSearchParams({ page: 1, per_page: 1, q: '' });
                // Simplest reliable way to fetch a single row's full values with this generic API:
                // re-fetch the current page and find it (rows already loaded client-side would go stale on edit anyway).
                const { data } = await api.get(`/admin/tables/${currentTable}/rows?per_page=100&page=${currentPage}${currentSearch ? '&q=' + encodeURIComponent(currentSearch) : ''}`);
                const row = data.find(r => String(r[currentPk]) === String(id));
                if (row) openEditModal(row);
            } catch (err) {
                showError(err.message || String(err));
            }
            return;
        }

        if (action === 'delete') {
            if (!confirm(`Delete row #${id} from ${currentTable}? This cannot be undone.`)) return;
            try {
                const { message } = await api.del(`/admin/tables/${currentTable}/rows/${id}`);
                showNotice(message || 'Row deleted.');
                await loadRows();
            } catch (err) {
                showError(err.message || String(err));
            }
        }
    }

    // ---- Modal / form ----
    function editableColumns() {
        return currentColumns.filter(c => c.is_editable);
    }

    function fieldHtml(col) {
        const required = !col.nullable && !col.has_default;
        const reqMark = required ? ' <span class="req">*</span>' : '';
        const id = `f_${col.name}`;
        const full = col.input_type === 'textarea' ? ' full' : '';

        let inputHtml = '';
        switch (col.input_type) {
            case 'textarea':
                inputHtml = `<textarea id="${id}" name="${col.name}"></textarea>`;
                break;
            case 'boolean':
                return `
                    <div class="field checkbox">
                        <input type="checkbox" id="${id}" name="${col.name}">
                        <label for="${id}">${escapeHtml(col.name)}</label>
                    </div>`;
            case 'enum':
                inputHtml = `<select id="${id}" name="${col.name}">
                    ${required ? '' : '<option value="">—</option>'}
                    ${(col.enum_options || []).map(o => `<option value="${escapeHtml(o)}">${escapeHtml(o)}</option>`).join('')}
                </select>`;
                break;
            case 'select': {
                const opts = fkOptionsCache[col.foreign_key.table] || [];
                inputHtml = `<select id="${id}" name="${col.name}">
                    ${required ? '' : '<option value="">—</option>'}
                    ${opts.map(o => `<option value="${o.id}">${escapeHtml(o.label ?? o.id)} (#${o.id})</option>`).join('')}
                </select>`;
                break;
            }
            case 'integer':
                inputHtml = `<input type="number" step="1" id="${id}" name="${col.name}">`;
                break;
            case 'decimal':
                inputHtml = `<input type="number" step="any" id="${id}" name="${col.name}">`;
                break;
            case 'date':
                inputHtml = `<input type="date" id="${id}" name="${col.name}">`;
                break;
            case 'datetime':
                inputHtml = `<input type="datetime-local" id="${id}" name="${col.name}">`;
                break;
            default:
                inputHtml = `<input type="text" id="${id}" name="${col.name}">`;
        }

        return `
            <div class="field${full}">
                <label for="${id}">${escapeHtml(col.name)}${reqMark}</label>
                ${inputHtml}
            </div>`;
    }

    function buildForm() {
        els.formFields.innerHTML = editableColumns().map(fieldHtml).join('');
    }

    function openCreateModal() {
        editingRow = null;
        els.modalTitle.textContent = `New row — ${currentTable}`;
        els.modalSub.textContent = 'Fields marked * are required.';
        els.modalError.style.display = 'none';
        buildForm();
        els.modalBackdrop.classList.add('open');
    }

    function openEditModal(row) {
        editingRow = row;
        els.modalTitle.textContent = `Edit row #${row[currentPk]} — ${currentTable}`;
        els.modalSub.textContent = 'Fields marked * are required.';
        els.modalError.style.display = 'none';
        buildForm();

        editableColumns().forEach(col => {
            const el = document.getElementById(`f_${col.name}`);
            if (!el) return;
            const value = row[col.name];

            if (col.input_type === 'boolean') {
                el.checked = value == 1;
            } else if (col.input_type === 'datetime' && value) {
                el.value = String(value).replace(' ', 'T').slice(0, 16);
            } else {
                el.value = value === null || value === undefined ? '' : value;
            }
        });

        els.modalBackdrop.classList.add('open');
    }

    function closeModal() {
        els.modalBackdrop.classList.remove('open');
        editingRow = null;
    }

    async function handleFormSubmit(e) {
        e.preventDefault();
        els.modalError.style.display = 'none';

        const payload = {};
        editableColumns().forEach(col => {
            const el = document.getElementById(`f_${col.name}`);
            if (!el) return;

            if (col.input_type === 'boolean') {
                payload[col.name] = el.checked;
            } else {
                payload[col.name] = el.value;
            }
        });

        try {
            if (editingRow) {
                await api.put(`/admin/tables/${currentTable}/rows/${editingRow[currentPk]}`, payload);
                showNotice('Row updated.');
            } else {
                await api.post(`/admin/tables/${currentTable}/rows`, payload);
                showNotice('Row created.');
            }
            closeModal();
            await loadRows();
            // Row counts in the sidebar may now be stale; refresh quietly.
            loadTableList();
        } catch (err) {
            els.modalError.textContent = err.message || String(err);
            els.modalError.style.display = 'block';
        }
    }

    // ---- Wire up ----
    els.tableSearch.addEventListener('input', renderTableList);
    els.btnCancel.addEventListener('click', closeModal);
    els.modalBackdrop.addEventListener('click', (e) => { if (e.target === els.modalBackdrop) closeModal(); });
    els.form.addEventListener('submit', handleFormSubmit);

    loadTableList();
})();
