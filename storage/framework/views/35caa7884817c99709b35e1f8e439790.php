

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

    .missing-subcommittee-panel {
    background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;
    border-radius: 10px; padding: 1.1rem 1.3rem; margin-bottom: 1.25rem; font-size: .88rem;
    }
    .missing-subcommittee-panel .ssc-head { margin-bottom: .8rem; }
    .missing-subcommittee-panel .ssc-error {
        background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA;
        border-radius: 7px; padding: .5rem .8rem; font-size: .82rem; margin-bottom: .7rem;
    }
    .missing-subcommittee-panel .ssc-row { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: .8rem; }
    .missing-subcommittee-panel .ssc-row > div { flex: 1; min-width: 200px; }
    .missing-subcommittee-panel label { display: block; font-size: .78rem; font-weight: 600; margin-bottom: .3rem; color: #1D4ED8; }
    .missing-subcommittee-panel input[type="text"] {
        width: 100%; border: 1px solid #BFDBFE; border-radius: 7px; padding: .5rem .7rem;
        font-size: .85rem; font-family: inherit; color: var(--ink); background: #fff;
    }
    .missing-subcommittee-panel .ssc-roster { margin-bottom: .9rem; }
    .missing-subcommittee-panel .ssc-checklist {
        max-height: 180px; overflow-y: auto; background: #fff; border: 1px solid #BFDBFE;
        border-radius: 8px; padding: .5rem .75rem; margin-top: .4rem;
    }
    .missing-subcommittee-panel .ssc-checklist .ssc-row-item { display: flex; align-items: center; gap: .5rem; padding: .3rem 0; font-size: .84rem; color: var(--ink); }
    .missing-subcommittee-panel .ssc-checklist .ssc-row-item .ssc-name { flex: 1; }
    .missing-subcommittee-panel .ssc-checklist input[type="text"] {
        width: 130px; border: 1px solid var(--line); border-radius: 6px; padding: .3rem .5rem; font-size: .78rem;
    }
    .missing-subcommittee-panel .ssc-login-toggle { font-size: .78rem; color: var(--ink); display:flex; align-items:center; gap:.25rem; white-space:nowrap; margin-left:.4rem; }
    .missing-subcommittee-panel .ssc-login-fields { margin: -.1rem 0 .5rem 1.6rem; }
    .missing-subcommittee-panel .ssc-login-fields input[type="email"] { width: 220px; border: 1px solid var(--line); border-radius: 6px; padding: .3rem .5rem; font-size: .78rem; }

    .missing-subcommittee-panel button.btn { background: #1D4ED8; border-color: #1D4ED8; }
    .missing-subcommittee-panel button.btn:hover { background: #1E40AF; border-color: #1E40AF; }

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
                onclick="if (window.history.length > 1) { history.back(); return false; }"
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
                    No Procurement Plan has been created yet for <b>PR-<?php echo e($missingPlanForPr->pr_number ?? $missingPlanForPr->id); ?></b> — a Procurement Plan is required before transferring it to a Sub-Committee.
                </div>
                <a href="<?php echo e(route('modules.show', 'procurement-plans')); ?>?new=1&field_pr_id=<?php echo e($missingPlanForPr->id); ?>&context_label=PR-<?php echo e($missingPlanForPr->pr_number ?? $missingPlanForPr->id); ?>">
                    Create Procurement Plan for PR-<?php echo e($missingPlanForPr->pr_number ?? $missingPlanForPr->id); ?> &rarr;
                </a>
                <div style="font-size:.8rem; opacity:.85;">Once the Plan is created, go back to the Purchase Requisitions list and click "Transfer to Sub-Committee (3rd Step)" again — it will then be auto-selected here.</div>
            </div>
        <?php endif; ?>

        <?php if(isset($missingSubCommitteeForProject)): ?>
        <?php if(in_array(auth()->user()->roleName(), [\App\Models\User::ADMIN, \App\Models\User::PROCUREMENT_OFFICER])): ?>
            <div class="missing-subcommittee-panel">
                <div class="ssc-head"><b>This project has no Sub-Committee.</b> Create a Sub-Committee before transferring.</div>
                <div id="sscError" class="ssc-error" style="display:none;"></div>
                <div class="ssc-row">
                    <div>
                        <label>Committee Name</label>
                        <input type="text" id="ssc_name" value="<?php echo e($missingSubCommitteeForProject['suggested_name']); ?>">
                    </div>
                    <div>
                        <label>Address (optional)</label>
                        <input type="text" id="ssc_address">
                    </div>
                </div>
                <div class="ssc-roster">
                    <label>Add members from the Committee Roster.</label>
                    <div id="sscRosterChecklist" class="ssc-checklist"><span class="muted">Loading…</span></div>
                </div>
                <button type="button" class="btn" id="sscCreateBtn" onclick="createSubCommittee(<?php echo e($missingSubCommitteeForProject['project_id']); ?>)">Create Sub-Committee</button>
            </div>
        <?php else: ?>
            <div class="missing-plan-notice">
                <div>This project has no Sub-Committee. Only an Admin/Procurement Officer can create a new Sub-Committee.</div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

        <?php if(!empty($step['coming_soon'])): ?>
            <div class="group-panel">
                <div class="coming-soon">This step's module is coming soon.</div>
            </div>
        <?php elseif(!empty($step['is_pr_picker'])): ?>
            <!-- <p class="case-hint">Pick an Approved PR — after that, every Process Step will work only on this PR.</p> -->
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
                                       <?php if($pr->project_name): ?>
                                        <span ><?php echo e($pr->project_name); ?></span> </br>
                                    <?php endif; ?>
                                    
                                    <?php echo e($pr->pr_number ?? ('PR-' . $pr->id)); ?> — Tk <?php echo e(number_format($pr->total_estimated_amount ?? 0, 2)); ?>

      
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
                   <div class="no-cases">No procurement  yet. <a href="<?php echo e(route('cases.create', $activePr ? ['pr_id' => $activePr->id] : [])); ?>">Open a new case</a> to get started.</div>
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
                            <div class="amount"> <?php echo e(number_format($case->amount, 2)); ?> taka</div>
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

    function loadSubCommitteeRoster() {
        const box = document.getElementById('sscRosterChecklist');
        if (!box) return;
        api.get('/procurement-committee-members').then(({ data }) => {
            if (!data.length) {
                box.innerHTML = '<span class="muted">Roster is empty — members can be added later.</span>';
                return;
            }
            box.innerHTML = `
                <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <colgroup><col style="width:28px;"><col></colgroup>
                    ${data.map(r => `
                        <tr>
                            <td style="padding:.4rem .4rem .1rem 0; vertical-align:top;">
                                <input type="checkbox" class="ssc-select-cb" value="${r.id}" id="ssc_r_${r.id}">
                            </td>
                            <td style="padding:.4rem 0 .1rem 0;">
                                <label for="ssc_r_${r.id}" style="font-weight:600; font-size:.85rem; cursor:pointer; word-break:break-word;">${r.name}</label>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="padding:0 0 .3rem 0;">
                                <input type="text" id="ssc_desig_${r.id}" value="${r.designation || ''}" placeholder="Designation" style="width:100%; box-sizing:border-box; padding:.3rem .5rem; font-size:.8rem; border:1px solid var(--line); border-radius:6px;">
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 .4rem 0 0; vertical-align:top;">
                                <input type="checkbox" class="ssc-login-cb" data-roster-id="${r.id}" id="ssc_login_${r.id}">
                            </td>
                            <td style="padding:0;">
                                <label for="ssc_login_${r.id}" style="font-size:.8rem; color:var(--muted); cursor:pointer;">Give login</label>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="padding:.3rem 0 .8rem 0;">
                                <div id="ssc_login_fields_${r.id}" style="display:none;">
                                    <input type="email" id="ssc_email_${r.id}" placeholder="Email" style="width:100%; box-sizing:border-box; padding:.35rem .5rem; font-size:.8rem; border:1px solid var(--line); border-radius:6px;">
                                </div>
                            </td>
                        </tr>
                        <tr><td colspan="2" style="border-bottom:1px solid var(--line); padding-bottom:.4rem;"></td></tr>
                    `).join('')}
                </table>
            `;

            box.querySelectorAll('.ssc-login-cb').forEach(cb => {
                cb.addEventListener('change', () => {
                    const fields = document.getElementById(`ssc_login_fields_${cb.dataset.rosterId}`);
                    if (fields) fields.style.display = cb.checked ? 'block' : 'none';
                });
            });
        }).catch(() => {
            box.innerHTML = '<span class="muted">Could not load the roster.</span>';
        });
    }
    loadSubCommitteeRoster();

    async function createSubCommittee(projectId) {
    const errBox = document.getElementById('sscError');
    if (!errBox) return;
    errBox.style.display = 'none';

    const name = document.getElementById('ssc_name').value.trim();
    const address = document.getElementById('ssc_address').value.trim();
    if (!name) {
        errBox.textContent = 'Please enter a Committee Name.';
        errBox.style.display = 'block';
        return;
    }

    const btn = document.getElementById('sscCreateBtn');
    btn.disabled = true;
    btn.textContent = 'Creating…';

    try {
        const { data: committee } = await api.post('/purchase-committees', {
            name,
            address: address || null,
            type: 'sub',
            project_id: projectId,
        });

        const checked = [...document.querySelectorAll('#sscRosterChecklist .ssc-select-cb:checked')];
        const createdLogins = [];
        for (const cb of checked) {
            const desigInput = document.getElementById(`ssc_desig_${cb.value}`);
            const designation = desigInput ? desigInput.value.trim() || null : null;
            const loginCb = document.getElementById(`ssc_login_${cb.value}`);

            let userId = null;
            if (loginCb && loginCb.checked) {
                const emailInput = document.getElementById(`ssc_email_${cb.value}`);
                const email = emailInput ? emailInput.value.trim() : '';
                if (!email) throw new Error('No Email was given for a member who was marked for login.');
                const { data: login } = await api.post('/committee-roster-logins', {
                    procurement_committee_member_id: cb.value,
                    committee_id: committee.id,
                    email,
                    designation,
                });
                userId = login.user.id;
                createdLogins.push(`${login.user.name} (${login.user.email}) — password: ${login.password}`);
            }

            await api.post('/committee-members', {
                committee_id: committee.id,
                procurement_committee_member_id: cb.value,
                user_id: userId,
                designation_in_committee: designation,
            });
        }

        if (createdLogins.length) {
            alert('New logins created — share them now, they are shown only once:\n' + createdLogins.join('\n'));
        }

        // After the page reloads, the new Sub-Committee will be picked up as
        // the destination, and the Transfer form's "To" field will be
        // auto-prefilled.
        window.location.href = window.location.pathname + window.location.search;
    } catch (err) {
        errBox.textContent = err.message;
        errBox.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Create Sub-Committee';
    }
    }
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/process-steps/show.blade.php ENDPATH**/ ?>