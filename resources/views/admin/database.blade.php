@extends('layouts.app')

@section('title', 'ESDO Procurement — Data Manager')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

    :root {
        --ink: #0F172A; --paper: #FFFFFF; --surface: #F8FAFC; --line: #E2E8F0;
        --muted: #64748B; --accent: #0D9488; --accent-dark: #0F766E;
        --amber: #B45309; --amber-bg: #FFFBEB; --green: #15803D; --green-bg: #F0FDF4;
        --red: #B91C1C; --red-bg: #FEF2F2; --slate-bg: #F1F5F9;
    }
    body { font-family: 'Inter', system-ui, sans-serif; }
    /* This page needs more horizontal room than the layout's default
       1100px container (table sidebar + wide data grid), and it also
       has to coexist with the app shell's own 248px nav sidebar. */
    .container { max-width: 1400px; }
    .shell { max-width: 1400px; margin: 0 auto; padding: 2rem 2rem 4rem; }
    .eyebrow { font-family: 'JetBrains Mono', monospace; font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); margin: 0 0 .35rem; }
    .page-head { margin-bottom: 1.5rem; }
    .page-head h1 { margin: 0; font-size: 1.4rem; letter-spacing: -0.01em; }
    .page-head p { margin: .3rem 0 0; color: var(--muted); font-size: .86rem; max-width: 640px; }

    .layout { display: grid; grid-template-columns: 260px 1fr; gap: 1.5rem; align-items: start; }

    /* ---- Sidebar ---- */
    /* Renamed from .sidebar to avoid colliding with the app shell's
       own persistent left-nav .sidebar, which was silently inheriting
       this panel's white background and clobbering the dark nav. */
    .db-sidebar { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; position: sticky; top: 1rem; max-height: calc(100vh - 2rem); display: flex; flex-direction: column; }
    .sidebar-search { padding: .75rem; border-bottom: 1px solid var(--line); }
    .sidebar-search input { width: 100%; border: 1px solid var(--line); border-radius: 7px; padding: .5rem .65rem; font-size: .82rem; font-family: inherit; }
    .table-list { list-style: none; margin: 0; padding: .4rem; overflow-y: auto; }
    .table-list li { margin-bottom: 2px; }
    .table-list button { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: .5rem; text-align: left; background: none; border: none; border-radius: 7px; padding: .5rem .65rem; font-size: .82rem; cursor: pointer; color: var(--ink); font-family: inherit; }
    .table-list button:hover { background: var(--surface); }
    .table-list button.active { background: var(--ink); color: #fff; }
    .table-list .count { font-family: 'JetBrains Mono', monospace; font-size: .68rem; color: var(--muted); }
    .table-list button.active .count { color: #CBD5E1; }

    /* ---- Main panel ---- */
    .panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: .75rem; }
    .panel-head h2 { margin: 0; font-size: 1.1rem; }
    .panel-head .sub { color: var(--muted); font-size: .8rem; margin-top: .15rem; }

    .toolbar { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
    .toolbar input { border: 1px solid var(--line); border-radius: 7px; padding: .55rem .75rem; font-size: .85rem; font-family: inherit; min-width: 240px; flex: 1; }

    .btn { display: inline-flex; align-items: center; gap: .4rem; background: var(--ink); color: #fff; border: 1px solid var(--ink); border-radius: 7px; padding: .6rem 1.1rem; cursor: pointer; text-decoration: none; font-size: .85rem; font-weight: 600; transition: background .15s ease; font-family: inherit; }
    .btn:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
    .btn.primary { background: var(--accent); border-color: var(--accent); }
    .btn.primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
    .btn.outline { background: transparent; color: var(--ink); border: 1px solid var(--line); }
    .btn.outline:hover { background: var(--surface); border-color: #CBD5E1; }
    .btn.danger { background: var(--red); border-color: var(--red); }
    .btn.danger:hover { background: #991B1B; border-color: #991B1B; }
    .btn.sm { padding: .38rem .7rem; font-size: .76rem; }
    .btn[disabled] { opacity: .4; cursor: not-allowed; }

    .panel { background: var(--paper); border: 1px solid var(--line); border-radius: 12px; overflow: auto; max-width: 100%; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { text-align: left; padding: .65rem .85rem; font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 600; background: var(--surface); border-bottom: 1px solid var(--line); white-space: nowrap; }
    table.data td { padding: .65rem .85rem; font-size: .82rem; border-bottom: 1px solid var(--line); vertical-align: middle; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    table.data tr:last-child td { border-bottom: none; }
    table.data tr:hover td { background: #FAFBFC; }
    .row-actions { display: flex; gap: .4rem; }
    .muted { color: var(--muted); }

    .pager { display: flex; justify-content: space-between; align-items: center; padding: .75rem .85rem; border-top: 1px solid var(--line); font-size: .8rem; color: var(--muted); }
    .pager .controls { display: flex; gap: .4rem; }

    .error-box { background: var(--red-bg); color: var(--red); padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; display: none; }
    .notice-box { background: var(--green-bg); color: var(--green); padding: .7rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .85rem; display: none; }
    .empty-state { padding: 3rem 1.5rem; text-align: center; color: var(--muted); font-size: .88rem; }

    /* ---- Modal ---- */
    .modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.45); display: none; align-items: flex-start; justify-content: center; padding: 3rem 1rem; z-index: 50; overflow-y: auto; }
    .modal-backdrop.open { display: flex; }
    .modal { background: #fff; border-radius: 14px; width: 100%; max-width: 640px; padding: 1.75rem; box-shadow: 0 20px 60px rgba(15,23,42,.25); }
    .modal h2 { margin: 0 0 .25rem; font-size: 1.1rem; }
    .modal .sub { margin: 0 0 1.25rem; color: var(--muted); font-size: .82rem; }
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: .9rem 1.1rem; max-height: 60vh; overflow-y: auto; padding-right: .25rem; }
    .field { display: flex; flex-direction: column; }
    .field.full { grid-column: 1 / -1; }
    .field label { font-size: .78rem; color: var(--muted); margin-bottom: .3rem; font-weight: 500; }
    .field label .req { color: var(--red); }
    .field input, .field select, .field textarea { border: 1px solid var(--line); border-radius: 7px; padding: .55rem .7rem; font-size: .87rem; font-family: inherit; }
    .field textarea { min-height: 70px; resize: vertical; }
    .field.checkbox { flex-direction: row; align-items: center; gap: .5rem; }
    .field.checkbox input { width: auto; }
    .field.checkbox label { margin: 0; }
    .modal-actions { display: flex; justify-content: flex-end; gap: .6rem; margin-top: 1.25rem; }

    /* Stack the table-list sidebar above the data panel earlier than
       before (was 860px) — the app shell's own 248px nav sidebar is
       still visible down to 900px, so the two side-by-side sidebars
       would otherwise squeeze the data panel in the 900–1100px range. */
    @media (max-width: 1100px) {
        .layout { grid-template-columns: 1fr; }
        .db-sidebar { position: static; max-height: 260px; }
    }
</style>
@endsection

@section('content')
<div class="shell">
    <div class="page-head">
        <p class="eyebrow">Super Admin</p>
        <h1>Data Manager</h1>
        <p>Direct create / read / update / delete access to every table in the database. Changes here bypass normal workflow rules — use with care. (The <code>users</code> table has its own screen: <a href="{{ route('admin.users.index') }}">User Management</a>.)</p>
    </div>

    <div class="layout">
        <div class="db-sidebar">
            <div class="sidebar-search">
                <input type="text" id="tableSearch" placeholder="Filter tables…">
            </div>
            <ul class="table-list" id="tableList">
                <li class="muted" style="padding:.5rem .65rem;">Loading tables…</li>
            </ul>
        </div>

        <div>
            <div id="errorBox" class="error-box"></div>
            <div id="noticeBox" class="notice-box"></div>

            <div id="mainPanel">
                <div class="empty-state">Pick a table on the left to view and edit its rows.</div>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="rowModalBackdrop">
    <div class="modal">
        <h2 id="modalTitle">New Row</h2>
        <p class="sub" id="modalSub"></p>
        <div id="modalError" class="error-box"></div>
        <form id="rowForm">
            <div class="form-grid" id="formFields"></div>
            <div class="modal-actions">
                <button type="button" class="btn outline" id="btnCancel">Cancel</button>
                <button type="submit" class="btn primary" id="btnSave">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/admin-database.js') }}"></script>
@endsection