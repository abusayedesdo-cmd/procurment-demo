<?php $__env->startSection('title', 'Tender Opening ' . ($rfq->rfq_number ?? '')); ?>

<?php $__env->startSection('content'); ?>
    <h1>TENDER OPENING REPORT</h1>
    <br>
    <p class="bold">RFQ/Tender Reference: <?php echo e($rfq->rfq_number ?? ''); ?></p>
    <p>Opening Date: <?php echo e(optional($opening->opening_date)->format('d F, Y')); ?></p>
    <p>Opened By: <?php echo e($opening->openedBy->name ?? '[Name]'); ?></p>

    <h2>Tender Opening Committee</h2>
    <table class="bordered">
        <thead>
            <tr><th style="width:8%">SL</th><th style="width:32%">Name</th><th style="width:32%">Designation</th><th style="width:28%">Signature</th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $committee; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($member->user->name ?? ''); ?></td>
                    <td><?php echo e($member->designation_in_committee ?? ''); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td>1</td><td colspan="3">[No committee members seeded]</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Bidder List &amp; Document Checklist</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%">SL</th>
                <th style="width:25%">Bidder / Vendor</th>
                <th style="width:15%">Bid Price (BDT)</th>
                <th style="width:13%">Trade License</th>
                <th style="width:11%">TIN</th>
                <th style="width:11%">BIN</th>
                <th style="width:20%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($q->vendor->name ?? ''); ?></td>
                    <td><?php echo e(number_format((float) $q->quoted_amount, 2)); ?></td>
                    <td class="center"><?php echo e($checkDoc($q->vendor_id, 'trade_license')); ?></td>
                    <td class="center"><?php echo e($checkDoc($q->vendor_id, 'tax_certificate')); ?></td>
                    <td class="center"><?php echo e($checkDoc($q->vendor_id, 'vat_certificate')); ?></td>
                    <td></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="7">[No quotations recorded against this RFQ yet]</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Bidder Representative Attendance</h2>
    <table class="bordered">
        <thead>
            <tr><th style="width:8%">SL</th><th style="width:23%">Vendor</th><th style="width:23%">Representative Name</th><th style="width:23%">Contact No.</th><th style="width:23%">Signature</th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($q->vendor->name ?? ''); ?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5">[No bidders to list]</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="margin-top:20px;">Remarks: <?php echo e($opening->remarks ?: '__________________________________________________'); ?></p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\New folder\procurment\resources\views/documents/tender-opening.blade.php ENDPATH**/ ?>