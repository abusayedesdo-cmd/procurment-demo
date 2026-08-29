<?php $__env->startSection('title', 'Meeting Attendance ' . $meeting->attendance_number); ?>

<?php $__env->startSection('content'); ?>
    <div class="center">
        <p class="bold" style="font-size:13px;margin-bottom:0">Eco-Social Development Organization (ESDO)</p>
        <p class="muted" style="margin-top:0">Adabor, Dhaka-1207.</p>
    </div>

    <div class="meta" style="margin-top:14px">
        <p><span class="bold">Attendance No:</span> <?php echo e($meeting->attendance_number); ?></p>
        <p><span class="bold">Date:</span> <?php echo e($meeting->meeting_date->format('d F, Y')); ?></p>
    </div>

    <h1>MEETING ATTENDANCE</h1>
    <p class="center">Central Procurement Committee</p>

    <table class="bordered">
        <tr><td class="bold" style="width:32%">Procurement Reference</td><td><?php echo e($case->ref); ?></td></tr>
        <tr><td class="bold">Subject</td><td><?php echo e($case->title); ?></td></tr>
        <tr><td class="bold">Venue</td><td><?php echo e($meeting->location); ?></td></tr>
    </table>

    <h2>Meeting Agenda</h2>
    <ol class="terms">
        <li><?php echo e($meeting->agenda ?: 'Discussion and decision on Procurement Reference ' . $case->ref . ' — ' . $case->title); ?></li>
        <li>Miscellaneous.</li>
    </ol>

    <h2>Attendance</h2>
    <table class="bordered">
        <thead>
            <tr><th style="width:6%">SL</th><th style="width:28%">Name</th><th style="width:28%">Designation</th><th style="width:20%">Signature</th><th>Remarks</th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $meeting->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                    <td><?php echo e($a->name); ?></td>
                    <td><?php echo e($a->designation); ?></td>
                    <td>&nbsp;</td>
                    <td><?php echo e($a->remarks); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php $__currentLoopData = $roster; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                        <td><?php echo e($m->name); ?></td>
                        <td><?php echo e($m->designation); ?></td>
                        <td>&nbsp;</td>
                        <td></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="sig-block">
        <p class="bold">Approved</p>
        <p style="margin-top:24px">(<?php echo e($convener->name ?? '[Convener Name]'); ?>)</p>
        <p class="bold">Convener, Central Procurement Committee, ESDO.</p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/meeting-attendance.blade.php ENDPATH**/ ?>