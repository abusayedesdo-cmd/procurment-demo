

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

        .action-menu { position: relative; display: inline-block; }
    .action-menu .action-btn {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 7px;
        padding: .4rem .75rem;
        font-size: .8rem;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer;
        font-family: inherit;
    }
    .action-menu .action-btn:hover { border-color: #CBD5E1; background: var(--surface); }
    .action-menu .action-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        min-width: 240px;
        z-index: 20;
        overflow: hidden;
        text-align: left;
    }
    .action-menu.open .action-dropdown { display: block; }
    .action-dropdown a {
        display: block;
        padding: .6rem .85rem;
        font-size: .82rem;
        color: var(--ink);
        text-decoration: none;
        border-bottom: 1px solid var(--line);
    }
    .action-dropdown a:last-child { border-bottom: none; }
    .action-dropdown a:hover { background: var(--surface); color: var(--accent-dark); }
    .no-cases { text-align: center; padding: 2rem 1rem; color: var(--muted); font-size: .88rem; }
    .no-cases a { color: var(--accent-dark); font-weight: 600; }

    .missing-plan-notice {
        display: flex; flex-direction: column; gap: .6rem;
        background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;
        border-radius: 10px; padding: 1rem 1.2rem; margin-bottom: 1.25rem; font-size: .88rem;
    }
    .missing-plan-notice a {
        align-self: flex-start; background: #1D4ED8; color: #fff; text-decoration: none;
        font-weight: 600; padding: .5rem .95rem; border-radius: 7px; font-size: .84rem;
    }

    .active-pr-banner {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        background: var(--surface); border: 1px solid var(--line); border-radius: 10px;
        padding: .8rem 1.1rem; margin-bottom: 1.25rem; font-size: .86rem; color: var(--ink);
    }
    .active-pr-banner a { color: var(--muted); font-weight: 600; font-size: .8rem; text-decoration: none; }
    .active-pr-banner a:hover { color: var(--accent-dark); }

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
                <!-- <p class="eyebrow">Step <?php echo e($step['step_no']); ?></p> -->
                <h1><?php echo e($step['subject']); ?></h1>
            </div>
            <div style="display:flex; align-items:center; gap:.6rem; flex-shrink:0;">
                <a href="<?php echo e(($activePr && $slug !== 'pr-receive') ? route('process-steps.show', 'pr-receive') : route('dashboard')); ?>"
                   class="back-link">&larr; Back</a>
            </div>
        </div>

        <?php if(isset($activePr)): ?>
            <div class="active-pr-banner">
                <span>Working on <b>PR-<?php echo e($activePr->pr_number ?? $activePr->id); ?></b> </span>
                <a href="<?php echo e(route('process-steps.show', $slug)); ?>?clear_pr=1">Change / clear &times;</a>
            </div>
        <?php endif; ?>

        <?php if(isset($missingPlanForPr)): ?>
            <div class="missing-plan-notice">
                <div>
                    <b>PR-<?php echo e($missingPlanForPr->pr_number ?? $missingPlanForPr->id); ?></b>-এর জন্য এখনো কোনো Procurement Plan তৈরি হয়নি — Sub-Committee-তে ট্রান্সফার করার আগে প্রথমে একটা Procurement Plan লাগবে।
                </div>
                <a href="<?php echo e(route('modules.show', 'procurement-plans')); ?>?new=1&field_pr_id=<?php echo e($missingPlanForPr->id); ?>&context_label=PR-<?php echo e($missingPlanForPr->pr_number ?? $missingPlanForPr->id); ?>">
                    Create Procurement Plan for PR-<?php echo e($missingPlanForPr->pr_number ?? $missingPlanForPr->id); ?> &rarr;
                </a>
                <div style="font-size:.8rem; opacity:.85;">Plan তৈরি হয়ে গেলে Purchase Requisitions লিস্টে ফিরে গিয়ে আবার "Transfer to Sub-Committee (3rd Step)" চাপুন — তখন এটা এখানে auto-select হয়ে আসবে।</div>
            </div>
        <?php endif; ?>

        <?php if(!empty($step['coming_soon'])): ?>
            <div class="group-panel">
                <div class="coming-soon">This step's module is coming soon.</div>
            </div>
        <?php elseif(!empty($step['is_pr_picker'])): ?>
            <!-- <p class="case-hint">একটা Approved PR বেছে নিন — এরপর Process Steps-এর প্রতিটা ধাপ শুধু এই PR নিয়েই কাজ করবে।</p> -->
            <?php if($prReceiveList->isEmpty()): ?>
                <div class="group-panel">
                    <div class="no-cases">No approved PR is waiting to be picked up right now.</div>
                </div>
            <?php else: ?>
                <div style="margin-bottom:1rem;">
                    <input type="text" id="prSearchInput" placeholder="Search by PR no. or Project name…"
                           style="width:100%; max-width:420px; padding:.55rem .8rem; border:1px solid var(--line); border-radius:8px; font-size:.85rem; font-family:inherit;">
                </div>
                <div class="group-panel">
                    <div class="module-list">
                        <?php $__currentLoopData = $prReceiveList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $prCase = $pr->procurementCase;
                                $prTransfer = $pr->procurementPlan?->subCommitteeTransfers->first();
                                $progressLabel = match(true) {
                                    (bool) $prCase => 'Step ' . min($prCase->current_step + 1, 23) . '/23 — ' . $prCase->currentStepName(),
                                    (bool) $pr->procurementPlan => $prTransfer
                                        ? ('3rd — With ' . ($prTransfer->toCommittee->name ?? '—'))
                                        : '3rd — Sub-Committee (pending transfer)',
                                    default => '2nd — PR Receive',
                                };

                                // Single "what's next" action per PR, based on
                                // the same state used for the progress label.
                                $nextAction = match(true) {
                                    (bool) $prCase => ['label' => 'Continue Case (' . $prCase->ref . ')', 'url' => route('cases.show', $prCase)],
                                    (bool) $prTransfer => ['label' => '1st Meeting Notice ', 'url' => route('process-steps.show', 'meeting-notice') . '?pr_id=' . $pr->id],
                                    default => ['label' => 'Transfer to Sub-Committee ', 'url' => route('process-steps.show', 'sub-committee') . '?pr_id=' . $pr->id],
                                };

                                // Steps already shown as their own button above
                                // (the primary next-action, and/or "Return to
                                // Main Committee") — leave these out of the
                                // "Other Steps" dropdown so nothing's repeated.
                                $excludedStepSlugs = ['pr-receive'];
                                if (! $prCase) {
                                    $excludedStepSlugs[] = $prTransfer ? 'meeting-notice' : 'sub-committee';
                                }
                                if ($prTransfer && $prTransfer->toCommittee?->type === 'sub') {
                                    $excludedStepSlugs[] = 'sub-committee';
                                }
                            ?>
                            <div class="module-row" data-search="<?php echo e(strtolower(($pr->pr_number ?? '').' '.($pr->project_name ?? ''))); ?>" style="cursor:default;">
                                <span>
                                    <?php echo e($pr->pr_number ?? ('PR-' . $pr->id)); ?> — ৳ <?php echo e(number_format($pr->total_estimated_amount ?? 0, 2)); ?>

                                    <?php if($pr->project_name): ?>
                                        <br><span style="color:var(--muted); font-size:.78rem;"><?php echo e($pr->project_name); ?></span>
                                    <?php endif; ?>
                                </span>
                                <div style="display:flex; align-items:center; gap:.5rem; flex-shrink:0;">
                                    <a href="<?php echo e($nextAction['url']); ?>" class="btn primary" style="padding:.3rem .75rem; font-size:.78rem;"><?php echo e($nextAction['label']); ?></a>

                                    <div class="action-menu">
                                        <button type="button" class="action-btn">Other Steps ▾</button>
                                        <div class="action-dropdown">
                                            <?php $__currentLoopData = \App\Http\Controllers\ProcessStepPageController::STEPS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jumpSlug => $jumpStep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(in_array($jumpSlug, $excludedStepSlugs, true)) continue; ?>
                                                <a href="<?php echo e(route('process-steps.show', $jumpSlug)); ?>?pr_id=<?php echo e($pr->id); ?>"><?php echo e($jumpStep['step_no']); ?> — <?php echo e($jumpStep['subject']); ?></a>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>

                                    <?php if($prTransfer && $prTransfer->toCommittee?->type === 'sub'): ?>
                                        <a href="<?php echo e(route('process-steps.show', 'sub-committee')); ?>?pr_id=<?php echo e($pr->id); ?>" class="btn" style="padding:.3rem .75rem; font-size:.78rem;">Return to Main Committee</a>
                                    <?php endif; ?>
                                     <a href="<?php echo e(route('purchase-requisitions.show', $pr->id)); ?>" class="btn" style="padding:.3rem .75rem; font-size:.78rem;">View</a>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php elseif(isset($cases)): ?>
            <p class="case-hint">Pick a case below to record this step directly on it.</p>
            <?php if($cases->isEmpty()): ?>
                <div class="group-panel">
                   <div class="no-cases">No procurement cases yet. <a href="<?php echo e(route('cases.create', $activePr ? ['pr_id' => $activePr->id] : [])); ?>">Open a new case</a> to get started.</div>
                </div>
            <?php else: ?>
                <div class="case-grid">
                    <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            // Meeting-step pages jump straight into that step's own
                            // form — the case is only listed here because it's
                            // actually ready for this specific action.
                            $firstMeeting = $case->relationLoaded('meetings') ? $case->meetings->first() : null;
                            $cardUrl = match ($slug) {
                                'meeting-notice' => route('meetings.notice.create', [$case, 'first']),
                                'meeting-attendance' => $firstMeeting ? route('meetings.attendance.create', $firstMeeting) : route('cases.show', $case),
                                'meeting-resolution' => $firstMeeting ? route('meetings.resolution.create', $firstMeeting) : route('cases.show', $case),
                                default => route('cases.show', $case),
                            };
                        ?>
                        <a href="<?php echo e($cardUrl); ?>" class="case-card">
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
                        <?php
                            $moduleUrl = isset($m['route']) ? route($m['route']) : route('modules.show', $m['slug']);
                            $prefillField = ($m['no_context'] ?? false) ? null : ($prefillFieldBySlug[$m['slug'] ?? null] ?? null);
                            $prefillValue = match ($prefillField) {
                                'pr_id' => $activePr?->id,
                                'procurement_plan_id' => $planId,
                                'procurement_case_id' => $caseId,
                                'rfq_id' => $rfqId,
                                default => null,
                            };
                        if ($m['no_context'] ?? false) {
                            $moduleUrl .= '?history=1';
                        } elseif ($prefillField && $prefillValue) {
                            $moduleUrl .= '?new=1&field_' . $prefillField . '=' . $prefillValue;
                            if ($activePr) {
                                $moduleUrl .= '&context_label=' . urlencode($activePr->pr_number ?? ('PR-' . $activePr->id));
                            }
                            // Sub-Committee Transfer's From/To committee auto-fill.
                            if (($m['slug'] ?? null) === 'sub-committee-transfers') {
                                if ($fromCommitteeId) {
                                    $moduleUrl .= '&field_from_committee_id=' . $fromCommitteeId;
                                }
                                if ($toCommitteeId) {
                                    $moduleUrl .= '&field_to_committee_id=' . $toCommitteeId;
                                }
                            }
                        }
                        ?>
                        <a class="module-row" href="<?php echo e($moduleUrl); ?>">
                            <span><?php echo e($m['title']); ?></span>
                            <span class="chevron">&rarr;</span>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const prSearchInput = document.getElementById('prSearchInput');
    if (prSearchInput) {
        prSearchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.module-list .module-row[data-search]').forEach(function (row) {
                row.style.display = (!q || row.dataset.search.includes(q)) ? '' : 'none';
            });
        });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.action-btn');
        const menu = e.target.closest('.action-menu');
        if (btn && menu && btn === menu.querySelector('.action-btn')) {
            const wasOpen = menu.classList.contains('open');
            document.querySelectorAll('.action-menu.open').forEach(m => m.classList.remove('open'));
            if (!wasOpen) menu.classList.add('open');
            return;
        }
        if (!e.target.closest('.action-menu')) {
            document.querySelectorAll('.action-menu.open').forEach(m => m.classList.remove('open'));
        }
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/process-steps/show.blade.php ENDPATH**/ ?>