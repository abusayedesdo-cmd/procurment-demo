<?php $__env->startSection('title', 'My Committee Work'); ?>
<?php $__env->startSection('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
  <div style="font-size:13px;color:var(--muted);max-width:680px">
    Cases currently transferred to a committee you're on. When a case moves to another committee later, it drops off this list automatically.
  </div>
</div>

<?php if($myCommittees->isNotEmpty()): ?>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
    <?php $__currentLoopData = $myCommittees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $membership): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <span class="chip" style="font-size:11.5px"><?php echo e($membership->committee?->name); ?> — <?php echo e($membership->designation_in_committee ?? 'Member'); ?></span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
<?php endif; ?>

<?php if($transfers->isEmpty()): ?>
  <div class="card card-pad" style="margin-top:14px">
    <div style="font-size:13.5px;color:var(--muted)">No cases are currently with your committee(s).</div>
  </div>
<?php else: ?>
  <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;margin-top:14px">
    <?php $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $case = $t->procurementPlan?->purchaseRequisition?->procurementCase;
        $pr = $t->procurementPlan?->purchaseRequisition;
      ?>
      <div class="card card-pad" style="display:flex;flex-direction:column;gap:10px">
        <div style="display:flex;align-items:center;gap:8px">
          <span class="chip" style="font-size:11.5px"><?php echo e($t->toCommittee?->name); ?></span>
          <span style="margin-left:auto;font-size:11.5px;color:var(--muted)">Transferred <?php echo e($t->transfer_date->format('d M Y')); ?></span>
        </div>
        <div style="font-size:14.5px;font-weight:700;line-height:1.4"><?php echo e($case->title ?? ($pr->pr_number ?? 'PR #' . $t->procurementPlan?->pr_id)); ?></div>
        <div style="font-size:12.5px;color:var(--muted)"><?php echo e($pr?->pr_number ?? '—'); ?> <?php if($t->fromCommittee): ?> · from <?php echo e($t->fromCommittee->name); ?> <?php endif; ?></div>
        <?php if($t->transfer_note): ?>
          <div style="font-size:12px;color:var(--muted);font-style:italic">"<?php echo e($t->transfer_note); ?>"</div>
        <?php endif; ?>
        <div style="display:flex;gap:8px;margin-top:4px">
          <?php if($case): ?>
            <a href="<?php echo e(route('cases.show', $case)); ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">Open Case</a>
          <?php endif; ?>
          <?php if($pr): ?>
            <a href="<?php echo e(route('purchase-requisitions.show', $pr)); ?>" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">Open PR</a>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/committee-work/index.blade.php ENDPATH**/ ?>