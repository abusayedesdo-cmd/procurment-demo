<?php $__env->startSection('title', 'ESDO Procurement — Dashboard'); ?>

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
        --slate-bg: #F1F5F9;
    }

    .shell {
        max-width: 1080px;
        margin: 0 auto;
        padding: 2.5rem 2rem 4rem;
    }

    /* ---- Header ---- */
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1.75rem;
        margin-bottom: 2.25rem;
        border-bottom: 1px solid var(--line);
    }

    .brand { display: flex; align-items: center; gap: .75rem; }

    .brand-mark {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: var(--ink);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
        font-size: .85rem;
        letter-spacing: -0.02em;
    }

    .brand-text h1 {
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .brand-text span {
        font-size: .78rem;
        color: var(--muted);
        font-weight: 500;
    }

    .user-block {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .user-meta { text-align: right; }

    .user-meta .name { font-size: .88rem; font-weight: 600; }

    .user-meta .role {
        font-size: .72rem;
        color: var(--accent-dark);
        background: var(--green-bg);
        border: 1px solid #BBF7D0;
        padding: .1rem .5rem;
        border-radius: 999px;
        display: inline-block;
        margin-top: .2rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .user-meta .designation {
        display: block;
        font-size: .74rem;
        color: var(--muted);
        margin-top: .2rem;
    }

    form.logout-form { margin: 0; }

    button.logout {
        background: none;
        border: 1px solid var(--line);
        color: var(--muted);
        cursor: pointer;
        font-size: .8rem;
        font-weight: 500;
        padding: .45rem .8rem;
        border-radius: 6px;
        transition: border-color .15s ease, color .15s ease;
    }

    button.logout:hover { border-color: #CBD5E1; color: var(--ink); }

    /* ---- Section eyebrow ---- */
    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 0 0 1rem;
    }

    /* ---- Stat grid ---- */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2.5rem;
    }

    .card {
        background: var(--surface);
        border-radius: 10px;
        padding: 1.25rem 1.35rem;
        border-left: 3px solid var(--line);
        transition: transform .12s ease, box-shadow .12s ease;
    }

    a.card-link { text-decoration: none; color: inherit; display: block; }
    a.card-link:hover .card { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(15,23,42,.08); }

    .card[data-tone="neutral"] { border-left-color: #94A3B8; }
    .card[data-tone="pending"] { border-left-color: var(--amber); }
    .card[data-tone="approved"] { border-left-color: var(--green); }
    .card[data-tone="brand"] { border-left-color: var(--accent); }
    .card[data-tone="violet"] { border-left-color: #7C3AED; }
    .card[data-tone="indigo"] { border-left-color: #4F46E5; }
    .card[data-tone="orange"] { border-left-color: #F59E0B; }

    .card h3 {
        margin: 0 0 .6rem;
        font-size: .74rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 600;
    }

    .card p {
        margin: 0;
        font-family: 'JetBrains Mono', monospace;
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: var(--ink);
    }

    /* ---- Actions ---- */
    .actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: var(--ink);
        color: #fff;
        border: 1px solid var(--ink);
        border-radius: 7px;
        padding: .65rem 1.15rem;
        cursor: pointer;
        text-decoration: none;
        font-size: .87rem;
        font-weight: 600;
        transition: background .15s ease;
    }

    .btn:hover { background: var(--accent-dark); border-color: var(--accent-dark); }

    .btn.primary { background: var(--accent); border-color: var(--accent); }
    .btn.primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); }

    .btn.secondary {
        background: transparent;
        color: var(--ink);
        border: 1px solid var(--line);
    }
    .btn.secondary:hover { background: var(--surface); border-color: #CBD5E1; }

    /* ---- Workflow note ---- */
    .workflow-note {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        display: flex;
        gap: .9rem;
        align-items: flex-start;
        background: var(--surface);
    }

    .workflow-note .tag {
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--accent-dark);
        background: var(--green-bg);
        border: 1px solid #BBF7D0;
        padding: .2rem .5rem;
        border-radius: 5px;
        white-space: nowrap;
        margin-top: .1rem;
    }

    .workflow-note p {
        margin: 0;
        font-size: .88rem;
        color: var(--muted);
        line-height: 1.5;
    }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
        .header { flex-direction: column; align-items: flex-start; gap: 1rem; }
        .user-block { width: 100%; justify-content: space-between; }
    }

    /* ---- Notifications (pending action items as a bell dropdown) ---- */
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <?php $canSeeModules = in_array($user->roleName() ?? null, [\App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN]); ?>

    <div class="shell">
        <div class="header">
            <div class="brand">
                <div class="brand-mark">EP</div>
                <div class="brand-text">
                    <h1>ESDO Procurement</h1>
                    <span>Management System</span>
                </div>
            </div>
            <?php
                $canReview = in_array($user->roleName() ?? null, [\App\Models\User::REVIEWER, \App\Models\User::ADMIN]);
                $canCheckBudget = in_array($user->roleName() ?? null, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN]);
                $canApprove = in_array($user->roleName() ?? null, [\App\Models\User::APPROVER, \App\Models\User::ADMIN]);
                $canFocalReview = in_array($user->roleName() ?? null, [\App\Models\User::FOCAL_PERSON, \App\Models\User::ADMIN]);
                $canEdApprove = in_array($user->roleName() ?? null, [\App\Models\User::EXECUTIVE_DIRECTOR, \App\Models\User::ADMIN]);

                $notifItems = collect();
                if ($canReview) {
                    foreach ($awaitingReview as $pr) { $notifItems->push(['label' => 'Review', 'pr' => $pr]); }
                }
                if ($canCheckBudget) {
                    foreach ($awaitingBudgetCheck as $pr) { $notifItems->push(['label' => 'Check Budget', 'pr' => $pr]); }
                }
                if ($canFocalReview) {
                    foreach ($awaitingFocalReview as $pr) { $notifItems->push(['label' => 'Focal Review', 'pr' => $pr]); }
                }
                if ($canEdApprove) {
                    foreach ($awaitingEdApproval as $pr) { $notifItems->push(['label' => 'ED Approval', 'pr' => $pr]); }
                }
                if ($canApprove) {
                    foreach ($awaitingApproval as $pr) { $notifItems->push(['label' => 'Approve', 'pr' => $pr]); }
                }
                // PRs transferred to one of this user's committees (Main/Central -> Sub-Committee etc.).
                foreach (($transferredToMe ?? collect()) as $t) {
                    $notifItems->push([
                        'label' => 'Transferred',
                        'pr' => (object) ['pr_number' => $t->pr_number],
                        'url' => $t->case_id ? route('cases.show', $t->case_id) : route('committee-work.index'),
                        'detail' => trim(($t->from ? 'from ' . $t->from . ' ' : '') . '→ ' . $t->to . ($t->date ? ' · ' . $t->date->format('d M Y') : '')),
                    ]);
                }
                $notifCount = $notifItems->count();
            ?>
            <div class="user-block">
                <div class="notif-wrap">
                    <button type="button" class="notif-bell" onclick="toggleNotifPanel(event)" aria-label="Pending actions">
                        🔔
                        <?php if($notifCount > 0): ?>
                            <span class="notif-badge"><?php echo e($notifCount > 99 ? '99+' : $notifCount); ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="notif-panel" id="notifPanel">
                        <div class="notif-panel-head">
                            <span class="title">Pending Actions</span>
                            <span class="count"><?php echo e($notifCount); ?></span>
                        </div>
                        <div class="notif-list">
                            <?php $__empty_1 = true; $__currentLoopData = $notifItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <a href="<?php echo e($item['url'] ?? (route('purchase-requisitions.show', $item['pr']->id) . '#budget-check')); ?>" class="notif-item" <?php if(!empty($item['detail'])): ?> title="<?php echo e($item['detail']); ?>" <?php endif; ?>>
                                    <span class="dot"></span>
                                    <span class="label"><?php echo e($item['label']); ?> —</span>
                                    <span class="prno"><?php echo e($item['pr']->pr_number); ?></span>
                                    <?php if(!empty($item['detail'])): ?>
                                        <span class="label" style="font-size:.72rem;margin-left:auto;"><?php echo e($item['detail']); ?></span>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="notif-empty">No pending actions</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="user-meta">
                    <div class="name"><?php echo e($user->name ?? ''); ?></div>
                    <span class="role"><?php echo e($user->roleLabel() ?? ''); ?></span>
                    <?php if(!empty($user->designation)): ?>
                        <span class="designation"><?php echo e($user->designation); ?></span>
                    <?php endif; ?>
                </div>
                <!-- <form class="logout-form" method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout">Sign out</button>
                </form> -->
            </div>
        </div>

        <p class="eyebrow">Operations Overview</p>
        <div class="card-grid">
            <a class="card-link" href="<?php echo e(route('purchase-requisitions.index')); ?>?status=draft">
                <div class="card" data-tone="neutral">
                    <h3>Draft PR</h3>
                    <p><?php echo e($draftPrs); ?></p>
                </div>
            </a>
            <a class="card-link" href="<?php echo e(route('purchase-requisitions.index')); ?>?status=reviewed,checked">
                <div class="card" data-tone="pending">
                    <h3>Pending Review </h3>
                    <p><?php echo e($pendingPrs); ?></p>
                </div>
            </a>
            <!-- <a class="card-link" href="<?php echo e(route('process-steps.show', 'pr-receive')); ?>">
                <div class="card" data-tone="approved">
                    <h3>Approved PR</h3>
                    <p><?php echo e($approvedPrs); ?></p>
                </div>
            </a> -->
            <a class="card-link" href="<?php echo e($canSeeModules ? route('process-steps.show', 'pr-receive') : route('purchase-requisitions.index') . '?status=approved'); ?>">
                <div class="card" data-tone="approved">
                    <h3>Approved PR</h3>
                    <p><?php echo e($approvedPrs); ?></p>
                </div>
            </a>

            
            <?php if(in_array($user->roleName() ?? null, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN])): ?>
                <a class="card-link" href="<?php echo e(route('annual-plans.index')); ?>">
                    <div class="card" data-tone="violet">
                        <h3>Annual Plan</h3>
                        <p><?php echo e($annualPlansCount); ?></p>
                    </div>
                </a>
            <?php endif; ?>

        
 

                <!-- <a class="card-link" href="<?php echo e(route('budget-dashboard')); ?>">
                    <div class="card" data-tone="orange">
                        <h3>Budget Dashboard</h3>
                        <p>-</p>
                    </div>
                </a> -->

            <?php
                $hideCommitteeCards = in_array($user->roleName() ?? null, [\App\Models\User::REQUESTER, \App\Models\User::REVIEWER, \App\Models\User::BUDGET_CHECKER, \App\Models\User::FOCAL_PERSON]);
            ?>
            <?php if($canSeeModules): ?>
                <a class="card-link" href="<?php echo e(route('modules.show', 'procurement-plans')); ?>?history=1&from=dashboard">
                    <div class="card" data-tone="brand">
                        <h3>Procurement Plans</h3>
                        <p><?php echo e($activePlans); ?></p>
                    </div>
                </a>



                <a class="card-link" href="<?php echo e(route('modules.show', 'contract-awards')); ?>?history=1&from=dashboard">
                    <div class="card" data-tone="brand">
                        <h3>Contracts Awarded</h3>
                        <p><?php echo e($contractsAwarded); ?></p>
                    </div>
                </a>
                

            <?php elseif(! $hideCommitteeCards): ?>
                <div class="card" data-tone="brand">
                    <h3>Procurement Plans</h3>
                    <p><?php echo e($activePlans); ?></p>
                </div>
                
                <div class="card" data-tone="brand">
                    <h3>Contracts Awarded</h3>
                    <p><?php echo e($contractsAwarded); ?></p>
                </div>
            <?php endif; ?>

        </div>

        <!-- <div class="actions">
            <a href="<?php echo e(route('purchase-requisitions.index')); ?>" class="btn primary">View Purchase Requisitions</a>
            <?php if($user && $user->roleName() === \App\Models\User::REQUESTER): ?>
                <a href="<?php echo e(route('purchase-requisitions.create')); ?>" class="btn">+ New Purchase Requisition</a>
            <?php endif; ?>
            <?php if($canSeeModules): ?>
                <a href="<?php echo e(route('modules.index')); ?>" class="btn secondary">All Modules — Plan, RFQ, Meeting, Evaluation, Contract</a>
            <?php endif; ?>
        </div> -->

        <div class="workflow-note">
            <span class="tag">Workflow</span>
            <p>The full process from Procurement Plan through Contract Award, Work Order, and Delivery Receipt is now managed from the Procurement Officer's "All Modules" view.</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/dashboard.blade.php ENDPATH**/ ?>