<?php $__env->startSection('title', 'Case Detail'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $backToStep = request()->query('focus') === 'meetings' ? request()->query('step') : null;
?>
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
    <?php if($backToStep): ?>
        <a href="<?php echo e(route('process-steps.show', $backToStep)); ?>" style="font-size:12.5px;font-weight:600;text-decoration:none">← Back to Step</a>
        <a href="<?php echo e(route('cases.create')); ?>" class="btn btn-primary" style="font-size:12.5px">+ New Case</a>
    <?php else: ?>
        <a href="<?php echo e(route('dashboard')); ?>" style="font-size:12.5px;font-weight:600;text-decoration:none">← Dashboard</a>
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
    <span>Source PR: <b style="color:var(--ink)"><?php echo e($case->purchaseRequisition?->pr_no ?? '—'); ?></b></span>
    <span>Estimate: <b style="color:var(--ink)">৳ <?php echo e(number_format($case->amount, 2)); ?></b></span>
    <span>Solicitation docs: <b style="color:var(--ink)"><?php echo e(['RFQ' => 'Specification', 'RFP' => 'TOR', 'RFT' => 'BOQ, drawing & design'][$case->method]); ?></b></span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;margin-top:16px">
    <div class="progress" style="flex:1;height:8px"><div style="width:<?php echo e($case->progressPct()); ?>%"></div></div>
    <span style="font-size:12.5px;font-weight:700;color:var(--brand)">Step <?php echo e(min($case->current_step + 1, 23)); ?> of 23</span>
  </div>
</div>

<div class="card card-pad">
  <b style="font-size:14px">Committee Meetings</b>
  <div style="font-size:12px;color:var(--muted);margin-top:2px">1st meeting sets the tender schedule; 2nd meeting records the tender opening &amp; award decision.</div>
  <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px">
    <?php $__currentLoopData = ['first' => '1st Meeting — Tender Schedule', 'second' => '2nd Meeting — Opening & Award']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php $m = $case->meetings->firstWhere('meeting_type', $type); ?>
      <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--line-soft);border-radius:10px">
        <div style="flex:1;font-size:13px;font-weight:600"><?php echo e($label); ?></div>
        <?php if($m): ?>
          <span style="font-size:12px;color:var(--muted)">Rezulation No. <?php echo e($m->rezulation_no); ?> — <?php echo e($m->meeting_date->format('d M Y')); ?></span>
          <a href="<?php echo e(route('meetings.show', $m)); ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12px">View minutes</a>
        <?php else: ?>
          <a href="<?php echo e(route('meetings.create', [$case, $type])); ?>" class="btn btn-primary" style="padding:6px 12px;font-size:12px">Record meeting</a>
        <?php endif; ?>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/cases/show.blade.php ENDPATH**/ ?>