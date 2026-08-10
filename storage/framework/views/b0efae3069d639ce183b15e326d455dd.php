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
            <div class="user-block">
                <div class="user-meta">
                    <div class="name"><?php echo e($user->name ?? ''); ?></div>
                    <span class="role"><?php echo e($user->roleLabel() ?? ''); ?></span>
                </div>
                <!-- <form class="logout-form" method="POST" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="logout">Sign out</button>
                </form> -->
            </div>
        </div>

        <?php
            $canReview = in_array($user->roleName() ?? null, [\App\Models\User::REVIEWER, \App\Models\User::ADMIN]);
            $canCheckBudget = in_array($user->roleName() ?? null, [\App\Models\User::BUDGET_CHECKER, \App\Models\User::ADMIN]);
            $canApprove = in_array($user->roleName() ?? null, [\App\Models\User::APPROVER, \App\Models\User::ADMIN]);
            $canFocalReview = in_array($user->roleName() ?? null, [\App\Models\User::FOCAL_PERSON, \App\Models\User::ADMIN]);
            $canEdApprove = in_array($user->roleName() ?? null, [\App\Models\User::EXECUTIVE_DIRECTOR, \App\Models\User::ADMIN]);
        ?>
        <?php if(($canReview && ($awaitingReview->count() ?? 0) > 0) || ($canCheckBudget && ($awaitingBudgetCheck->count() ?? 0) > 0) || ($canApprove && ($awaitingApproval->count() ?? 0) > 0) || ($canFocalReview && ($awaitingFocalReview->count() ?? 0) > 0) || ($canEdApprove && ($awaitingEdApproval->count() ?? 0) > 0)): ?>
            <div class="actions">
                <?php if($canReview): ?>
                    <?php $__currentLoopData = $awaitingReview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('purchase-requisitions.show', $pr->id)); ?>#budget-check" class="btn primary">
                            ✓ Review — <?php echo e($pr->pr_number); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
                <?php if($canCheckBudget): ?>
                    <?php $__currentLoopData = $awaitingBudgetCheck; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('purchase-requisitions.show', $pr->id)); ?>#budget-check" class="btn primary">
                            ✓ Check Budget — <?php echo e($pr->pr_number); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
                <?php if($canFocalReview): ?>
                    <?php $__currentLoopData = $awaitingFocalReview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('purchase-requisitions.show', $pr->id)); ?>#budget-check" class="btn primary">
                            ✓ Focal Review — <?php echo e($pr->pr_number); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
                <?php if($canEdApprove): ?>
                    <?php $__currentLoopData = $awaitingEdApproval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('purchase-requisitions.show', $pr->id)); ?>#budget-check" class="btn primary">
                            ✓ ED Approval — <?php echo e($pr->pr_number); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
                <?php if($canApprove): ?>
                    <?php $__currentLoopData = $awaitingApproval; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('purchase-requisitions.show', $pr->id)); ?>#budget-check" class="btn primary">
                            ✓ Approve — <?php echo e($pr->pr_number); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

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
                    <h3>Pending Review / Check</h3>
                    <p><?php echo e($pendingPrs); ?></p>
                </div>
            </a>
            <a class="card-link" href="<?php echo e(route('purchase-requisitions.index')); ?>?status=approved">
                <div class="card" data-tone="approved">
                    <h3>Approved PR</h3>
                    <p><?php echo e($approvedPrs); ?></p>
                </div>
            </a>

            <?php if($canSeeModules): ?>
                <a class="card-link" href="<?php echo e(route('modules.show', 'procurement-plans')); ?>">
                    <div class="card" data-tone="brand">
                        <h3>Active Procurement Plans</h3>
                        <p><?php echo e($activePlans); ?></p>
                    </div>
                </a>
                <a class="card-link" href="<?php echo e(route('modules.show', 'contract-awards')); ?>">
                    <div class="card" data-tone="brand">
                        <h3>Contracts Awarded</h3>
                        <p><?php echo e($contractsAwarded); ?></p>
                    </div>
                </a>
            <?php else: ?>
                <div class="card" data-tone="brand">
                    <h3>Active Procurement Plans</h3>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/dashboard.blade.php ENDPATH**/ ?>