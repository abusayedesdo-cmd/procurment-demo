<?php $__env->startSection('title', 'Case Detail'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $backToStep = request()->query('focus') === 'meetings' ? request()->query('step') : null;
?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
    <?php if($backToStep): ?>
        <a href="<?php echo e(route('process-steps.show', ['slug' => $backToStep, 'skip_redirect' => 1])); ?>" style="font-size:12.5px;font-weight:600;text-decoration:none">← Back to Step</a>
        <a href="<?php echo e(route('cases.create')); ?>" class="btn btn-primary" style="font-size:12.5px">+ New Case</a>
    <?php else: ?>
        <a href="<?php echo e(route('process-steps.show', 'pr-receive')); ?>"
        style="font-size:12.5px;font-weight:600;text-decoration:none">← back </a>
    <?php endif; ?>
</div>

<div class="card" style="padding:22px">
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <b style="font-size:18px;flex:1;min-width:220px"><?php echo e($case->title); ?></b>
    <span class="chip chip-method" style="font-size:12px;padding:4px 10px"><?php echo e($case->method); ?></span>
    <span class="chip chip-<?php echo e(strtolower($case->category)); ?>" style="font-size:12px;padding:4px 10px"><?php echo e($case->category); ?></span>
  </div>
  <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:12px;font-size:13px;color:var(--muted)">
    <span>Ref: <b style="color:var(--ink)"><?php echo e($case->ref); ?></b></span>
    <span>Source PR: <b style="color:var(--ink)"><?php echo e($case->purchaseRequisition?->pr_number ?? '—'); ?></b></span>
    <span>Estimate: <b style="color:var(--ink)">৳ <?php echo e(number_format($case->amount, 2)); ?></b></span>
    <span>Solicitation docs: <b style="color:var(--ink)"><?php echo e(['RFQ' => 'Specification', 'RFP' => 'TOR', 'RFT' => 'BOQ, drawing & design'][$case->method]); ?></b></span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;margin-top:16px">
    <div class="progress" style="flex:1;height:8px"><div style="width:<?php echo e($case->progressPct()); ?>%"></div></div>
    <!-- <span style="font-size:12.5px;font-weight:700;color:var(--brand)">Step <?php echo e(min($case->current_step + 1, 23)); ?> of 23</span> -->
  </div>
</div>

<!-- <div class="card card-pad">
  <?php
    $plan = $case->purchaseRequisition?->procurementPlan;
    $latestTransfer = $plan?->subCommitteeTransfers->first();
  ?>
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <div>
      <b style="font-size:14px">Sub-Committee</b>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">
        <?php if($latestTransfer): ?>
          Currently with <b style="color:var(--ink)"><?php echo e($latestTransfer->toCommittee?->name); ?></b>
          (from <?php echo e($latestTransfer->fromCommittee?->name ?? '—'); ?>, <?php echo e($latestTransfer->transfer_date->format('d M Y')); ?>) — that committee's members can see this case on their dashboard.
        <?php else: ?>
          Not yet transferred to a sub-committee — it's still with the main/central committee.
        <?php endif; ?>
      </div>
    </div>
    <?php if($plan): ?>
      <a href="<?php echo e(route('modules.show', 'sub-committee-transfers')); ?>?new=1&field_procurement_plan_id=<?php echo e($plan->id); ?>"
         class="btn btn-primary" style="padding:6px 12px;font-size:12.5px">
        <?php echo e($latestTransfer ? 'Transfer again' : 'Transfer to Sub-Committee'); ?>

      </a>
    <?php else: ?>
      <span style="font-size:12px;color:var(--muted)">No Procurement Plan linked to this case's PR yet.</span>
    <?php endif; ?>
  </div>
</div> -->

<style>
  .step-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
  .step-no { width:18px; font-size:12px; flex:0 0 18px; }
  .step-label { flex:1 1 220px; min-width:0; font-size:12.5px; overflow-wrap:anywhere; }
  .doc-actions { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-left:auto; }
  .doc-btn { padding:5px 12px; font-size:12px; }
  .dl-menu { position:relative; }
  .dl-menu > summary { list-style:none; cursor:pointer; user-select:none; }
  .dl-menu > summary::-webkit-details-marker { display:none; }
  .dl-menu[open] > summary { background:var(--surface, #F8FAFC); }
  .dl-pop { position:absolute; right:0; top:calc(100% + 4px); z-index:20; min-width:170px; background:#fff; border:1px solid var(--line, #E2E8F0); border-radius:10px; box-shadow:0 10px 28px rgba(15,23,42,.14); padding:4px; }
  .dl-pop a { display:block; padding:8px 12px; border-radius:7px; color:var(--ink, #0F172A); text-decoration:none; font-size:13px; font-weight:600; }
  .dl-pop a:hover { background:var(--surface, #F8FAFC); }
  @media (max-width: 640px) {
    .doc-actions { flex:1 1 100%; margin-left:28px; }
    .doc-actions > .btn, .doc-actions > .dl-menu { flex:1 1 0; text-align:center; }
    .doc-btn { padding:9px 12px; font-size:13px; }
    .dl-menu > summary { display:block; }
    .dl-pop { left:0; right:0; }
  }
</style>

<div class="card card-pad">
  <b style="font-size:14px">Committee Meetings</b>
  <div style="font-size:12px;color:var(--muted);margin-top:2px">1st meeting sets the tender schedule; 2nd meeting records the tender opening &amp; award decision. Each one is recorded in 3 steps — Notice, Attendance, Resolution.</div>
  <div style="display:flex;flex-direction:column;gap:14px;margin-top:12px">
    <?php $__currentLoopData = ['first' => '1st Meeting — Tender Schedule', 'second' => '2nd Meeting — Opening & Award']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $m = $case->meetings->firstWhere('meeting_type', $type); ?>
      <div style="border:1px solid var(--line-soft);border-radius:10px;padding:12px 14px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div style="font-size:13px;font-weight:600"><?php echo e($label); ?></div>
          <?php if($m && $m->rezulation_no): ?>
            <span style="font-size:12px;color:var(--muted)">Rezulation No. <?php echo e($m->rezulation_no); ?> — <?php echo e($m->meeting_date->format('d M Y')); ?></span>
          <?php endif; ?>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;margin-top:12px">
          
          <div class="step-row">
            <span class="step-no" style="color:<?php echo e($m ? 'var(--good, #0D9488)' : 'var(--muted)'); ?>"><?php echo e($m ? '✓' : '1'); ?></span>
            <span class="step-label">Notice<?php echo e($m ? ' — ' . $m->notice_number : ''); ?></span>
            <div class="doc-actions">
              <?php if(! $m): ?>
                <a href="<?php echo e(route('meetings.notice.create', [$case, $type])); ?>" class="btn btn-primary doc-btn">Send notice</a>
              <?php else: ?>
                <?php echo $__env->make('cases._doc-buttons', [
                  'preview' => route('api.meetings.notice-preview', $m),
                  'word' => route('api.meetings.notice-document', $m),
                  'pdf' => route('api.meetings.notice-pdf', $m),
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              <?php endif; ?>
            </div>
          </div>

          
          <div class="step-row">
            <span class="step-no" style="color:<?php echo e($m?->attendance_number ? 'var(--good, #0D9488)' : 'var(--muted)'); ?>"><?php echo e($m?->attendance_number ? '✓' : '2'); ?></span>
            <span class="step-label">Attendance<?php echo e($m?->attendance_number ? ' — ' . $m->attendance_number : ''); ?></span>
            <div class="doc-actions">
              <?php if($m && ! $m->attendance_number): ?>
                <a href="<?php echo e(route('meetings.attendance.create', $m)); ?>" class="btn btn-primary doc-btn">Record attendance</a>
              <?php elseif($m?->attendance_number): ?>
                <?php echo $__env->make('cases._doc-buttons', [
                  'preview' => route('api.meetings.attendance-preview', $m),
                  'word' => route('api.meetings.attendance-document', $m),
                  'pdf' => route('api.meetings.attendance-pdf', $m),
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
              <?php else: ?>
                <span style="font-size:12px;color:var(--muted)">Waiting on notice</span>
              <?php endif; ?>
            </div>
          </div>

          
          <div class="step-row">
            <span class="step-no" style="color:<?php echo e($m?->rezulation_no ? 'var(--good, #0D9488)' : 'var(--muted)'); ?>"><?php echo e($m?->rezulation_no ? '✓' : '3'); ?></span>
            <span class="step-label">Resolution<?php echo e($m?->rezulation_no ? ' — Rezulation No. ' . $m->rezulation_no : ''); ?></span>
            <div class="doc-actions">
              <?php if($m?->attendance_number && ! $m->rezulation_no): ?>
                <a href="<?php echo e(route('meetings.resolution.create', $m)); ?>" class="btn btn-primary doc-btn">Finalize resolution</a>
              <?php elseif($m?->rezulation_no): ?>
                <?php echo $__env->make('cases._doc-buttons', [
                  'preview' => route('api.meetings.minutes-preview', $m),
                  'word' => route('api.meetings.minutes-document', $m),
                  'pdf' => route('api.meetings.minutes-pdf', $m),
                ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <a href="<?php echo e(route('meetings.show', $m)); ?>" class="btn btn-outline doc-btn">View minutes</a>
              <?php else: ?>
                <span style="font-size:12px;color:var(--muted)">Waiting on attendance</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>

<script>
  // Close the Download menu when clicking elsewhere or after choosing a file.
  document.addEventListener('click', function (e) {
    var link = e.target.closest ? e.target.closest('.dl-pop a') : null;
    document.querySelectorAll('details.dl-menu[open]').forEach(function (d) {
      if (link ? d.contains(link) : !d.contains(e.target)) d.open = false;
    });
  });
  // Opening one menu closes the others.
  document.addEventListener('toggle', function (e) {
    var d = e.target;
    if (d.classList && d.classList.contains('dl-menu') && d.open) {
      document.querySelectorAll('details.dl-menu[open]').forEach(function (o) { if (o !== d) o.open = false; });
    }
  }, true);
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/cases/show.blade.php ENDPATH**/ ?>