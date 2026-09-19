<?php $__env->startSection('title', 'RFQ ' . $rfq->rfq_number); ?>

<?php $__env->startSection('content'); ?>
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    <?php if(file_exists(public_path('img/esdo-logo.png'))): ?>
        <img src="<?php echo e(public_path('img/esdo-logo.png')); ?>" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    <?php endif; ?>
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 15px; font-weight: bold; margin-top: 2px;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 13px; font-weight: bold; line-height: 1.3;">House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207</div>
            <!-- <div style="font-size: 13px; font-weight: bold; line-height: 1.3;">Gobindanagar (Collegepara), Thakurgaon-5100</div> -->
            
        </div>
    </div>

    <table class="plain" style="margin-bottom:8px;">
        <tr>
            <td class="bold" style="text-align:left;">Memo: <?php echo e($rfq->rfq_number); ?></td>
            <td class="bold" style="text-align:right;">Date: <?php echo e(optional($rfq->issue_date)->format('d F, Y')); ?></td>
        </tr>
    </table>

    <h1>Request for Quotation (RFQ)</h1>
    <br>

    <p>
        Eco-Social Development Organization (ESDO) is hereby requesting quotation as per following
        <strong><?php echo e($rfq->procurementCase->purchaseRequisition->category->name ?? '[Category Name]'); ?></strong> vendors/suppliers for supplying
        <strong><?php echo e($rfq->procurementCase->purchaseRequisition->category->name ?? '[Category Name]'); ?></strong>
        equipment under "<strong><?php echo e($rfq->subject ?: '[Project Name]'); ?></strong>" in
        <strong><?php echo e($rfq->procurementCase->purchaseRequisition->delivery_location ?? '[Project Location]'); ?></strong>. Interested Vendors/Suppliers are requested to submit
        quotation through courier or directly according to the below mentioned terms &amp; conditions by
        or before on <?php echo e(optional($rfq->closing_date)->format('d F, Y')); ?> at 04:00 PM addressing to
        "Convener, Central Procurement Committee, Eco-Social Development Organization (ESDO),
        House # 748, Road# 8 Adabor, Dhaka".
    </p>

    <p class="bold">Items Details:</p>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%">Sl. No.</th>
                <th style="width:16%">Item Name</th>
                <th style="width:29%">Item Specification</th>
                <th style="width:8%">Unit</th>
                <th style="width:7%">Qty.</th>
                <th style="width:16%">Unit Price</th>
                <th style="width:19%">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($line->item->name ?? ''); ?></td>
                    <td><?php echo e($line->specification); ?></td>
                    <td><?php echo e($line->unit->symbol ?? $line->unit->name ?? ''); ?></td>
                    <td><?php echo e($line->quantity); ?></td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td>1</td><td>[No linked PR items found]</td><td></td><td></td><td></td><td></td><td></td></tr>
            <?php endif; ?>
            <tr>
                <td colspan="5" class="bold">Total Amount with VAT &amp; Tax</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <h2>Terms &amp; Conditions</h2>
    <ol class="terms">
        <li>Legal Document PDF Copy must be Submitted with Quotation: (Trade License, VAT Registration, TIN Certificate, PSR)</li>
        <li>Relevant Experience Certificate PDF Copy must be Submitted with Quotation.</li>
        <li>General Experience Certificate PDF Copy must be Submitted with Quotation.</li>
        <li>RFQ Receiving PDF Copy Need to Attach with the Quotation.</li>
        <li>Quotation will be Opened on <?php echo e(optional($rfq->closing_date)->format('d F, Y') ?: '[date]'); ?> at 04:00 PM (Those who will submit the quotation are invited to present at the opening time).</li>
        <li>Delivery Location: equipment must be delivered to "[Delivery Schedule]" office in <?php echo e($rfq->procurementCase->purchaseRequisition->delivery_location ?? '[Project Location]'); ?>.</li>
        <li>As per govt. rules and regulation vat &amp; tax will be deducted at the time of payment.</li>
        <li>The given price of the product must be valid for at least 15 days, and within this time frame the supplier is bound to supply products at the given price.</li>
        <li>Mode of payment: Payment will be made through Account Payee cheque/Pay order/RTGS/BEFTN or DD in favour of the supplying vendor after successful delivery of goods.</li>
        <li>ESDO reserves the authority to cancel — partially or fully — any quotation with or without explanation.</li>
        <li>ESDO never allows any harassment to women and children, and never allows child labour. Any institution or organization associated with such practices is strongly discouraged from participating in the bid.</li>
    </ol>

    <p style="margin-top:20px;">Thanks, with best regards</p>

    <table class="plain" style="margin-top:30px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                <p class="bold">(<?php echo e($signatoryName); ?>)</p>
                <p><?php echo e($signatoryTitle); ?></p>
                <p>Central Procurement Committee, ESDO, Dhaka-1207.</p>
            </td>
            <td style="width:50%; vertical-align:top; text-align:right;">
                <p>Receivers Signature &amp; Seal</p>
            </td>
        </tr>
    </table>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/rfq.blade.php ENDPATH**/ ?>