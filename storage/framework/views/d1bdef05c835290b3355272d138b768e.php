<?php $__env->startSection('title', 'Procurement Cases'); ?>
<?php $__env->startSection('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px">
  <div style="font-size:13px;color:var(--muted);max-width:680px">Each case follows the 23-step ESDO procurement process — Goods → RFQ, Services → RFP, Works → RFT.</div>
  <a href="<?php echo e(route('cases.create')); ?>" class="btn btn-primary" style="white-space:nowrap">+ New Case</a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px">
  <?php $__currentLoopData = $cases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('cases.show', $case)); ?>" class="card card-pad" style="display:flex;flex-direction:column;gap:10px;text-decoration:none;color:inherit">
      <div style="display:flex;align-items:center;gap:8px">
        <span class="chip chip-method"><?php echo e($case->method); ?></span>
        <span class="chip chip-<?php echo e(strtolower($case->category)); ?>"><?php echo e($case->category); ?></span>
        <span style="margin-left:auto;font-size:11.5px;font-weight:700;color:var(--muted)"><?php echo e($case->ref); ?></span>
      </div>
      <div style="font-size:14.5px;font-weight:700;line-height:1.4"><?php echo e($case->title); ?></div>
      <div style="font-size:12.5px;color:var(--muted)"><?php echo e($case->purchaseRequisition?->pr_no ?? '—'); ?> · ৳ <?php echo e(number_format($case->amount, 2)); ?></div>
      <div style="display:flex;align-items:center;gap:10px">
        <div class="progress" style="flex:1"><div style="width:<?php echo e($case->progressPct()); ?>%"></div></div>
        <span style="font-size:11.5px;font-weight:700;color:var(--brand);white-space:nowrap">Step <?php echo e(min($case->current_step + 1, 23)); ?>/23</span>
      </div>
      <div style="font-size:12px;font-weight:600;color:#44465E">Now: <?php echo e($case->currentStepName()); ?></div>
    </a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/cases/index.blade.php ENDPATH**/ ?>