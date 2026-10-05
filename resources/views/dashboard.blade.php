@extends('layouts.app')

@section('title', 'ESDO Procurement — Dashboard')

@section('styles')
<style>
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
        --slate-bg: #F1F5F9;
    }

    .shell { max-width: 1160px; margin: 0 auto; padding: 2rem 2rem 4rem; }
    .dash-section { margin-bottom: 1.75rem; }
    .eyebrow { font-size: .72rem; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--muted); margin: 0 0 .75rem; }

    /* ---- Header ---- */
    .dash-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1.75rem; }
    .dash-head h1 { margin: 0; font-size: 1.55rem; font-weight: 700; letter-spacing: -.02em; color: var(--ink); }
    .dash-head .sub { margin-top: .25rem; font-size: .88rem; color: var(--muted); }
    .dash-head .sub b { color: var(--ink); font-weight: 600; }
    .proj-chip { display: inline-block; padding: .08rem .6rem; border-radius: 999px; background: #F0FDFA; border: 1px solid #99F6E4; color: var(--accent-dark); font-size: .76rem; font-weight: 600; vertical-align: 1px; }
    .dash-head .date { margin-top: .15rem; font-size: .8rem; color: var(--muted); }
    .user-block { display: flex; align-items: center; gap: .75rem; }

    /* ---- Panels ---- */
    .panel { background: var(--paper); border: 1px solid var(--line); border-radius: 14px; padding: 1.15rem 1.25rem; }
    .panel-head { display: flex; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .85rem; }
    .panel-head h2 { margin: 0; font-size: .98rem; font-weight: 700; color: var(--ink); }
    .panel-head .count { font-size: .75rem; font-weight: 700; background: var(--slate-bg); color: #334155; border-radius: 999px; padding: .1rem .6rem; }
    .panel-head a.more { font-size: .8rem; font-weight: 600; color: var(--accent-dark); text-decoration: none; }
    .panel-head a.more:hover { text-decoration: underline; }

    /* ---- KPI cards ---- */
    .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem; }
    a.kpi { text-decoration: none; color: inherit; display: block; }
    .kpi-card { background: var(--paper); border: 1px solid var(--line); border-radius: 14px; padding: 1.1rem 1.2rem; display: flex; gap: .9rem; align-items: center; transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease; height: 100%; }
    a.kpi:hover .kpi-card { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(15,23,42,.08); border-color: #CBD5E1; }
    .kpi-icon { flex: 0 0 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: var(--slate-bg); color: #475569; }
    .kpi-icon svg { width: 22px; height: 22px; }
    .kpi-card[data-tone="pending"] .kpi-icon { background: var(--amber-bg); color: var(--amber); }
    .kpi-card[data-tone="approved"] .kpi-icon { background: var(--green-bg); color: var(--green); }
    .kpi-card[data-tone="brand"] .kpi-icon { background: #F0FDFA; color: var(--accent-dark); }
    .kpi-card[data-tone="violet"] .kpi-icon { background: #F5F3FF; color: #6D28D9; }
    .kpi-body { min-width: 0; }
    .kpi-label { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); }
    .kpi-value { font-size: 1.85rem; font-weight: 700; line-height: 1.15; color: var(--ink); letter-spacing: -.02em; }
    .kpi-hint { font-size: .76rem; color: var(--muted); margin-top: .1rem; }

    /* ---- PR pipeline ---- */
    .pipeline { display: grid; grid-template-columns: repeat(5, 1fr); gap: .5rem; }
    a.stage { text-decoration: none; color: inherit; display: block; }
    .stage-box { position: relative; border: 1px solid var(--line); border-radius: 12px; padding: .8rem .9rem; background: var(--surface); transition: border-color .12s ease, background .12s ease; height: 100%; }
    a.stage:hover .stage-box { border-color: #CBD5E1; background: #fff; }
    .stage-box .n { font-size: 1.5rem; font-weight: 700; color: var(--ink); line-height: 1.1; }
    .stage-box .l { font-size: .76rem; font-weight: 600; color: var(--muted); margin-top: .15rem; }
    .stage-box.has { background: #fff; }
    .stage-box.has .n { color: var(--accent-dark); }
    .stage-box.last.has .n { color: var(--green); }
    .stage-bar { height: 4px; border-radius: 999px; background: var(--line); margin-top: .6rem; overflow: hidden; }
    .stage-bar > i { display: block; height: 100%; background: var(--accent); border-radius: 999px; }

    /* ---- Two-column area ---- */
    .dash-cols { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); gap: 1.25rem; align-items: start; }
    .dash-cols.single { grid-template-columns: 1fr; }
    .dash-cols.single .quick { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); }

    /* ---- Task list ---- */
    .task-list { display: flex; flex-direction: column; }
    .task { display: flex; align-items: center; gap: .85rem; padding: .7rem .1rem; border-top: 1px solid var(--line); text-decoration: none; color: inherit; }
    .task:first-child { border-top: 0; }
    .task:hover .task-go { background: var(--accent); color: #fff; border-color: var(--accent); }
    .task-pill { flex: 0 0 auto; font-size: .7rem; font-weight: 700; padding: .18rem .6rem; border-radius: 999px; background: var(--amber-bg); color: var(--amber); white-space: nowrap; }
    .task-pill.transfer { background: #EEF2FF; color: #4338CA; }
    .task-main { flex: 1; min-width: 0; }
    .task-title { font-size: .9rem; font-weight: 600; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .task-meta { font-size: .76rem; color: var(--muted); margin-top: .1rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .task-go { flex: 0 0 auto; font-size: .78rem; font-weight: 600; padding: .35rem .8rem; border-radius: 8px; border: 1px solid var(--line); color: var(--ink); background: #fff; transition: all .12s ease; }
    .empty { text-align: center; padding: 1.6rem 1rem; color: var(--muted); font-size: .88rem; }
    .empty svg { width: 30px; height: 30px; color: var(--green); display: block; margin: 0 auto .5rem; }

    /* ---- Quick actions ---- */
    .quick { display: flex; flex-direction: column; gap: .5rem; }
    .quick a { display: flex; align-items: center; gap: .7rem; padding: .65rem .8rem; border: 1px solid var(--line); border-radius: 10px; text-decoration: none; color: var(--ink); font-size: .88rem; font-weight: 600; background: #fff; transition: border-color .12s ease, background .12s ease; }
    .quick a:hover { border-color: var(--accent); background: #F0FDFA; }
    .quick a svg { width: 18px; height: 18px; color: var(--accent-dark); flex: 0 0 18px; }
    .quick a .badge { margin-left: auto; font-size: .72rem; background: var(--slate-bg); color: #334155; border-radius: 999px; padding: .05rem .55rem; }
    .quick a.primary { background: var(--ink); color: #fff; border-color: var(--ink); }
    .quick a.primary svg { color: #fff; }
    .quick a.primary:hover { background: #1E293B; }

    /* ---- Status badges + recent table ---- */
    .st { display: inline-block; padding: .12rem .6rem; border-radius: 999px; font-size: .73rem; font-weight: 600; background: var(--slate-bg); color: #334155; white-space: nowrap; }
    .st-draft { background: #F1F5F9; color: #475569; }
    .st-reviewed { background: #FEF3C7; color: #92400E; }
    .st-checked { background: #E0F2FE; color: #075985; }
    .st-focal_reviewed { background: #EDE9FE; color: #5B21B6; }
    .st-approved { background: #DCFCE7; color: #166534; }
    .st-rejected { background: #FEE2E2; color: #991B1B; }
    table.recent { width: 100%; border-collapse: collapse; }
    table.recent th { text-align: left; font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; color: var(--muted); font-weight: 700; padding: .5rem .6rem; border-bottom: 1px solid var(--line); }
    table.recent td { padding: .7rem .6rem; border-bottom: 1px solid var(--line); font-size: .86rem; vertical-align: middle; }
    table.recent tr:last-child td { border-bottom: 0; }
    table.recent tbody tr:hover td { background: var(--surface); }
    table.recent td.num, table.recent th.num { text-align: right; }
    table.recent a.prno { font-weight: 700; color: var(--ink); text-decoration: none; }
    table.recent a.prno:hover { color: var(--accent-dark); text-decoration: underline; }

    /* ---- Responsive ---- */
    @media (max-width: 960px) {
        .dash-cols { grid-template-columns: 1fr; }
        .pipeline { grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); }
    }
    @media (max-width: 760px) {
        .shell { padding: 1.25rem 1rem 3rem; }
        .dash-head h1 { font-size: 1.3rem; }
        .task { flex-wrap: wrap; }
        .task-main { flex: 1 1 60%; }
        .task-go { margin-left: auto; }
        table.recent, table.recent tbody, table.recent tr, table.recent td { display: block; width: 100%; }
        table.recent thead { display: none; }
        table.recent tr { border: 1px solid var(--line); border-radius: 12px; padding: .5rem .85rem; margin-bottom: .7rem; }
        table.recent td { border: 0; padding: .25rem 0; display: flex; justify-content: space-between; gap: .75rem; text-align: right; }
        table.recent td::before { content: attr(data-label); font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; font-weight: 700; color: var(--muted); text-align: left; }
        table.recent td.num { text-align: right; }
        table.recent tbody tr:hover td { background: transparent; }
    }

    /* ---- Notification bell (existing behaviour) ---- */
    .notif-bell svg { width: 20px; height: 20px; display: block; }
    .notif-wrap { position: relative; }

    .notif-bell {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid var(--line);
        background: var(--surface);
        cursor: pointer;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color .15s ease, background .15s ease;
    }

    .notif-bell:hover { border-color: #CBD5E1; background: #fff; }

    .notif-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #DC2626;
        color: #fff;
        font-family: 'JetBrains Mono', monospace;
        font-size: .64rem;
        font-weight: 700;
        min-width: 18px;
        height: 18px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        border: 2px solid var(--paper);
        line-height: 1;
    }

    .notif-panel {
        display: none;
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        width: 340px;
        max-height: 440px;
        overflow-y: auto;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 10px;
        box-shadow: 0 14px 34px rgba(15,23,42,.16);
        z-index: 60;
    }

    .notif-panel.open { display: block; }

    .notif-panel-head {
        position: sticky;
        top: 0;
        background: var(--paper);
        padding: .8rem 1rem;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .notif-panel-head span.title {
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
    }

    .notif-panel-head span.count {
        background: var(--amber-bg);
        color: var(--amber);
        font-size: .68rem;
        font-weight: 700;
        padding: .1rem .55rem;
        border-radius: 999px;
        border: 1px solid #FDE68A;
    }

    .notif-list { padding: .4rem; }

    .notif-item {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .65rem .7rem;
        border-radius: 8px;
        text-decoration: none;
        color: var(--ink);
        font-size: .85rem;
    }

    .notif-item:hover { background: var(--surface); }

    .notif-item .dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: var(--accent);
        flex-shrink: 0;
    }

    .notif-item .label { color: var(--muted); }
    .notif-item .prno { font-family: 'JetBrains Mono', monospace; font-weight: 700; }

    .notif-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--muted);
        font-size: .85rem;
    }

    @media (max-width: 560px) {
        .notif-panel { width: 88vw; right: -0.5rem; }
    }
</style>
@endsection

@section('content')
    @php
        $role = $user->roleName() ?? null;
        $isAdmin = $role === \App\Models\User::ADMIN;
        $canSeeModules = in_array($role, [\App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN]);
        $canSeeAnnualPlan = in_array($role, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN]);
        $canSeeBudget = in_array($role, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN]);
        $canCreatePr = in_array($role, \App\Models\User::PR_CREATOR_ROLES);

        $canReview = in_array($role, [\App\Models\User::REVIEWER, \App\Models\User::ADMIN]);
        $canCheckBudget = in_array($role, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN]);
        $canApprove = in_array($role, [\App\Models\User::APPROVER, \App\Models\User::ADMIN]);
        $canFocalReview = in_array($role, [\App\Models\User::FOCAL_PERSON, \App\Models\User::ADMIN]);
        $canEdApprove = in_array($role, [\App\Models\User::EXECUTIVE_DIRECTOR, \App\Models\User::ADMIN]);

        // Everything waiting on this user: PRs at their approval stage + PRs transferred to their committee.
        $notifItems = collect();
        if ($canReview) { foreach ($awaitingReview as $pr) { $notifItems->push(['label' => 'Review', 'pr' => $pr]); } }
        if ($canCheckBudget) { foreach ($awaitingBudgetCheck as $pr) { $notifItems->push(['label' => 'Check Budget', 'pr' => $pr]); } }
        if ($canFocalReview) { foreach ($awaitingFocalReview as $pr) { $notifItems->push(['label' => 'Focal Review', 'pr' => $pr]); } }
        if ($canEdApprove) { foreach ($awaitingEdApproval as $pr) { $notifItems->push(['label' => 'ED Approval', 'pr' => $pr]); } }
        if ($canApprove) { foreach ($awaitingApproval as $pr) { $notifItems->push(['label' => 'Approve', 'pr' => $pr]); } }
        foreach (($transferredToMe ?? collect()) as $t) {
            $notifItems->push([
                'label' => 'Transferred',
                'pr' => (object) ['pr_number' => $t->pr_number],
                'url' => $t->case_id ? route('cases.show', $t->case_id) : route('committee-work.index'),
                'detail' => trim(($t->from ? 'from ' . $t->from . ' ' : '') . '→ ' . $t->to . ($t->date ? ' · ' . $t->date->format('d M Y') : '')),
            ]);
        }
        $notifCount = $notifItems->count();
        // Only roles that approve/check PRs, or sit on a committee, get a "Needs your action" panel.
        $hasTaskRole = $canReview || $canCheckBudget || $canApprove || $canFocalReview || $canEdApprove || $hasCommittee;

        $dhaka = now('Asia/Dhaka');
        $greeting = $dhaka->hour < 12 ? 'Good morning' : ($dhaka->hour < 17 ? 'Good afternoon' : 'Good evening');
        $firstName = trim(explode(' ', trim($user->name ?? ''))[0] ?? '');

        $svg = [
            'file' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
            'calendar' => '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4M16 3v4M4 10h16"/>',
            'layers' => '<path d="m12 3 9 5-9 5-9-5z"/><path d="m3 13 9 5 9-5"/>',
            'award' => '<circle cx="12" cy="9" r="5"/><path d="m9 13-1 8 4-2 4 2-1-8"/>',
            'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20a6 6 0 0 1 12 0"/><circle cx="17" cy="9" r="2.5"/><path d="M21 19a5 5 0 0 0-4-4.8"/>',
            'bell' => '<path d="M6 9a6 6 0 0 1 12 0c0 6 2 7 2 7H4s2-1 2-7"/><path d="M10 20a2 2 0 0 0 4 0"/>',
            'plus' => '<path d="M12 5v14M5 12h14"/>',
            'list' => '<path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/>',
            'wallet' => '<path d="M3 7a2 2 0 0 1 2-2h13v4"/><path d="M3 7v11a2 2 0 0 0 2 2h14a1 1 0 0 0 1-1v-9a1 1 0 0 0-1-1H5a2 2 0 0 1-2-2z"/><circle cx="16.5" cy="14.5" r="1"/>',
            'grid' => '<rect x="4" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.5"/>',
        ];
        $icon = fn ($n) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($svg[$n] ?? '') . '</svg>';

        $stageLabels = [
            'draft' => 'Draft',
            'reviewed' => 'Reviewed',
            'checked' => 'Budget checked',
            'focal_reviewed' => 'Focal reviewed',
            'approved' => 'Approved',
        ];
        $stageTotal = max(1, collect($stageLabels)->keys()->sum(fn ($k) => (int) ($stageCounts[$k] ?? 0)));
        $money = fn ($v) => '৳ ' . number_format((float) $v, 2);
    @endphp

    <div class="shell">
        {{-- ===== Header ===== --}}
        <div class="dash-head">
            <div>
                <h1>{{ $greeting }}{{ $firstName !== '' ? ', ' . $firstName : '' }}</h1>
                @if (!empty($user->designation))
                    <div class="sub"><b>{{ $user->designation }}</b></div>
                @endif
                <div class="date">{{ $dhaka->format('l, d F Y') }}</div>
            </div>
            <div class="user-block">
                <div class="notif-wrap">
                    <button type="button" class="notif-bell" onclick="toggleNotifPanel(event)" aria-label="Pending actions">
                        {!! $icon('bell') !!}
                        @if ($notifCount > 0)
                            <span class="notif-badge">{{ $notifCount > 99 ? '99+' : $notifCount }}</span>
                        @endif
                    </button>
                    <div class="notif-panel" id="notifPanel">
                        <div class="notif-panel-head">
                            <span class="title">Pending Actions</span>
                            <span class="count">{{ $notifCount }}</span>
                        </div>
                        <div class="notif-list">
                            @forelse ($notifItems as $item)
                                <a href="{{ $item['url'] ?? (route('purchase-requisitions.show', $item['pr']->id) . '#budget-check') }}" class="notif-item" @if (!empty($item['detail'])) title="{{ $item['detail'] }}" @endif>
                                    <span class="dot"></span>
                                    <span class="label">{{ $item['label'] }} —</span>
                                    <span class="prno">{{ $item['pr']->pr_number }}</span>
                                    @if (!empty($item['detail']))
                                        <span class="label" style="font-size:.72rem;margin-left:auto;">{{ $item['detail'] }}</span>
                                    @endif
                                </a>
                            @empty
                                <div class="notif-empty">No pending actions</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== KPI cards ===== --}}
        <div class="dash-section">
            @php
                // Project the user works under; Admin / Procurement Officer have none → "All projects".
                $projectLabel = $user->project?->name ?: ($canSeeModules ? 'All projects' : null);
            @endphp
            <p class="eyebrow">Overview @if ($projectLabel) · <span class="proj-chip">{{ $projectLabel }}</span> @endif</p>
            <div class="kpi-grid">
                <a class="kpi" href="{{ route('purchase-requisitions.index') }}?status=draft">
                    <div class="kpi-card" data-tone="neutral">
                        <div class="kpi-icon">{!! $icon('file') !!}</div>
                        <div class="kpi-body"><div class="kpi-label">Draft PR</div><div class="kpi-value">{{ $draftPrs }}</div><div class="kpi-hint">Not yet sent for review</div></div>
                    </div>
                </a>
                <a class="kpi" href="{{ route('purchase-requisitions.index') }}?status=reviewed,checked">
                    <div class="kpi-card" data-tone="pending">
                        <div class="kpi-icon">{!! $icon('clock') !!}</div>
                        <div class="kpi-body"><div class="kpi-label">Pending review</div><div class="kpi-value">{{ $pendingPrs }}</div><div class="kpi-hint">In review, budget check or approval</div></div>
                    </div>
                </a>
                <a class="kpi" href="{{ $canSeeModules ? route('process-steps.show', 'pr-receive') : route('purchase-requisitions.index') . '?status=approved' }}">
                    <div class="kpi-card" data-tone="approved">
                        <div class="kpi-icon">{!! $icon('check') !!}</div>
                        <div class="kpi-body"><div class="kpi-label">Approved PR</div><div class="kpi-value">{{ $approvedPrs }}</div><div class="kpi-hint">Ready for procurement</div></div>
                    </div>
                </a>

                {{-- Annual Plan belongs to the Accountant (Budget Checker); Admin keeps access too. --}}
                @if ($canSeeAnnualPlan)
                    <a class="kpi" href="{{ route('annual-plans.index') }}">
                        <div class="kpi-card" data-tone="violet">
                            <div class="kpi-icon">{!! $icon('calendar') !!}</div>
                            <div class="kpi-body"><div class="kpi-label">Annual Plan</div><div class="kpi-value">{{ $annualPlansCount }}</div><div class="kpi-hint">Plans on file</div></div>
                        </div>
                    </a>
                @endif

                @if ($hasCommittee)
                    <a class="kpi" href="{{ route('committee-work.index') }}">
                        <div class="kpi-card" data-tone="brand">
                            <div class="kpi-icon">{!! $icon('users') !!}</div>
                            <div class="kpi-body"><div class="kpi-label">My committee work</div><div class="kpi-value">{{ $myCommitteeWorkCount }}</div><div class="kpi-hint">Cases currently with your committee</div></div>
                        </div>
                    </a>
                @endif

                @if ($canSeeModules)
                    <a class="kpi" href="{{ route('modules.show', 'procurement-plans') }}?history=1&from=dashboard">
                        <div class="kpi-card" data-tone="brand">
                            <div class="kpi-icon">{!! $icon('layers') !!}</div>
                            <div class="kpi-body"><div class="kpi-label">Procurement plans</div><div class="kpi-value">{{ $activePlans }}</div><div class="kpi-hint">Planned or ongoing</div></div>
                        </div>
                    </a>
                    <a class="kpi" href="{{ route('modules.show', 'contract-awards') }}?history=1&from=dashboard">
                        <div class="kpi-card" data-tone="brand">
                            <div class="kpi-icon">{!! $icon('award') !!}</div>
                            <div class="kpi-body"><div class="kpi-label">Contracts awarded</div><div class="kpi-value">{{ $contractsAwarded }}</div><div class="kpi-hint">Awarded to date</div></div>
                        </div>
                    </a>
                @endif
            </div>
        </div>

        {{-- ===== PR pipeline ===== --}}
        <div class="dash-section">
            <div class="panel">
                <div class="panel-head">
                    <h2>Purchase requisition pipeline</h2>
                    <a class="more" href="{{ route('purchase-requisitions.index') }}">View all PRs</a>
                </div>
                <div class="pipeline">
                    @foreach ($stageLabels as $key => $label)
                        @php $n = (int) ($stageCounts[$key] ?? 0); @endphp
                        <a class="stage" href="{{ route('purchase-requisitions.index') }}?status={{ $key }}">
                            <div class="stage-box {{ $n > 0 ? 'has' : '' }} {{ $loop->last ? 'last' : '' }}">
                                <div class="n">{{ $n }}</div>
                                <div class="l">{{ $label }}</div>
                                <div class="stage-bar"><i style="width: {{ round($n / $stageTotal * 100) }}%"></i></div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ===== Your tasks + quick actions ===== --}}
        <div class="dash-section dash-cols {{ $hasTaskRole ? '' : 'single' }}">
            @if ($hasTaskRole)
            <div class="panel">
                <div class="panel-head">
                    <h2>Needs your action</h2>
                    <span class="count">{{ $notifCount }}</span>
                </div>
                @if ($notifCount === 0)
                    <div class="empty">{!! $icon('check') !!}You're all caught up — nothing is waiting on you.</div>
                @else
                    <div class="task-list">
                        @foreach ($notifItems->take(8) as $item)
                            @php
                                $isTransfer = ($item['label'] ?? '') === 'Transferred';
                                $url = $item['url'] ?? (route('purchase-requisitions.show', $item['pr']->id) . '#budget-check');
                                $pr = $item['pr'];
                                $metaParts = [];
                                if (! empty($pr->project_name)) { $metaParts[] = $pr->project_name; }
                                if (isset($pr->total_estimated_amount)) { $metaParts[] = $money($pr->total_estimated_amount); }
                                if (! empty($pr->created_at)) { $metaParts[] = 'waiting ' . \Illuminate\Support\Carbon::parse($pr->created_at)->diffForHumans(null, true); }
                                if (! empty($item['detail'])) { $metaParts[] = $item['detail']; }
                            @endphp
                            <a class="task" href="{{ $url }}">
                                <span class="task-pill {{ $isTransfer ? 'transfer' : '' }}">{{ $item['label'] }}</span>
                                <span class="task-main">
                                    <span class="task-title" style="display:block">{{ $pr->pr_number }}</span>
                                    @if ($metaParts)
                                        <span class="task-meta" style="display:block">{{ implode(' · ', $metaParts) }}</span>
                                    @endif
                                </span>
                                <span class="task-go">Open &rarr;</span>
                            </a>
                        @endforeach
                    </div>
                    @if ($notifCount > 8)
                        <div style="margin-top:.7rem;font-size:.8rem;color:var(--muted)">+ {{ $notifCount - 8 }} more — see the bell above.</div>
                    @endif
                @endif
            </div>
            @endif

            <div class="panel">
                <div class="panel-head"><h2>Quick actions</h2></div>
                <div class="quick">
                    @if ($canCreatePr)
                        <a class="primary" href="{{ route('purchase-requisitions.create') }}">{!! $icon('plus') !!}Create new PR</a>
                    @endif
                    <a href="{{ route('purchase-requisitions.index') }}">{!! $icon('list') !!}All purchase requisitions</a>
                    @if ($hasCommittee)
                        <a href="{{ route('committee-work.index') }}">{!! $icon('users') !!}My committee work @if ($myCommitteeWorkCount > 0)<span class="badge">{{ $myCommitteeWorkCount }}</span>@endif</a>
                    @endif
                    @if ($canSeeBudget)
                        <a href="{{ route('budget-dashboard') }}">{!! $icon('wallet') !!}Budget dashboard</a>
                    @endif
                    @if ($canSeeAnnualPlan)
                        <a href="{{ route('annual-plans.index') }}">{!! $icon('calendar') !!}Annual plan</a>
                    @endif
                    @if ($canSeeModules)
                        <a href="{{ route('cases.index') }}">{!! $icon('layers') !!}Procurement cases</a>
                        <a href="{{ route('meetings.notice.create.standalone') }}">{!! $icon('calendar') !!}Meeting notice</a>
                        <a href="{{ route('settings.committee.index') }}">{!! $icon('users') !!}Committee roster</a>
                        <a href="{{ route('modules.index') }}">{!! $icon('grid') !!}All modules</a>
                    @endif
                    @if ($isAdmin)
                        <a href="{{ route('admin.users.index') }}">{!! $icon('users') !!}User management</a>
                        <a href="{{ route('admin.database.index') }}">{!! $icon('grid') !!}Data manager</a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Recent PRs ===== --}}
        <div class="dash-section">
            <div class="panel">
                <div class="panel-head">
                    <h2>Recent purchase requisitions</h2>
                    <a class="more" href="{{ route('purchase-requisitions.index') }}">View all</a>
                </div>
                @if ($recentPrs->isEmpty())
                    <div class="empty">{!! $icon('file') !!}No purchase requisitions yet.</div>
                @else
                    <table class="recent">
                        <thead>
                            <tr><th>PR No.</th><th>Project</th><th>Requestor</th><th class="num">Amount</th><th>Status</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($recentPrs as $r)
                                <tr>
                                    <td data-label="PR No."><a class="prno" href="{{ route('purchase-requisitions.show', $r->id) }}">{{ $r->pr_number }}</a></td>
                                    <td data-label="Project">{{ $r->project_name ?: '—' }}</td>
                                    <td data-label="Requestor">{{ $r->requestor_name ?: '—' }}</td>
                                    <td class="num" data-label="Amount">{{ $money($r->total_estimated_amount) }}</td>
                                    <td data-label="Status"><span class="st st-{{ $r->status }}">{{ $stageLabels[$r->status] ?? ucfirst(str_replace('_', ' ', (string) $r->status)) }}</span></td>
                                    <td data-label="Date">{{ $r->requisition_date ? \Illuminate\Support\Carbon::parse($r->requisition_date)->format('d M Y') : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function toggleNotifPanel(e) {
        e.stopPropagation();
        document.getElementById('notifPanel').classList.toggle('open');
    }
    document.addEventListener('click', function (e) {
        var wrap = document.querySelector('.notif-wrap');
        var panel = document.getElementById('notifPanel');
        if (wrap && panel && !wrap.contains(e.target)) {
            panel.classList.remove('open');
        }
    });
</script>
@endsection
