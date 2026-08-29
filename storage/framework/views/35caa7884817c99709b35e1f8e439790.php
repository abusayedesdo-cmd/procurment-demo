

<?php $__env->startSection('title', $step['subject']); ?>

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
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .shell { max-width: 1080px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

    .page-header { padding-bottom: 1.75rem; margin-bottom: 2rem; border-bottom: 1px solid var(--line); display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }

    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 0 0 .5rem;
    }

    .page-header h1 { margin: 0; font-size: 1.5rem; font-weight: 700; letter-spacing: -0.01em; color: var(--ink); }

    .back-link {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: .5rem .9rem;
        font-size: .82rem;
        font-weight: 600;
        color: var(--ink);
        text-decoration: none;
    }
    .back-link:hover { border-color: var(--accent); color: var(--accent-dark); }

    .group-panel { background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 1.35rem 1.5rem; }

    .module-list { display: flex; flex-direction: column; }

    .module-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem .2rem;
        border-bottom: 1px solid var(--line);
        text-decoration: none;
        color: var(--ink);
        font-size: .9rem;
        font-weight: 600;
        transition: background .15s ease, padding-left .15s ease, color .15s ease;
    }
    .module-list .module-row:last-child { border-bottom: none; }
    .module-row:hover { background: var(--surface); padding-left: .6rem; color: var(--accent-dark); }
    .module-row .chevron { color: var(--muted); font-weight: 400; flex-shrink: 0; transition: transform .15s ease, color .15s ease; }
    .module-row:hover .chevron { color: var(--accent); transform: translateX(3px); }

    .coming-soon { text-align: center; padding: 3rem 1rem; color: var(--muted); font-size: .9rem; }

    .case-hint { font-size: .82rem; color: var(--muted); margin-bottom: 1rem; }
    .case-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: .9rem; }
    .case-card {
        display: flex; flex-direction: column; gap: .55rem;
        background: var(--paper); border: 1px solid var(--line); border-radius: 10px;
        padding: 1rem 1.1rem; text-decoration: none; color: inherit;
        transition: border-color .15s ease, transform .15s ease;
    }
    .case-card:hover { border-color: var(--accent); transform: translateY(-1px); }
    .case-card .top-row { display: flex; align-items: center; gap: .4rem; font-size: .7rem; font-weight: 700; color: var(--muted); }
    .case-card .ref { margin-left: auto; }
    .case-card .title { font-size: .92rem; font-weight: 700; color: var(--ink); }
    .case-card .amount { font-size: .78rem; color: var(--muted); }
    .case-card .progress-track { height: 5px; background: var(--surface); border-radius: 999px; overflow: hidden; }
    .case-card .progress-fill { height: 100%; background: var(--accent); }
    .case-card .step-label { font-size: .72rem; font-weight: 700; color: var(--accent-dark); }
    .case-card .now { font-size: .78rem; font-weight: 600; color: var(--ink); }
    .no-cases { text-align: center; padding: 2rem 1rem; color: var(--muted); font-size: .88rem; }
    .no-cases a { color: var(--accent-dark); font-weight: 600; }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
        .page-header { flex-direction: column; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Step <?php echo e($step['step_no']); ?></p>
                <h1><?php echo e($step['subject']); ?></h1>
            </div>
            <div style="display:flex; align-items:center; gap:.6rem; flex-shrink:0;">
                <?php if(isset($cases)): ?>
                    <a href="<?php echo e(route('cases.create')); ?>" class="back-link" style="background: var(--accent); border-color: var(--accent); color: #fff;">+ New Case</a>
                <?php endif; ?>
                <a href="<?php echo e(route('dashboard')); ?>" class="back-link">&larr; Dashboard</a>
            </div>
        </div>

        <?php if(!empty($step['coming_soon'])): ?>
            <div class="group-panel">
                <div class="coming-soon">This step's module is coming soon.</div>
            </div>
        <?php elseif(isset($cases)): ?>
            <p class="case-hint">Pick a case below to record this step directly on it.</p>
            <?php if($cases->isEmpty()): ?>
                <div class="group-panel">
                    <div class="no-cases">No procurement cases yet. <a href="<?php echo e(route('cases.create')); ?>">Open a new case</a> to get started.</div>
                </div>
            <?php else: ?>
                <div class="case-grid">
                    <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('cases.show', $case)); ?>?focus=meetings&step=<?php echo e($slug); ?>" class="case-card">
                            <div class="top-row">
                                <span><?php echo e($case->method); ?></span>
                                <span>&middot;</span>
                                <span><?php echo e($case->category); ?></span>
                                <span class="ref"><?php echo e($case->ref); ?></span>
                            </div>
                            <div class="title"><?php echo e($case->title); ?></div>
                            <div class="amount">৳ <?php echo e(number_format($case->amount, 2)); ?></div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="group-panel">
                <div class="module-list">
                    <?php $__currentLoopData = $step['modules']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="module-row" href="<?php echo e(isset($m['route']) ? route($m['route']) : route('modules.show', $m['slug'])); ?>">
                            <span><?php echo e($m['title']); ?></span>
                            <span class="chevron">&rarr;</span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/process-steps/show.blade.php ENDPATH**/ ?>