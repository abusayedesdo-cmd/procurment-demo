<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'ESDO Procurement'); ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

        :root {
            --ink: #0F172A;
            --ink-2: #1E293B;
            --paper: #FFFFFF;
            --surface: #F8FAFC;
            --line: #E2E8F0;
            --muted: #64748B;
            --accent: #0D9488;
            --accent-dark: #0F766E;
            --amber: #B45309;
            --amber-bg: #FFFBEB;
            --amber-line: #FDE68A;
            --green: #15803D;
            --green-bg: #F0FDF4;
            --green-line: #BBF7D0;
            --red: #991B1B;
            --red-bg: #FEF2F2;
            --red-line: #FECACA;
            --slate-bg: #F1F5F9;
            --sidebar-w: 248px;
        }

        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--surface);
            margin: 0;
            color: var(--ink);
        }

        /* ---- App shell ---- */
        .app-shell { display: flex; min-height: 100vh; }

        /* ---- Sidebar ---- */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--ink);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            border-right: 2px solid var(--accent);
        }
        .sidebar a { color: inherit; text-decoration: none; }

        .brand {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid #1E293B;
        }
        .brand-logo { height: 30px; width: auto; display: block; }
        .brand-text { display: flex; flex-direction: column; line-height: 1.15; }
        .brand-text .name { font-weight: 700; font-size: .95rem; letter-spacing: -0.01em; }
        .brand-text .sub {
            font-family: 'JetBrains Mono', monospace;
            font-size: .6rem;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #94A3B8;
        }

        .nav-links {
            display: flex;
            flex-direction: column;
            padding: .75rem;
            gap: .15rem;
            overflow-y: auto;
        }
        .nav-links a {
            font-size: .87rem;
            font-weight: 500;
            color: #CBD5E1;
            padding: .6rem .75rem;
            border-radius: 7px;
            border-left: 3px solid transparent;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }
        .nav-links a:hover {
            background: #1E293B;
            color: #fff;
            border-left-color: var(--accent);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: .9rem 1.1rem 1.1rem;
            border-top: 1px solid #1E293B;
        }
        .user-row { display: flex; align-items: center; gap: .6rem; margin-bottom: .75rem; }
        .avatar {
            width: 30px; height: 30px;
            border-radius: 999px;
            background: var(--accent);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: .78rem; font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            flex-shrink: 0;
        }
        .user-meta { display: flex; flex-direction: column; gap: .2rem; min-width: 0; }
        .user-name {
            font-size: .84rem; color: #fff; font-weight: 600;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .user-role {
            align-self: flex-start;
            font-size: .62rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--accent);
            background: rgba(13,148,136,.15);
            border: 1px solid rgba(13,148,136,.35);
            padding: .1rem .45rem;
            border-radius: 999px;
        }

        /* ---- Mobile topbar (hidden on desktop) ---- */
        .mobile-topbar {
            display: none;
            align-items: center;
            gap: .75rem;
            background: var(--ink);
            color: #fff;
            padding: .65rem 1rem;
            border-bottom: 2px solid var(--accent);
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .sidebar-toggle {
            background: transparent;
            border: 1px solid #334155;
            color: #fff;
            border-radius: 6px;
            width: 34px; height: 34px;
            font-size: 1.1rem;
            cursor: pointer;
            flex-shrink: 0;
        }
        .sidebar-toggle:hover { background: #1E293B; }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            z-index: 40;
        }

        /* ---- Main content ---- */
        .main-wrap { flex: 1; min-width: 0; }
        .container { padding: 1.5rem; max-width: 1100px; margin: 0 auto; }

        .card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 1.35rem 1.5rem;
            margin-bottom: 1rem;
        }

        /* ---- Tables ---- */
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: .7rem .6rem;
            border-bottom: 1px solid var(--line);
            font-size: .88rem;
        }
        th {
            color: var(--muted);
            font-weight: 600;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        tbody tr:hover td { background: var(--surface); }

        /* ---- Buttons ---- */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: var(--ink);
            color: #fff;
            border: 1px solid var(--ink);
            border-radius: 7px;
            padding: .55rem 1.05rem;
            cursor: pointer;
            text-decoration: none;
            font-size: .85rem;
            font-weight: 600;
            font-family: inherit;
            transition: background .15s ease, border-color .15s ease;
        }
        .btn:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
        .btn.primary { background: var(--accent); border-color: var(--accent); }
        .btn.primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
        .btn.secondary { background: transparent; color: var(--ink); border: 1px solid var(--line); }
        .btn.secondary:hover { background: var(--surface); border-color: #CBD5E1; }
        .btn.danger { background: var(--red); border-color: var(--red); }
        .btn.danger:hover { background: #7F1D1D; border-color: #7F1D1D; }
        .btn:disabled { opacity: .5; cursor: not-allowed; }

        .sidebar-footer .btn.secondary {
            width: 100%;
            justify-content: center;
            background: transparent;
            color: #E2E8F0;
            border: 1px solid #334155;
            padding: .5rem .8rem;
            font-size: .82rem;
        }
        .sidebar-footer .btn.secondary:hover {
            background: #1E293B;
            border-color: #475569;
            color: #fff;
        }

        /* ---- Forms ---- */
        label { display: block; font-size: .78rem; font-weight: 600; color: var(--muted); margin: 0 0 .4rem; }
        input, select, textarea {
            width: 100%;
            padding: .55rem .7rem;
            border: 1px solid var(--line);
            border-radius: 7px;
            font-size: .88rem;
            font-family: inherit;
            color: var(--ink);
            background: var(--paper);
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(13,148,136,.12);
        }

        /* Old simple row grid — still used by the PR create form */
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .75rem; align-items: start; }
        .item-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: .5rem; align-items: end; margin-bottom: .5rem; }

        /* New generic module form grid — every field is its own block,
           hidden/currentUser fields are excluded entirely, textarea and
           checkbox fields always take the full row width. */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem 1.25rem; align-items: start; }
        .form-field { display: flex; flex-direction: column; }
        .form-field.full-width { grid-column: 1 / -1; }
        .form-field.checkbox-field label { display: flex; align-items: center; gap: .5rem; font-size: .87rem; color: var(--ink); font-weight: 500; }
        .form-field.checkbox-field input[type="checkbox"] { width: auto; accent-color: var(--accent); }

        /* ---- Badges ---- */
        .badge {
            display: inline-block;
            padding: .2rem .65rem;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            border: 1px solid transparent;
        }
        .badge.draft { background: var(--slate-bg); color: #475569; border-color: var(--line); }
        .badge.reviewed, .badge.checked { background: var(--amber-bg); color: var(--amber); border-color: var(--amber-line); }
        .badge.approved { background: var(--green-bg); color: var(--green); border-color: var(--green-line); }
        .badge.rejected { background: var(--red-bg); color: var(--red); border-color: var(--red-line); }

        .error-box {
            background: var(--red-bg);
            color: var(--red);
            border: 1px solid var(--red-line);
            padding: .75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: .88rem;
        }
        .muted { color: var(--muted); font-size: .85rem; }

        /* ---- Responsive: sidebar becomes an off-canvas drawer ---- */
        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                top: 0; left: 0;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform .2s ease;
                z-index: 50;
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar-backdrop.show { display: block; }
            .mobile-topbar { display: flex; }
        }

        @media (max-width: 560px) {
            .container { padding: 1.1rem; }
            :root { --sidebar-w: 84vw; }
        }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar" id="sidebar">
            <a href="<?php echo e(route('dashboard')); ?>" class="brand">
                <img
                    src="<?php echo e(asset('img/esdo-logo.png')); ?>"
                    alt="ESDO"
                    class="brand-logo"
                    onerror="this.style.display='none'"
                >
                <span class="brand-text">
                    <span class="name">ESDO</span>
                    <span class="sub">Procurement</span>
                </span>
            </a>

            <nav class="nav-links">
                <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                <a href="<?php echo e(route('purchase-requisitions.index')); ?>">Purchase Requisitions</a>
                <?php if(in_array(auth()->user()->roleName(), [\App\Models\User::BUDGET_CHECKER, \App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN])): ?>
                    <a href="<?php echo e(route('budget-dashboard')); ?>">Budget Dashboard</a>
                    <a href="<?php echo e(route('annual-plans.index')); ?>">Annual Plan</a>
                <?php endif; ?>
                <?php if(in_array(auth()->user()->roleName(), [\App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN])): ?>
                    <a href="<?php echo e(route('modules.index')); ?>">All Modules</a>
                <?php endif; ?>
                <?php if(auth()->user()->roleName() === \App\Models\User::ADMIN): ?>
                    <a href="<?php echo e(route('admin.users.index')); ?>">User Management</a>
                    <a href="<?php echo e(route('admin.database.index')); ?>">Data Manager</a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="user-row">
                    <span class="avatar"><?php echo e(strtoupper(substr(auth()->user()->name ?? '?', 0, 1))); ?></span>
                    <span class="user-meta">
                        <span class="user-name"><?php echo e(auth()->user()->name ?? ''); ?></span>
                        <span class="user-role"><?php echo e(\App\Models\User::ROLE_LABELS[auth()->user()->roleName()] ?? auth()->user()->roleName()); ?></span>
                    </span>
                </div>
                <form method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn secondary">Sign out</button>
                </form>
            </div>
        </aside>

        <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

        <div class="main-wrap">
            <div class="mobile-topbar">
                <button type="button" class="sidebar-toggle" onclick="openSidebar()" aria-label="Open menu">☰</button>
                <span class="brand-text">
                    <span class="name">ESDO Procurement</span>
                </span>
            </div>

            <div class="container">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>

    <script>
        const sidebarEl = document.getElementById('sidebar');
        const backdropEl = document.getElementById('sidebarBackdrop');

        function openSidebar() {
            sidebarEl.classList.add('open');
            backdropEl.classList.add('show');
        }
        function closeSidebar() {
            sidebarEl.classList.remove('open');
            backdropEl.classList.remove('show');
        }

        // Close the drawer automatically if the viewport grows back to desktop size.
        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) closeSidebar();
        });
    </script>
    <script src="<?php echo e(asset('js/api.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html><?php /**PATH D:\New Poject\Project_procrument\resources\views/layouts/app.blade.php ENDPATH**/ ?>