@extends('layouts.app')

@section('title', 'ESDO Procurement — Project Management')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

    :root {
        --ink: #0F172A;
        --paper: #FFFFFF;
        --surface: #F8FAFC;
        --line: #E2E8F0;
        --muted: #64748B;
        --accent: #0D9488;
        --accent-dark: #0F766E;
        --amber: #B45309;
        --amber-bg: #FFFBEB;
        --green: #15803D;
        --green-bg: #F0FDF4;
        --red: #B91C1C;
        --red-bg: #FEF2F2;
        --slate-bg: #F1F5F9;
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .container { max-width: 1180px; }
    .shell { max-width: 1180px; margin: 0 auto; padding: 2rem 2rem 4rem; }

    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem; font-weight: 600; letter-spacing: .1em;
        text-transform: uppercase; color: var(--muted); margin: 0 0 .35rem;
    }

    .page-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
    .page-head h1 { margin: 0; font-size: 1.4rem; letter-spacing: -0.01em; }
    .page-head p { margin: .3rem 0 0; color: var(--muted); font-size: .88rem; max-width: 620px; }

    .btn {
        display: inline-flex; align-items: center; gap: .4rem;
        background: var(--ink); color: #fff; border: 1px solid var(--ink);
        border-radius: 7px; padding: .6rem 1.1rem; cursor: pointer;
        text-decoration: none; font-size: .85rem; font-weight: 600;
        transition: background .15s ease; font-family: inherit;
    }
    .btn:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
    .btn.primary { background: var(--accent); border-color: var(--accent); }
    .btn.primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
    .btn.outline { background: transparent; color: var(--ink); border: 1px solid var(--line); }
    .btn.outline:hover { background: var(--surface); border-color: #CBD5E1; }
    .btn.sm { padding: .38rem .7rem; font-size: .76rem; }
    .btn[disabled] { opacity: .4; cursor: not-allowed; }

    .panel { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 1.25rem; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { text-align: left; padding: .75rem 1rem; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 600; background: var(--surface); border-bottom: 1px solid var(--line); }
    table.data td { padding: .8rem 1rem; font-size: .86rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
    table.data tr:last-child td { border-bottom: none; }
    table.data tr:hover td { background: #FAFBFC; }
    .row-actions { display: flex; gap: .4rem; flex-wrap: wrap; }

    .badge { display: inline-block; padding: .18rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .02em; }
    .badge.status-active { background: var(--green-bg); color: var(--green); }
    .badge.status-inactive { background: var(--red-bg); color: var(--red); }
    .badge.committee { background: #EDE9FE; color: #5B21B6; }

    .muted { color: var(--muted); }
    .error-box { background: var(--red-bg); color: var(--red); padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; display: none; }
    .notice-box { background: var(--green-bg); color: var(--green); padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; display: none; }

    /* ---- Modal ---- */
    .modal-backdrop {
        position: fixed; inset: 0; background: rgba(15,23,42,.45);
        display: none; align-items: flex-start; justify-content: center;
        padding: 3rem 1rem; z-index: 50; overflow-y: auto;
    }
    .modal-backdrop.open { display: flex; }
    .modal {
        background: #fff; border-radius: 14px; width: 100%; max-width: 640px;
        padding: 1.75rem; box-shadow: 0 20px 60px rgba(15,23,42,.25);
    }
    .modal h2 { margin: 0 0 .25rem; font-size: 1.1rem; }
    .modal .sub { margin: 0 0 1.25rem; color: var(--muted); font-size: .82rem; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .9rem 1.1rem; }
    .field { display: flex; flex-direction: column; }
    .field.full { grid-column: 1 / -1; }
    .field label { font-size: .78rem; color: var(--muted); margin-bottom: .3rem; font-weight: 500; }
    .field input, .field select, .field textarea {
        border: 1px solid var(--line); border-radius: 7px; padding: .55rem .7rem;
        font-size: .87rem; font-family: inherit;
    }
    .field.checkbox { flex-direction: row; align-items: center; gap: .5rem; }
    .field.checkbox input { width: auto; }
    .field.checkbox label { margin: 0; }
    .modal-actions { display: flex; justify-content: flex-end; gap: .6rem; margin-top: 1.5rem; }
    .hint { font-size: .74rem; color: var(--muted); margin-top: .25rem; }

    /* ---- Team modal specific ---- */
    .team-section { margin-top: 1.25rem; padding-top: 1.1rem; border-top: 1px solid var(--line); }
    .team-section h3 { margin: 0 0 .6rem; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
    .user-checklist { max-height: 220px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: .5rem .75rem; }
    .user-checklist label { display: flex; align-items: center; gap: .5rem; margin: 0; padding: .35rem 0; font-size: .85rem; font-weight: 500; color: var(--ink); cursor: pointer; }
    .user-checklist input[type="checkbox"] { width: auto; flex: 0 0 auto; margin: 0; padding: 0; accent-color: var(--accent); }
    .user-checklist label .email { color: var(--muted); font-size: .76rem; }
    .user-checklist .empty { color: var(--muted); font-size: .82rem; padding: .4rem 0; }
    .new-user-row { display: grid; grid-template-columns: 1.3fr 1.3fr 1fr .9fr; gap: .5rem; margin-bottom: .5rem; }
    .new-user-row input, .new-user-row select { border: 1px solid var(--line); border-radius: 6px; padding: .45rem .6rem; font-size: .82rem; font-family: inherit; }
    .team-members-list { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .5rem; }
    .team-members-list .chip { background: var(--slate-bg); border-radius: 999px; padding: .25rem .7rem; font-size: .78rem; }

    @media (max-width: 640px) {
        .shell { padding: 1.25rem 1rem 3rem; }
        table.data { font-size: .8rem; }
        .new-user-row { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('content')
<div class="shell">
    <div class="page-head">
        <div>
            <p class="eyebrow">Super Admin</p>
            <h1>Project Management</h1>
            <p>Name a project, and an empty Sub-Committee is created for it automatically. Assign existing staff or create new accounts under the project right here — then add Sub-Committee members from the People & Committees module.</p>
        </div>
        <div style="display:flex; gap:.6rem;">
            <a href="{{ route('admin.users.index') }}" class="btn outline">User Management</a>
            <a href="{{ route('admin.committees.index') }}" class="btn outline">Manage Committees</a>
            <button class="btn outline" type="button" onclick="bulkOpen()">Import Annual Plans (Excel)</button>
            <button class="btn primary" id="btnNewProject">+ New Project</button>
        </div>
    </div>

    <div id="errorBox" class="error-box"></div>
    <div id="noticeBox" class="notice-box"></div>

    <div class="panel">
        <table class="data">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Code</th>
                    <th>Sub-Committee</th>
                    <th>Team</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="projectTableBody">
                <tr><td colspan="6" class="muted">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- New Project modal -->
<div class="modal-backdrop" id="projectModalBackdrop">
    <div class="modal">
        <h2>New Project</h2>
        <p class="sub">Saving this creates the project and, automatically, an empty Sub-Committee for it.</p>
        <div id="projectModalError" class="error-box"></div>
        <form id="projectForm">
            <div class="form-grid">
                <div class="field full">
                    <label for="p_name">Project Name</label>
                    <input type="text" id="p_name" required>
                </div>
                <div class="field">
                    <label for="p_code">Code</label>
                    <input type="text" id="p_code" placeholder="optional">
                </div>
                <div class="field checkbox">
                    <input type="checkbox" id="p_is_active" checked>
                    <label for="p_is_active">Active</label>
                </div>
                <div class="field full">
                    <label for="p_description">Description</label>
                    <textarea id="p_description" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn outline" id="btnCancelProject">Cancel</button>
                <button type="submit" class="btn primary">Save Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Team modal -->
<div class="modal-backdrop" id="teamModalBackdrop">
    <div class="modal">
        <h2>Assign Team — <span id="teamProjectName"></span></h2>
        <p class="sub">Move existing staff into this project, or create new accounts for it directly.</p>
        <div id="teamModalError" class="error-box"></div>
        <div id="teamModalNotice" class="notice-box"></div>

        <div>
            <strong style="font-size:.85rem;">Current team</strong>
            <div class="team-members-list" id="currentTeamList"><span class="muted" style="font-size:.82rem;">Loading…</span></div>
        </div>

        <div class="team-section">
            <h3>Assign Existing Users</h3>
            <div class="user-checklist" id="existingUserChecklist"><span class="empty">Loading…</span></div>
            <div class="modal-actions" style="margin-top:.75rem;">
                <button type="button" class="btn sm" id="btnAssignExisting">Assign Selected</button>
            </div>
        </div>

        <div class="team-section">
            <h3>Add New Users</h3>
            <div id="newUserRows"></div>
            <button type="button" class="btn outline sm" id="btnAddUserRow">+ Add another user</button>
        </div>

        <div class="modal-actions">
            <a href="{{ route('admin.committees.index') }}" class="btn outline sm" target="_blank" rel="noopener">Manage Sub-Committee Members →</a>
            <button type="button" class="btn" id="btnCloseTeam">Done</button>
        </div>
    </div>
</div>
<!-- Import Annual Plans (Excel) modal -->
<div class="modal-backdrop" id="bulkBackdrop">
    <div class="modal" style="max-width:1180px;">
        <h2>Import Annual Plans from Excel</h2>
        <p class="sub">One file, many projects. For each project choose an existing project, a new one, or skip it. Each project gets an Annual Plan for the fiscal year (an existing plan is reused) and its packages. Nothing is saved until you press <b>Import</b>.</p>
        <div id="bulkError" class="error-box"></div>
        <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
            <div class="field"><label>Excel file (.xlsx)</label><input type="file" id="bulkFile" accept=".xlsx,.xls" onchange="bulkOnFile()"></div>
            <div class="field" style="width:170px;"><label>Fiscal year starts (July)</label><input type="number" id="bulkFy" value="2026" min="2000" max="2100" onchange="bulkPreview()"></div>
        </div>
        <div id="bulkNote" class="hint" style="margin-top:.5rem;"></div>
        <div id="bulkMsg" class="hint" style="margin-top:.5rem; font-size:.85rem;"></div>
        <div id="bulkBody" style="margin-top:.75rem;"></div>
        <div class="modal-actions">
            <button type="button" class="btn outline" onclick="bulkClose()">Cancel</button>
            <button type="button" class="btn primary" id="bulkConfirm" onclick="bulkCommit()" disabled>Import</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.ADMIN_ROLES = @json($roles);
    window.ADMIN_ROLE_LABELS = @json($roleLabels);
</script>
<script src="{{ asset('js/admin-projects.js') }}"></script>
<script>
    // ---------------- Import Annual Plans (Excel) ----------------
    let bulkCfg = {};          // project key -> { action: existing|new|skip, project_id, name }
    let bulkGroupMap = {};     // normalised Sub-Category text -> group id | '' (no group)
    let bulkData = null;

    const bulkEsc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const bulkMoney = n => Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });

    function bulkOpen() {
        bulkCfg = {}; bulkGroupMap = {}; bulkData = null;
        document.getElementById('bulkFile').value = '';
        document.getElementById('bulkBody').innerHTML = '';
        document.getElementById('bulkNote').textContent = '';
        document.getElementById('bulkConfirm').disabled = true;
        document.getElementById('bulkConfirm').textContent = 'Import';
        bulkShow('');
        document.getElementById('bulkBackdrop').classList.add('open');
    }

    function bulkClose() { document.getElementById('bulkBackdrop').classList.remove('open'); }

    function bulkShow(msg, isError) {
        const box = document.getElementById('bulkError');
        box.style.display = isError ? 'block' : 'none';
        box.textContent = isError ? msg : '';
        document.getElementById('bulkMsg').textContent = isError ? '' : (msg || '');
    }

    async function bulkRequest(path, extra = {}) {
        const file = document.getElementById('bulkFile').files[0];
        if (!file) throw new Error('Choose the Excel file first.');
        const fd = new FormData();
        fd.append('file', file);
        fd.append('fy_start_year', document.getElementById('bulkFy').value || '2026');
        Object.entries(extra).forEach(([k, v]) => fd.append(k, v));

        const res = await fetch(`/api/admin/annual-plan-import/${path}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(json.message || (json.errors ? Object.values(json.errors).flat().join(', ') : `Request failed (${res.status})`));
        }
        return json;
    }

    async function bulkOnFile() {
        bulkCfg = {}; bulkGroupMap = {};
        await bulkPreview();
    }

    async function bulkPreview() {
        document.getElementById('bulkConfirm').disabled = true;
        if (!document.getElementById('bulkFile').files[0]) return;
        bulkShow('Reading the file…');
        try {
            const data = await bulkRequest('preview', { config: JSON.stringify(bulkCfg), group_map: JSON.stringify(bulkGroupMap) });
            // First time: remember the suggested choice for every project, so later edits are kept.
            data.projects.forEach(p => { if (!bulkCfg[p.key]) bulkCfg[p.key] = { ...p.setting }; });
            bulkData = data;
            bulkRender();
            bulkShow('');
        } catch (e) {
            bulkShow(e.message, true);
        }
    }

    function bulkSetAction(key, v) { bulkCfg[key].action = v; bulkPreview(); }
    function bulkSetProject(key, v) { bulkCfg[key].project_id = v ? Number(v) : null; bulkPreview(); }
    function bulkSetName(key, v) { bulkCfg[key].name = v; }
    function bulkGroup(key, v) {
        if (v === '__keep') delete bulkGroupMap[key]; else bulkGroupMap[key] = v === 'none' ? '' : v;
        bulkPreview();
    }

    function bulkRender() {
        const d = bulkData;
        const t = d.totals;
        document.getElementById('bulkNote').textContent =
            `FY ${d.fiscal_year}. ${d.skipped_no_project} rows with no project name are skipped.`;

        const sysOpts = sel => '<option value="">— choose project —</option>' + d.system_projects.map(sp =>
            `<option value="${sp.id}" ${String(sel) === String(sp.id) ? 'selected' : ''}>${bulkEsc(sp.name)}</option>`).join('');

        let html = `<div style="margin-bottom:.6rem;"><b>${t.projects}</b> projects · <b>${t.packages}</b> packages · total <b>${bulkMoney(t.grand_total)}</b></div>`;

        if (d.unmatched_groups.length) {
            const opts = cat => d.groups.filter(g => String(g.category_id) === String(cat))
                .map(g => `<option value="${g.id}">${bulkEsc(g.code)} — ${bulkEsc(g.name)}</option>`).join('');
            html += `<div style="border:1px solid #FCD34D; background:#FFFBEB; border-radius:8px; padding:.7rem .9rem; margin-bottom:.9rem;">
                <b>Choose a Procurement Group</b> for these Sub-Category texts, or import them without a group:
                <table class="data" style="margin-top:.4rem;"><thead><tr><th>Text in the file</th><th>Rows</th><th>Category</th><th>Procurement Group</th></tr></thead><tbody>
                ${d.unmatched_groups.map(u => `<tr><td>${u.text ? bulkEsc(u.text) : '<i>(blank)</i>'}</td><td>${u.count}</td><td>${bulkEsc(u.category)}</td>
                    <td><select onchange="bulkGroup('${u.key}', this.value)"><option value="__keep">— choose —</option><option value="none">No group</option>${opts(u.category_id)}</select></td></tr>`).join('')}
                </tbody></table></div>`;
        }

        html += `<div style="overflow-x:auto; max-height:460px; overflow-y:auto;"><table class="data"><thead><tr>
            <th>Project in the file</th><th>Action</th><th>System project / new name</th><th>Donor · District</th><th>Rows</th><th>Total</th><th>Notes</th></tr></thead><tbody>
            ${d.projects.map(p => {
                const c = bulkCfg[p.key];
                const target = c.action === 'existing'
                    ? `<select onchange="bulkSetProject('${p.key}', this.value)">${sysOpts(c.project_id)}</select>`
                    : c.action === 'new'
                        ? `<input type="text" value="${bulkEsc(c.name)}" oninput="bulkSetName('${p.key}', this.value)" style="width:100%; border:1px solid var(--line); border-radius:6px; padding:.4rem .5rem;">`
                        : '<span class="muted">—</span>';
                const notes = [];
                if (c.action === 'existing' && !c.project_id) notes.push('<span style="color:#B91C1C;">choose a project</span>');
                if (p.existing_plan) notes.push(`plan #${p.existing_plan.id} exists (${p.existing_plan.packages} packages) — packages are added to it`);
                if (p.with_warnings) notes.push(`${p.with_warnings} rows have notes`);
                return `<tr style="${c.action === 'skip' ? 'opacity:.55;' : ''}">
                    <td><b>${bulkEsc(p.short_name)}</b>${p.short_name !== p.name ? `<div class="hint">${bulkEsc(p.name)}</div>` : ''}</td>
                    <td><select onchange="bulkSetAction('${p.key}', this.value)">
                        <option value="existing" ${c.action === 'existing' ? 'selected' : ''}>Use existing</option>
                        <option value="new" ${c.action === 'new' ? 'selected' : ''}>Create new</option>
                        <option value="skip" ${c.action === 'skip' ? 'selected' : ''}>Skip</option></select></td>
                    <td style="min-width:240px;">${target}</td>
                    <td>${bulkEsc(p.donor)}${p.district ? ' · ' + bulkEsc(p.district) : ''}</td>
                    <td>${c.action === 'skip' ? '' : p.import_rows}</td><td>${c.action === 'skip' ? '' : bulkMoney(p.import_total)}</td>
                    <td class="hint">${notes.join('<br>')}</td></tr>`;
            }).join('')}
            </tbody></table></div>`;

        document.getElementById('bulkBody').innerHTML = html;
        const missing = d.projects.some(p => bulkCfg[p.key].action === 'existing' && !bulkCfg[p.key].project_id);
        const btn = document.getElementById('bulkConfirm');
        btn.disabled = t.packages === 0 || missing;
        btn.textContent = `Import ${t.projects} projects / ${t.packages} packages`;
    }

    async function bulkCommit() {
        const t = bulkData?.totals;
        if (!t || !t.packages) return;
        if (!confirm(`Import ${t.packages} packages for ${t.projects} projects?\nThis cannot be undone from here.`)) return;
        const btn = document.getElementById('bulkConfirm');
        btn.disabled = true;
        bulkShow('Importing… please wait.');
        try {
            const r = await bulkRequest('commit', { config: JSON.stringify(bulkCfg), group_map: JSON.stringify(bulkGroupMap) });
            bulkClose();
            alert(`Done.\nNew projects: ${r.projects_created}\nNew plans: ${r.plans_created} (existing plans reused: ${r.plans_reused})\nPackages imported: ${r.packages}`);
            location.reload();
        } catch (e) {
            btn.disabled = false;
            bulkShow(e.message, true);
        }
    }
</script>
@endsection