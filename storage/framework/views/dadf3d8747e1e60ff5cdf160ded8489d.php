<?php $__env->startSection('title', 'Tender Schedule ' . $rfq->rfq_number); ?>

<?php $__env->startSection('content'); ?>
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    <?php if(file_exists(public_path('img/esdo-logo.png'))): ?>
        <img src="<?php echo e(public_path('img/esdo-logo.png')); ?>" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    <?php endif; ?>
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;">Collegepara(Gobindanagar),Thakurgaon, Rangpur, Bangladesh</div>
        </div>
    </div>

    <p class="bold">Reference: <?php echo e($rfq->rfq_number); ?></p>
    <p>Date: <?php echo e(optional($rfq->issue_date)->format('d.m.Y')); ?></p>

    <h1>TENDER SCHEDULE</h1>
    <p class="center">(<?php echo e($rfq->type); ?>)</p>

    <table class="bordered">
        <tr><td class="bold" style="width:35%">Procurement Reference</td><td><?php echo e($rfq->rfq_number); ?></td></tr>
        <tr><td class="bold">Procurement Nature</td><td><?php echo e($rfq->type); ?></td></tr>
        <tr><td class="bold">Date of Publication/Issue</td><td><?php echo e(optional($rfq->issue_date)->format('d F, Y')); ?></td></tr>
        <tr><td class="bold">Submission Deadline</td><td><?php echo e(optional($rfq->closing_date)->format('d F, Y, h:i A')); ?></td></tr>
        <tr><td class="bold">Tender Validity</td><td><?php echo e($validityDays); ?> days from the submission deadline</td></tr>
        <tr><td class="bold">Delivery Location</td><td>[Project Office / District — fill in]</td></tr>
        <tr><td class="bold">Expected Delivery Date</td><td><?php echo e(optional($rfq->procurementCase?->purchaseRequisition?->procurementPlan?->est_delivery_date)->format('d F, Y') ?: '[TBD]'); ?></td></tr>
        <tr><td class="bold">Performance Security</td><td><?php echo e($performanceSecurityPercent); ?>% of contract value (Works/Goods contracts above threshold)</td></tr>
        <tr><td class="bold">Delay Penalty</td><td><?php echo e($delayPenaltyPercent); ?>% of contract value per week of delay, max 10%</td></tr>
    </table>

    <h2>ANNEX-II: PRICE SCHEDULE</h2>
    <?php $__empty_1 = true; $__currentLoopData = $itemsByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $categoryName => $lines): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <p class="bold">Category: <?php echo e($categoryName); ?></p>
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width:6%">SL</th>
                    <th style="width:44%">Item Details</th>
                    <th style="width:12%">Qty</th>
                    <th style="width:12%">Unit</th>
                    <th style="width:26%">Unit Price (BDT)</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($i + 1); ?></td>
                        <td><?php echo e($line->item->name ?? ''); ?><?php echo e($line->item->specification ? ': '.$line->item->specification : ''); ?></td>
                        <td><?php echo e($line->quantity); ?></td>
                        <td><?php echo e($line->unit->symbol ?? $line->unit->name ?? ''); ?></td>
                        <td></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p>[No linked PR items found — link a Procurement Plan with PR items to auto-fill this table.]</p>
    <?php endif; ?>

    <h2>ANNEX-III: TECHNICAL EVALUATION SHEET</h2>
    <p>Total Marks: 100 (Technical: 60, Financial: 40). Minimum 60% required on Technical to qualify for financial evaluation.</p>
    <table class="bordered">
        <thead>
            <tr><th style="width:8%">SL</th><th style="width:72%">Evaluation Criteria</th><th style="width:20%">Marks</th></tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $technicalCriteria; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $criterion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td><?php echo e($i + 1); ?></td><td><?php echo e($criterion); ?></td><td>10</td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr><td colspan="2" class="bold">Total</td><td class="bold">60</td></tr>
        </tbody>
    </table>

    <h2>ANNEX-IV: TERMS &amp; CONDITIONS</h2>
    <ol class="terms">
        <li>Bidders must submit valid Trade License, VAT Registration Certificate, TIN Certificate, and Proof of Return Submission (PSR) with the bid.</li>
        <li>Bids must be submitted in a sealed envelope, addressed to the Convener, Central Procurement Committee, ESDO, on or before the submission deadline above.</li>
        <li>Bids received after the deadline will not be accepted under any circumstances.</li>
        <li>Prices quoted must be inclusive of VAT &amp; Tax, and must remain valid for the period stated above.</li>
        <li>The successful bidder will be required to submit a Performance Security as stated above, within 7 days of receiving the Notification of Award.</li>
        <li>ESDO reserves the right to accept or reject any or all bids without assigning any reason.</li>
        <li>Any form of collusion, bribery, or fraudulent practice will result in immediate disqualification and may be reported to the appropriate authorities.</li>
    </ol>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/tender-schedule.blade.php ENDPATH**/ ?>