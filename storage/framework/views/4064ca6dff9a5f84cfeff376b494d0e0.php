<?php $__env->startSection('title', 'ESDO Procurement — User Management'); ?>

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

    .shell { max-width: 1180px; margin: 0 auto; padding: 2rem 2rem 4rem; }

    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem; font-weight: 600; letter-spacing: .1em;
        text-transform: uppercase; color: var(--muted); margin: 0 0 .35rem;
    }

    .page-head { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.75rem; flex-wrap: wrap; gap: 1rem; }
    .page-head h1 { margin: 0; font-size: 1.4rem; letter-spacing: -0.01em; }
    .page-head p { margin: .3rem 0 0; color: var(--muted); font-size: .88rem; max-width: 560px; }

    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .stat-card { background: var(--surface); border-radius: 10px; padding: 1.1rem 1.25rem; border-left: 3px solid var(--line); }
    .stat-card[data-tone="brand"] { border-left-color: var(--accent); }
    .stat-card[data-tone="approved"] { border-left-color: var(--green); }
    .stat-card[data-tone="pending"] { border-left-color: var(--amber); }
    .stat-card h3 { margin: 0 0 .5rem; font-size: .72rem; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; font-weight: 600; }
    .stat-card p { margin: 0; font-family: 'JetBrains Mono', monospace; font-size: 1.7rem; font-weight: 700; color: var(--ink); }

    .toolbar { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
    .toolbar input, .toolbar select {
        border: 1px solid var(--line); border-radius: 7px; padding: .55rem .75rem;
        font-size: .85rem; font-family: inherit; background: var(--paper);
    }
    .toolbar input { min-width: 220px; flex: 1; }
    .toolbar .spacer { flex: 1; }

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
    .btn.danger { background: var(--red); border-color: var(--red); }
    .btn.danger:hover { background: #991B1B; border-color: #991B1B; }
    .btn.sm { padding: .38rem .7rem; font-size: .76rem; }
    .btn[disabled] { opacity: .4; cursor: not-allowed; }

    .panel { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { text-align: left; padding: .75rem 1rem; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 600; background: var(--surface); border-bottom: 1px solid var(--line); }
    table.data td { padding: .8rem 1rem; font-size: .86rem; border-bottom: 1px solid var(--line); vertical-align: middle; }
    table.data tr:last-child td { border-bottom: none; }
    table.data tr:hover td { background: #FAFBFC; }
    .name-cell .who { font-weight: 600; color: var(--ink); }
    .name-cell .email { color: var(--muted); font-size: .78rem; }
    .row-actions { display: flex; gap: .4rem; flex-wrap: wrap; }

    .badge { display: inline-block; padding: .18rem .6rem; border-radius: 999px; font-size: .72rem; font-weight: 600; letter-spacing: .02em; }
    .badge.role-admin { background: #EDE9FE; color: #5B21B6; }
    .badge.role-procurement_officer { background: var(--green-bg); color: var(--green); }
    .badge.role-budget_checker { background: var(--amber-bg); color: var(--amber); }
    .badge.role-approver { background: #DBEAFE; color: #1D4ED8; }
    .badge.role-reviewer { background: #FCE7F3; color: #9D174D; }
    .badge.role-requester { background: var(--slate-bg); color: var(--muted); }
    .badge.status-active { background: var(--green-bg); color: var(--green); }
    .badge.status-inactive { background: var(--red-bg); color: var(--red); }

    .muted { color: var(--muted); }
    .error-box { background: var(--red-bg); color: var(--red); padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; display: none; }
    .notice-box { background: var(--green-bg); color: var(--green); padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; display: none; }

    /* ---- Modal ---- */
    .modal-backdrop {
        position: fixed; inset: 0; background: rgba(15,23,42,.45);
        display: none; align-items: flex-start; justify-content: center;
        padding: 4rem 1rem; z-index: 50; overflow-y: auto;
    }
    .modal-backdrop.open { display: flex; }
    .modal {
        background: #fff; border-radius: 14px; width: 100%; max-width: 520px;
        padding: 1.75rem; box-shadow: 0 20px 60px rgba(15,23,42,.25);
    }
    .modal h2 { margin: 0 0 .25rem; font-size: 1.1rem; }
    .modal .sub { margin: 0 0 1.25rem; color: var(--muted); font-size: .82rem; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .9rem 1.1rem; }
    .field { display: flex; flex-direction: column; }
    .field.full { grid-column: 1 / -1; }
    .field label { font-size: .78rem; color: var(--muted); margin-bottom: .3rem; font-weight: 500; }
    .field input, .field select {
        border: 1px solid var(--line); border-radius: 7px; padding: .55rem .7rem;
        font-size: .87rem; font-family: inherit;
    }
    .field.checkbox { flex-direction: row; align-items: center; gap: .5rem; }
    .field.checkbox input { width: auto; }
    .field.checkbox label { margin: 0; }
    .modal-actions { display: flex; justify-content: flex-end; gap: .6rem; margin-top: 1.5rem; }
    .hint { font-size: .74rem; color: var(--muted); margin-top: .25rem; }

    @media (max-width: 640px) {
        .shell { padding: 1.25rem 1rem 3rem; }
        table.data { font-size: .8rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="shell">
    <div class="page-head">
        <div>
            <p class="eyebrow">Super Admin</p>
            <h1>User Management</h1>
            <p>Create accounts, assign roles, and manage access for everyone using the Procurement system.</p>
        </div>
        <button class="btn primary" id="btnNewUser">+ New User</button>
    </div>

    <div class="stat-grid" id="statGrid">
        <div class="stat-card" data-tone="brand"><h3>Total Users</h3><p id="statTotal">—</p></div>
        <div class="stat-card" data-tone="approved"><h3>Active</h3><p id="statActive">—</p></div>
        <div class="stat-card" data-tone="pending"><h3>Inactive</h3><p id="statInactive">—</p></div>
        <div class="stat-card"><h3>Admins</h3><p id="statAdmins">—</p></div>
    </div>

    <div id="errorBox" class="error-box"></div>
    <div id="noticeBox" class="notice-box"></div>

    <div class="toolbar">
        <input type="text" id="searchInput" placeholder="Search name, email, phone, designation…">
        <select id="roleFilter"><option value="">All roles</option></select>
        <select id="statusFilter">
            <option value="">All statuses</option>
            <option value="active">Active only</option>
            <option value="inactive">Inactive only</option>
        </select>
    </div>

    <div class="panel">
        <table class="data">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Phone</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <tr><td colspan="6" class="muted">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-backdrop" id="userModalBackdrop">
    <div class="modal">
        <h2 id="modalTitle">New User</h2>
        <p class="sub" id="modalSub">Create a login for a staff member and assign their role.</p>
        <div id="modalError" class="error-box"></div>
        <form id="userForm">
            <div class="form-grid">
                <div class="field full">
                    <label for="f_name">Full name</label>
                    <input type="text" id="f_name" required>
                </div>
                <div class="field full">
                    <label for="f_email">Email</label>
                    <input type="email" id="f_email" required>
                </div>
                <div class="field">
                    <label for="f_role">Role</label>
                    <select id="f_role" required></select>
                </div>
                <div class="field">
                    <label for="f_phone">Phone</label>
                    <input type="text" id="f_phone">
                </div>
                <div class="field full">
                    <label for="f_designation">Designation</label>
                    <input type="text" id="f_designation" placeholder="e.g. Procurement Officer">
                </div>
                <div class="field full" id="passwordField">
                    <label for="f_password">Password</label>
                    <input type="text" id="f_password" autocomplete="off">
                    <div class="hint" id="passwordHint">Minimum 8 characters. Shown as plain text so you can share it with the user.</div>
                </div>
                <div class="field checkbox full">
                    <input type="checkbox" id="f_is_active" checked>
                    <label for="f_is_active">Account is active</label>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn outline" id="btnCancel">Cancel</button>
                <button type="submit" class="btn primary" id="btnSave">Save User</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    window.currentUserId = <?php echo e(auth()->id()); ?>;
    window.ADMIN_ROLES = <?php echo json_encode($roles, 15, 512) ?>;
    window.ADMIN_ROLE_LABELS = <?php echo json_encode($roleLabels, 15, 512) ?>;
</script>
<script src="<?php echo e(asset('js/admin-users.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/admin/users.blade.php ENDPATH**/ ?>