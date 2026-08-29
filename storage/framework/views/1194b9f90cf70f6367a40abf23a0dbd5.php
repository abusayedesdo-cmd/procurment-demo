<?php $__env->startSection('title', 'Rezulation-Minutes -' . $meeting->rezulation_no); ?>

<?php $__env->startSection('content'); ?>
    <div class="center">
        <p class="bold" style="font-size:13px;margin-bottom:0">Eco-Social Development Organization (ESDO)</p>
        <p class="muted" style="margin-top:0">Adabor, Dhaka-1207.</p>
    </div>

    <div class="meta" style="margin-top:14px">
        <p><span class="bold">Rezulation/Minutes No:</span> -<?php echo e($meeting->rezulation_no); ?></p>
        <p><span class="bold"><?php echo e($meeting->typeLabel()); ?></span></p>
    </div>

    <h1>MINUTES OF THE MEETING</h1>
    <p class="center">Central Procurement Committee</p>

    <table class="bordered">
        <tr><td class="bold" style="width:32%">Venue</td><td><?php echo e($meeting->location); ?></td></tr>
        <tr><td class="bold">Date</td><td><?php echo e($meeting->meeting_date->format('d F, Y')); ?><?php if($meeting->meeting_time): ?>, Time: <?php echo e($meeting->meeting_time); ?><?php endif; ?></td></tr>
        <tr><td class="bold">Procurement Reference</td><td><?php echo e($case->ref); ?> — <?php echo e($case->title); ?></td></tr>
    </table>

    <p style="margin-top:14px">
        The meeting was led by <?php echo e($convener->name ?? 'the Convener'); ?>, Convener of the ESDO Central Procurement
        Committee. At the beginning, he welcomed all the members and thanked them for joining, after which the
        meeting officially began.
    </p>

    <h2>Attendance of Procurement Committee Meeting</h2>
    <table class="bordered">
        <thead><tr><th style="width:8%">SL</th><th style="width:40%">Name</th><th>Designation</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $meeting->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td><td><?php echo e($a->name); ?></td><td><?php echo e($a->designation); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <h2>Meeting Agenda</h2>
    <ol class="terms">
        <li>Reading and approval of the minutes of the previous meeting.</li>
        <li><?php echo e($meeting->agenda ?: 'Discussion and decision on Procurement Reference ' . $case->ref); ?></li>
        <li>Miscellaneous.</li>
    </ol>

    <h2>Decisions of Today's Meeting</h2>
    <ol class="terms">
        <li>The Member Secretary read out the minutes of the previous meeting, which were approved by the committee without any amendment.</li>
        <li>
            <?php if($meeting->meeting_type === 'first'): ?>
                After discussion, the committee approved the following tender schedule for <?php echo e($case->ref); ?>:
                <ul style="margin:6px 0 0 18px">
                    <li>Tender Publication Date: <?php echo e(optional($meeting->publish_date)->format('d F Y') ?? '[TBD]'); ?></li>
                    <li>Tender Submission Deadline: <?php echo e(optional($meeting->closing_date)->format('d F Y') ?? '[TBD]'); ?></li>
                    <li>Tender Opening Date: <?php echo e(optional($meeting->opening_date)->format('d F Y') ?? '[TBD]'); ?></li>
                </ul>
                <?php if($meeting->schedule_override_reason): ?>
                    <p class="muted" style="margin-top:6px">Note: <?php echo e($meeting->schedule_override_reason); ?></p>
                <?php endif; ?>
            <?php else: ?>
                After evaluation, the committee approved the following award(s) for <?php echo e($case->ref); ?>:
                <table class="bordered" style="margin-top:8px">
                    <thead><tr><th>Vendor</th><th>Scope / Lot</th><th style="width:22%">Awarded Amount</th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $meeting->awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr><td><?php echo e($a->vendor_name); ?></td><td><?php echo e($a->scope_note); ?></td><td>৳ <?php echo e(number_format($a->amount, 2)); ?></td></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr><td colspan="2" class="bold" style="text-align:right">Total awarded</td><td class="bold">৳ <?php echo e(number_format($meeting->totalAwarded(), 2)); ?></td></tr>
                    </tbody>
                </table>
            <?php endif; ?>
            <?php if($meeting->decisions): ?>
                <p style="margin-top:6px"><?php echo e($meeting->decisions); ?></p>
            <?php endif; ?>
        </li>
        <li>There being no further matters to discuss, the meeting was adjourned with thanks to all present.</li>
    </ol>

    <div class="sig-block">
        <p>With thanks,</p>
        <p style="margin-top:24px" class="bold">(<?php echo e($convener->name ?? '[Convener Name]'); ?>)</p>
        <p>Convener, Central Procurement Committee,</p>
        <p>ESDO, Dhaka.</p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/meeting-minutes.blade.php ENDPATH**/ ?>