<?php $__env->startSection('title', 'Meeting Minutes'); ?>
<?php $__env->startSection('content'); ?>

<div><a href="<?php echo e(route('cases.show', $meeting->procurementCase)); ?>" style="font-size:12.5px;font-weight:600;text-decoration:none">← <?php echo e($meeting->procurementCase->ref); ?></a></div>

<div class="card" style="padding:32px;max-width:760px;margin:0 auto">
  <div style="text-align:center">
    <b style="font-size:15px">Eco-Social Development Organization (ESDO)</b>
    <div style="font-size:12.5px;color:var(--muted)">Adabor, Dhaka-1207.</div>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:baseline;margin-top:20px;border-top:1px solid var(--line-soft);border-bottom:1px solid var(--line-soft);padding:10px 0">
    <b style="font-size:13.5px">Rezulation No: -<?php echo e($meeting->rezulation_no); ?></b>
    <span style="font-size:12.5px;color:var(--muted)"><?php echo e($meeting->typeLabel()); ?></span>
  </div>

  <div style="display:flex;justify-content:space-between;margin-top:14px;font-size:13px">
    <span><b>Location:</b> <?php echo e($meeting->location); ?></span>
    <span><b>Date:</b> <?php echo e($meeting->meeting_date->format('d.m.Y')); ?><?php if($meeting->meeting_time): ?>, Time: <?php echo e($meeting->meeting_time); ?><?php endif; ?></span>
  </div>

  <p style="font-size:13px;margin-top:16px;line-height:1.6">
    The meeting was led by <?php echo e($meeting->attendees->first()?->name ?? 'the Convener'); ?>, Convener of the ESDO Central Procurement Committee.
    At the beginning, he welcomed all the members and thanked them for joining. After that, he started the meeting officially.
  </p>

  <b style="font-size:13px;display:block;margin-top:18px">Names of Members</b>
  <table class="data" style="margin-top:8px">
    <thead><tr><th style="width:36px">SN</th><th>Name</th><th>Designation</th><th style="width:120px">Signature</th></tr></thead>
    <tbody>
      <?php $__currentLoopData = $meeting->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr><td><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td><td><?php echo e($a->name); ?></td><td><?php echo e($a->designation); ?></td><td></td></tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>

  <b style="font-size:13px;display:block;margin-top:18px">Agenda of the Meeting</b>
  <p style="font-size:13px;margin-top:6px;line-height:1.6;white-space:pre-line"><?php echo e($meeting->agenda); ?></p>

  <b style="font-size:13px;display:block;margin-top:18px">Decisions of Today's Meeting</b>
  <p style="font-size:13px;margin-top:6px;line-height:1.6;white-space:pre-line"><?php echo e($meeting->decisions); ?></p>

  <?php if($meeting->meeting_type === 'first' && $meeting->publish_date): ?>
    <div style="background:#F7F8FC;border-radius:10px;padding:14px 16px;margin-top:14px;font-size:13px;line-height:1.8">
      <b>Tender Schedule Confirmed:</b><br>
      Tender Publication Date: <?php echo e($meeting->publish_date->format('d F Y')); ?><br>
      Tender Submission Deadline: <?php echo e($meeting->closing_date?->format('d F Y')); ?><br>
      Tender Opening Date: <?php echo e($meeting->opening_date?->format('d F Y')); ?>

      <?php if($meeting->schedule_override_reason): ?>
        <br><span style="color:var(--muted)">Note: <?php echo e($meeting->schedule_override_reason); ?></span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if($meeting->meeting_type === 'second' && $meeting->awards->isNotEmpty()): ?>
    <b style="font-size:13px;display:block;margin-top:18px">Award Decision</b>
    <table class="data" style="margin-top:8px">
      <thead><tr><th>Vendor</th><th>Scope / Lot</th><th class="num">Awarded Amount</th></tr></thead>
      <tbody>
        <?php $__currentLoopData = $meeting->awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr><td><?php echo e($a->vendor_name); ?></td><td><?php echo e($a->scope_note); ?></td><td class="num">৳ <?php echo e(number_format($a->amount, 2)); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <tr><td colspan="2" style="text-align:right;font-weight:700">Total awarded</td><td class="num" style="font-weight:700">৳ <?php echo e(number_format($meeting->totalAwarded(), 2)); ?></td></tr>
      </tbody>
    </table>
  <?php endif; ?>

  <div style="margin-top:32px;font-size:13px">
    <div>With thanks,</div>
    <div style="margin-top:36px"><b>(<?php echo e($meeting->attendees->first()?->name); ?>)</b></div>
    <div>Convener, Central Procurement Committee,</div>
    <div>ESDO, Dhaka.</div>
  </div>

  <div style="margin-top:24px;padding-top:12px;border-top:1px solid var(--line-soft);font-size:11px;color:var(--faint)">
    Recorded by <?php echo e($meeting->recordedBy?->name ?? '—'); ?> on <?php echo e($meeting->created_at->format('d M Y, H:i')); ?>

  </div>
</div>

<div style="max-width:760px;margin:14px auto 0;text-align:right;display:flex;gap:8px;justify-content:flex-end">
  <a href="<?php echo e(route('api.meetings.notice-document', $meeting)); ?>" class="btn btn-outline">Notice PDF</a>
  <a href="<?php echo e(route('api.meetings.attendance-document', $meeting)); ?>" class="btn btn-outline">Attendance PDF</a>
  <a href="<?php echo e(route('api.meetings.minutes-document', $meeting)); ?>" class="btn btn-outline">Minutes PDF</a>
  <button onclick="window.print()" class="btn btn-outline">Print</button>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/meetings/show.blade.php ENDPATH**/ ?>