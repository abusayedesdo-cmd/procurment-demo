<?php $__env->startSection('title', 'ESDO Procurement — Committee Management'); ?>

<?php $__env->startSection('styles'); ?>
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
    .page-head p { margin: .3rem 0 0; color: var(--muted); font-size: .88rem; max-width: 640px; }

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
    .btn.danger { background: var(--red); border-color: var(--red); }
    .btn.danger:hover { background: #991B1B; border-color: #991B1B; }
    .btn[disabled] { opacity: .4; cursor: not-allowed; }

    .panel { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 1.25rem; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { text-align: left; padding: .75rem 1rem; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 600; background: var(--surface); border-bottom: 1px solid var(--line); }
    table.data td { padding: .8rem 1rem; font-size: .86rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
    table.data tr:last-child td { border-bottom: none; }
    table.data tr:hover td { background: #FAFBFC; }
    .row-actions { display: flex; gap: .4rem; flex-wrap: wrap; }

    .badge { display: inline-block; padding: .18rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .02em; }
    .badge.type-main { background: #EDE9FE; color: #5B21B6; }
    .badge.type-sub { background: #E0F2FE; color: #0369A1; }
    .badge.size-ok { background: var(--green-bg); color: var(--green); }
    .badge.size-warn { background: var(--amber-bg); color: var(--amber); }

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
        background: #fff; border-radius: 14px; width: 100%; max-width: 680px;
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
    .modal-actions { display: flex; justify-content: flex-end; gap: .6rem; margin-top: 1.5rem; }
    .hint { font-size: .74rem; color: var(--muted); margin-top: .25rem; }

    /* ---- Members modal specific ---- */
    .members-section { margin-top: 1.25rem; padding-top: 1.1rem; border-top: 1px solid var(--line); }
    .members-section h3 { margin: 0 0 .6rem; font-size: .82rem; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); display: flex; justify-content: space-between; align-items: center; }
    .current-members-table { width: 100%; border-collapse: collapse; }
    .current-members-table td { padding: .4rem 0; font-size: .85rem; border-bottom: 1px solid var(--line); }
    .current-members-table td.designation-cell input { width: 100%; border: 1px solid var(--line); border-radius: 6px; padding: .3rem .5rem; font-size: .8rem; font-family: inherit; }
    .current-members-table .rm-btn { color: var(--red); background: none; border: none; cursor: pointer; font-size: .78rem; font-family: inherit; }
    .copy-row { display: flex; gap: .5rem; align-items: center; }
    .copy-row select { flex: 1; border: 1px solid var(--line); border-radius: 7px; padding: .5rem .6rem; font-size: .85rem; font-family: inherit; }
    .user-checklist { max-height: 220px; overflow-y: auto; border: 1px solid var(--line); border-radius: 8px; padding: .5rem .75rem; }
    .user-checklist .uc-row { display: flex; align-items: center; gap: .5rem; padding: .35rem 0; font-size: .85rem; }
    .user-checklist .uc-row .uc-name { flex: 1; }
    .user-checklist .uc-row .email { color: var(--muted); font-size: .76rem; }
    .user-checklist .uc-row input[type="text"] { width: 130px; border: 1px solid var(--line); border-radius: 6px; padding: .3rem .5rem; font-size: .78rem; font-family: inherit; }
    .user-checklist .uc-login-toggle { display: flex; align-items: center; gap: .3rem; font-size: .75rem; color: var(--muted); white-space: nowrap; cursor: pointer; }
    .user-checklist .uc-login-fields { display: none; gap: .5rem; flex-wrap: wrap; margin: 0 0 .5rem 1.7rem; }
    .user-checklist .uc-login-fields.show { display: flex; }
    .user-checklist .uc-login-fields input, .user-checklist .uc-login-fields select { border: 1px solid var(--line); border-radius: 6px; padding: .3rem .5rem; font-size: .78rem; font-family: inherit; }
    .user-checklist .uc-login-fields input[type="email"] { flex: 1; min-width: 160px; }
    .user-checklist .uc-role-fixed { font-size: .76rem; color: var(--muted); background: var(--surface); border: 1px solid var(--line); border-radius: 6px; padding: .3rem .6rem; white-space: nowrap; }
    .user-checklist .empty { color: var(--muted); font-size: .82rem; padding: .4rem 0; }
    .new-user-row { display: grid; grid-template-columns: 1.1fr 1.1fr .9fr .8fr .9fr; gap: .5rem; margin-bottom: .5rem; }
    .new-user-row input, .new-user-row select { border: 1px solid var(--line); border-radius: 6px; padding: .45rem .6rem; font-size: .8rem; font-family: inherit; }

    @media (max-width: 640px) {
        .shell { padding: 1.25rem 1rem 3rem; }
        table.data { font-size: .8rem; }
        .new-user-row { grid-template-columns: 1fr 1fr; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <div class="page-head">
        <div>
            <p class="eyebrow">Super Admin</p>
            <h1>Committee Management</h1>
            <p>Create or edit a Main/Sub Committee and manage its members in one place — pick from existing staff, copy the roster from another committee, or create new accounts, each with their own designation on this committee.</p>
        </div>
        <div style="display:flex; gap:.6rem;">
            <a href="<?php echo e(route('admin.projects.index')); ?>" class="btn outline">Projects</a>
            <button class="btn primary" id="btnNewCommittee">+ New Committee</button>
        </div>
    </div>

    <div id="errorBox" class="error-box"></div>
    <div id="noticeBox" class="notice-box"></div>

    <div class="panel">
        <table class="data">
            <thead>
                <tr>
                    <th>Committee</th>
                    <th>Type</th>
                    <th>Project</th>
                    <th>Parent</th>
                    <th>Members</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="committeeTableBody">
                <tr><td colspan="6" class="muted">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- New/Edit Committee modal -->
<div class="modal-backdrop" id="committeeModalBackdrop">
    <div class="modal">
        <h2 id="committeeModalTitle">New Committee</h2>
        <p class="sub">Type = Sub-committee requires a Project (Policy §9). Main/Central committee has no project.</p>
        <div id="committeeModalError" class="error-box"></div>
        <form id="committeeForm">
            <input type="hidden" id="c_id">
            <div class="form-grid">
                <div class="field full">
                    <label for="c_name">Committee Name</label>
                    <input type="text" id="c_name" required>
                </div>
                <div class="field">
                    <label for="c_type">Type</label>
                    <select id="c_type" required>
                        <option value="main">Main / Central</option>
                        <option value="sub">Sub-committee</option>
                    </select>
                </div>
                <div class="field" id="c_project_field">
                    <label for="c_project">Project (required for Sub-committee)</label>
                    <select id="c_project"><option value="">—</option></select>
                </div>
                <div class="field">
                    <label for="c_parent">Parent Committee</label>
                    <select id="c_parent"><option value="">—</option></select>
                </div>
                <div class="field full">
                    <label for="c_address">Address</label>
                    <input type="text" id="c_address">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn outline" id="btnCancelCommittee">Cancel</button>
                <button type="submit" class="btn primary">Save Committee</button>
            </div>
        </form>
    </div>
</div>

<!-- Manage Members modal -->
<div class="modal-backdrop" id="membersModalBackdrop">
    <div class="modal">
        <h2>Members — <span id="membersCommitteeName"></span></h2>
        <p class="sub" id="membersCommitteeMeta"></p>
        <div id="membersModalError" class="error-box"></div>
        <div id="membersModalNotice" class="notice-box"></div>

        <div>
            <strong style="font-size:.85rem;">Current members</strong>
            <table class="current-members-table" id="currentMembersTable" style="margin-top:.5rem;">
                <tbody><tr><td class="muted">Loading…</td></tr></tbody>
            </table>
        </div>

        <div class="members-section">
            <h3>Copy from another committee</h3>
            <div class="copy-row">
                <select id="copySourceCommittee"><option value="">Select a committee…</option></select>
                <button type="button" class="btn sm outline" id="btnCopyMembers">Copy Members</button>
            </div>
            <p class="hint">Copies each member (with their designation on that committee) onto this one — you can edit designations before or after.</p>
        </div>

        <div class="members-section">
            <h3>Add from Committee Roster</h3>
            <p class="hint">Members are picked straight from the procurement committee roster (no login account needed) — same names/designations used on meeting documents. To give someone a login (so they can use "My Committee Work" etc.), assign them to this committee's Project from the Projects page instead — Manage Team there covers adding/creating Users.</p>
            <div class="user-checklist" id="rosterChecklist"><span class="empty">Loading…</span></div>
            <div class="modal-actions" style="margin-top:.75rem;">
                <button type="button" class="btn sm" id="btnAssignRoster">Add Selected</button>
            </div>
        </div>

        <div class="members-section">
            <h3>Add new users</h3>
            <div id="newUserRows"></div>
            <button type="button" class="btn outline sm" id="btnAddUserRow">+ Add another user</button>
        </div>

        <div class="modal-actions">
            <button type="button" class="btn" id="btnCloseMembers">Done</button>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    window.ADMIN_ROLES = <?php echo json_encode($roles, 15, 512) ?>;
    window.ADMIN_ROLE_LABELS = <?php echo json_encode($roleLabels, 15, 512) ?>;
</script>
<script src="<?php echo e(asset('js/admin-committees.js')); ?>"></script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/admin/committees.blade.php ENDPATH**/ ?>