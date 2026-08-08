<?php $__env->startSection('title', 'RFQ ' . $rfq->rfq_number); ?>

<?php $__env->startSection('content'); ?>
    <p class="bold">Memo: <?php echo e($rfq->rfq_number); ?>&emsp;&emsp;&emsp;Date: <?php echo e(optional($rfq->issue_date)->format('d.m.Y')); ?></p>

    <h1>Request for Quotation (RFQ)</h1>
    <br>

    <p>
        Eco-Social Development Organization (ESDO) is hereby requesting quotation from original
        vendors/Suppliers for supplying items under ESDO- "[Project Name]" in [District].
        Interested Vendors/Suppliers are requested to submit Quotation through Courier or directly
        according to the below mentioned terms &amp; conditions by or before
        <?php echo e(optional($rfq->closing_date)->format('d F, Y')); ?> at 04:00 PM addressing to
        "Convener, Central Procurement Committee, Eco-Social Development Organization (ESDO),
        House # 748, Road# 8 Adabor, Dhaka".
    </p>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:6%">SL No</th>
                <th style="width:44%">Item Details</th>
                <th style="width:12%">Qty</th>
                <th style="width:19%">Unit Price</th>
                <th style="width:19%">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($line->item->name ?? ''); ?><?php echo e($line->item->specification ? ': '.$line->item->specification : ''); ?></td>
                    <td><?php echo e($line->quantity); ?> <?php echo e($line->unit->symbol ?? $line->unit->name ?? ''); ?></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td>1</td><td>[No linked PR items found]</td><td></td><td></td><td></td></tr>
            <?php endif; ?>
            <tr>
                <td colspan="3" class="bold">Total with Vat-Tax</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <h2>Terms &amp; Conditions</h2>
    <ol class="terms">
        <li>Photocopy of Valid Trade license, TIN certificate, PSR Copy, BIN Certificate, RFQ receiving copy need to attach with the Quotation.</li>
        <li>Quotation will be opened at <?php echo e(optional($rfq->closing_date)->format('h:i A') ?: '[time]'); ?> on <?php echo e(optional($rfq->closing_date)->format('d F, Y') ?: '[date]'); ?> (Those who will submit the quotation are invited to present at the time of opening).</li>
        <li>Items must be delivered to the project office location as specified in the Procurement Plan.</li>
        <li>As per govt. rules and regulation vat &amp; tax will be deducted at the time of payment.</li>
        <li>The given price of the product must be valid for at least 15 days, and within this time frame the supplier is bound to supply products at the given price.</li>
        <li>Mode of payment: Payment will be made through Account Payee cheque/Pay order/RTGS/BEFTN or DD in favour of the supplying vendor after successful delivery of goods.</li>
        <li>ESDO reserves the authority to cancel — partially or fully — any quotation with or without explanation.</li>
        <li>ESDO never allows any harassment to women and children, and never allows child labour. Any institution or organization associated with such practices is strongly discouraged from participating in the bid.</li>
    </ol>

    <p style="margin-top:20px;">Thanks, with best regards</p>

    <div class="sig-block">
        <p class="bold">(<?php echo e($signatoryName); ?>)</p>
        <p><?php echo e($signatoryTitle); ?></p>
        <p>Central Procurement Committee, ESDO, Dhaka-1207.</p>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/rfq.blade.php ENDPATH**/ ?>