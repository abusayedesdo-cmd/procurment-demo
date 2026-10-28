<?php $__env->startSection('title', 'Procurement Report ' . ($case->ref ?? '')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d F, Y') : '-';
    $m = fn ($v) => $v === null ? '-' : number_format((float) $v, 2);
    $mark = fn ($v) => $v === null ? '-' : number_format((float) $v, 2);
    $ord = function ($n) { if (! $n) return '-'; $s = ['th', 'st', 'nd', 'rd']; $v = $n % 100; return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]); };
?>
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    <?php if(file_exists(public_path('img/esdo-logo.png'))): ?>
        <img src="<?php echo e(public_path('img/esdo-logo.png')); ?>" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    <?php endif; ?>
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;"><?php echo e($committeeName); ?>, <?php echo e($committeeAddress); ?></div>
        </div>
    </div>

    <h1>PROCUREMENT REPORT</h1>
    <p class="center muted">Report date: <?php echo e($generatedAt->format('d F, Y')); ?></p>

    <h2>1. Procurement Summary</h2>
    <table class="bordered">
        <tbody>
            <tr><td style="width:30%" class="bold">Case reference</td><td><?php echo e($case->ref); ?></td></tr>
            <tr><td class="bold">Subject</td><td><?php echo e($case->title); ?></td></tr>
            <tr><td class="bold">Purchase Requisition No.</td><td><?php echo e($pr->pr_number ?? '-'); ?> (dated <?php echo e($d($pr->requisition_date ?? null)); ?>)</td></tr>
            <tr><td class="bold">Project</td><td><?php echo e($pr->project_name ?? '-'); ?></td></tr>
            <tr><td class="bold">Category / Method</td><td><?php echo e($case->category); ?> — <?php echo e($case->natureLabel()); ?></td></tr>
            <tr><td class="bold">Requested by</td><td><?php echo e($pr->requestor_name ?? '-'); ?><?php if(!empty($pr->requestor_designation)): ?>, <?php echo e($pr->requestor_designation); ?><?php endif; ?></td></tr>
            <tr><td class="bold">Delivery location</td><td><?php echo e($pr->delivery_location ?? '-'); ?></td></tr>
            <tr><td class="bold">Procurement committee</td><td><?php echo e($committeeName); ?></td></tr>
            <tr><td class="bold">Estimated cost (BDT)</td><td><?php echo e($m($estimated)); ?></td></tr>
            <tr><td class="bold">Awarded amount (BDT)</td><td><?php echo e($m($awarded)); ?></td></tr>
            <tr><td class="bold">Difference (estimate − award)</td><td><?php echo e($saving === null ? '-' : $m($saving) . ($saving >= 0 ? ' (below estimate)' : ' (above estimate)')); ?></td></tr>
            <tr><td class="bold">Selected vendor</td><td><?php echo e($award->vendor->name ?? ($recommended ? $recommended . ' (recommended)' : 'Not awarded yet')); ?></td></tr>
        </tbody>
    </table>

    <?php if($items->isNotEmpty()): ?>
        <h2>2. Items Procured</h2>
        <table class="bordered">
            <thead><tr><th style="width:6%">SL</th><th>Item</th><th>Specification</th><th style="width:9%">Unit</th><th style="width:9%">Qty</th><th style="width:14%">Rate (BDT)</th><th style="width:15%">Total (BDT)</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><?php echo e($i + 1); ?></td><td><?php echo e($it->item->name ?? ''); ?></td><td><?php echo e($it->specification); ?></td><td><?php echo e($it->unit->name ?? ''); ?></td>
                        <td style="text-align:right"><?php echo e(rtrim(rtrim(number_format((float) $it->quantity, 2), '0'), '.')); ?></td>
                        <td style="text-align:right"><?php echo e($m($it->rate_bdt)); ?></td><td style="text-align:right"><?php echo e($m($it->total_amount)); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>3. Key Dates</h2>
    <table class="bordered">
        <thead><tr><th style="width:42%">Step</th><th style="width:22%">Date</th><th>Reference</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr><td><?php echo e($t['label']); ?></td><td><?php echo e($d($t['date'])); ?></td><td><?php echo e($t['ref']); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="3">No dated records yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($meetings->isNotEmpty()): ?>
        <h2>4. Committee Meetings</h2>
        <table class="bordered">
            <thead><tr><th style="width:6%">SL</th><th style="width:16%">Meeting</th><th style="width:20%">Date</th><th style="width:18%">Resolution No.</th><th>Agenda / Decision</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $meetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $mt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><?php echo e($i + 1); ?></td><td><?php echo e($mt->meeting_type === 'second' ? '2nd meeting' : '1st meeting'); ?></td><td><?php echo e($d($mt->meeting_date)); ?></td><td><?php echo e($mt->rezulation_no ?? '-'); ?></td><td><?php echo e(\Illuminate\Support\Str::limit(trim(($mt->decisions ?: $mt->agenda) ?? ''), 220)); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>5. Quotations Received <?php if($rfq): ?><span class="muted" style="font-weight:normal;font-size:11px">— RFQ <?php echo e($rfq->rfq_number); ?><?php if($rfq_count > 1): ?> (latest of <?php echo e($rfq_count); ?> RFQs)<?php endif; ?>, closing <?php echo e($d($rfq->closing_date)); ?></span><?php endif; ?></h2>
    <table class="bordered">
        <thead><tr><th style="width:6%">SL</th><th>Vendor</th><th style="width:20%">Quoted amount (BDT)</th><th style="width:20%">Status</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $quotations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr><td><?php echo e($i + 1); ?></td><td><?php echo e($q->vendor->name ?? ''); ?></td><td style="text-align:right"><?php echo e($m($q->quoted_amount)); ?></td><td><?php echo e(ucfirst((string) $q->status)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="4">No quotation recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if($ranking->isNotEmpty()): ?>
        <h2>6. Evaluation Result</h2>
        <table class="bordered">
            <thead><tr><th style="width:6%">SL</th><th>Vendor</th><th style="width:14%">Bid (BDT)</th><th>Technical</th><th>Financial</th><?php if($has_sample): ?><th>Sample</th><?php endif; ?><th>Total</th><th style="width:7%">Rank</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $ranking; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr><td><?php echo e($i + 1); ?></td><td><?php echo e($r->vendor->name ?? ''); ?></td><td style="text-align:right"><?php echo e($m($r->amount)); ?></td>
                        <td style="text-align:center"><?php echo e($mark($r->technical_marks)); ?></td><td style="text-align:center"><?php echo e($mark($r->financial_marks)); ?></td>
                        <?php if($has_sample): ?><td style="text-align:center"><?php echo e($mark($r->sample_marks)); ?></td><?php endif; ?>
                        <td style="text-align:center" class="bold"><?php echo e($mark($r->total_marks)); ?></td><td style="text-align:center"><?php echo e($ord($r->rank)); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php if($recommended): ?><p>Recommended vendor: <span class="bold"><?php echo e($recommended); ?></span></p><?php endif; ?>
    <?php endif; ?>

    <h2>7. Award, Contract & Delivery</h2>
    <table class="bordered">
        <tbody>
            <tr><td style="width:30%" class="bold">Notification of Award</td><td><?php if($award): ?><?php echo e($award->noa_number); ?> — <?php echo e($d($award->noa_date)); ?> — <?php echo e($award->vendor->name ?? ''); ?><?php else: ?> Not issued yet <?php endif; ?></td></tr>
            <tr><td class="bold">Pay Order</td><td><?php if($payOrder): ?>Pay order <?php echo e($m($payOrder->pay_order_amount)); ?> BDT; received <?php echo e($m($payOrder->received_amount)); ?> BDT on <?php echo e($d($payOrder->received_date)); ?><?php else: ?> - <?php endif; ?></td></tr>
            <tr><td class="bold">Contract Agreement</td><td><?php if($agreement): ?><?php echo e($agreement->agreement_number); ?> — <?php echo e($d($agreement->agreement_date)); ?><?php else: ?> - <?php endif; ?></td></tr>
            <tr><td class="bold">Work Order</td><td><?php if($workOrder): ?><?php echo e($workOrder->wo_number); ?> — <?php echo e($d($workOrder->wo_date)); ?><?php else: ?> - <?php endif; ?></td></tr>
            <tr><td class="bold">Delivery</td><td><?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php echo e($d($dv->delivery_date)); ?><?php if($dv->received_by): ?>, received by <?php echo e($dv->received_by); ?><?php endif; ?> <?php if($dv->remarks): ?>(<?php echo e($dv->remarks); ?>)<?php endif; ?><br><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> - <?php endif; ?></td></tr>
        </tbody>
    </table>

    <h2>8. Document Checklist</h2>
    <table class="bordered">
        <thead><tr><th style="width:8%">SL</th><th>Document</th><th style="width:16%">Records</th><th style="width:18%">Status</th></tr></thead>
        <tbody>
            <?php $__currentLoopData = $checklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr><td><?php echo e($i + 1); ?></td><td><?php echo e($row[0]); ?></td><td style="text-align:center"><?php echo e($row[1]); ?></td><td><?php echo e($row[1] > 0 ? 'Available' : 'Not yet'); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <table class="plain sig-block">
        <tr>
            <td style="width:33%">Prepared by<br><br><br><?php echo e($preparedBy ?? ''); ?></td>
            <td style="width:34%; text-align:center">Member Secretary<br><br><br><?php echo e($committeeName); ?></td>
            <td style="width:33%; text-align:right">Convener<br><br><br><?php echo e($committeeName); ?></td>
        </tr>
    </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Procument_Pro\Project_procrument\resources\views/documents/procurement-report.blade.php ENDPATH**/ ?>