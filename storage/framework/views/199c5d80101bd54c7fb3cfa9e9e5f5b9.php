<?php $__env->startSection('title', 'Work Order ' . ($memo ?? '')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $fd = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d F, Y') : '';
    $money = fn ($v) => $v === null ? '' : number_format((float) $v, 2);
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $quoted = $rfqDate ? 'on ' . $fd($rfqDate) : 'earlier';
    $where = $deliveryPlaces ? ' for ' . implode(', ', $deliveryPlaces) : '';
    $by = $deadline ? ' by ' . $fd($deadline) : ' within the time agreed';
?>
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    <?php if(file_exists(public_path('img/esdo-logo.png'))): ?>
        <img src="<?php echo e(public_path('img/esdo-logo.png')); ?>" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    <?php endif; ?>
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;"><?php echo e($committeeAddress); ?></div>
        </div>
    </div>

    <table class="plain" style="margin-bottom:4px"><tr>
        <td class="bold">Memo: <?php echo e($memo); ?></td>
        <td class="bold" style="text-align:right">Date: <?php echo e($fd($date)); ?></td>
    </tr></table>

    <h1>WORK ORDER</h1>
    <br>
    <p>To,<br><span class="bold"><?php echo e($vendorName); ?></span><?php if($vendorAddress): ?><br><?php echo e($vendorAddress); ?><?php endif; ?></p>
    <p>Dear Sir/Madam,</p>
    <p style="text-align:justify">
        Eco-Social Development Organization (ESDO) invited quotations <?php echo e($quoted); ?> from interested genuine businesses/suppliers for the supply of
        <?php echo e($subject); ?><?php echo e($where); ?>. As per the quotations received, your organization is the lowest evaluated bidder, so this Work Order is being issued to you.
        You are requested to supply the items below<?php echo e($by); ?>, according to the description and terms &amp; conditions given here.
    </p>

    <table class="bordered">
        <thead>
            <tr><th style="width:7%">Sl. No.</th><th>Description</th><th style="width:13%">Total Qty.</th><th style="width:16%">Unit Price</th><th style="width:18%">Total Price</th></tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td style="text-align:center"><?php echo e($i + 1); ?></td>
                    <td><?php echo nl2br(e($l['description'])); ?></td>
                    <td style="text-align:center"><?php echo e($qty($l['qty'])); ?><?php echo e($l['unit'] ? ' ' . $l['unit'] : ''); ?></td>
                    <td style="text-align:right"><?php echo e($money($l['unit_price'])); ?></td>
                    <td style="text-align:right"><?php echo e($money($l['amount'])); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5">No item recorded.</td></tr>
            <?php endif; ?>
            <tr>
                <td colspan="4" class="bold">Total price including VAT &amp; Tax (BDT)</td>
                <td style="text-align:right" class="bold"><?php echo e($money($total)); ?></td>
            </tr>
        </tbody>
    </table>
    <p class="bold">In words: <?php echo e($amountWords); ?></p>

    <h2>Terms &amp; Conditions applicable:</h2>
    <ol class="terms">
        <?php $__currentLoopData = $terms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $term): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($term); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>

    <?php if($distribution): ?>
        <p style="margin-top:10px">The items must be delivered to the locations below according to these quantities:</p>
        <table class="bordered">
            <thead>
                <tr><th style="width:6%">Sl.</th><th>Location</th><?php $__currentLoopData = $distribution['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th><?php echo e(strtok($n, "\n")); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><th>Total</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $distribution['places']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $sum = 0; ?>
                    <tr>
                        <td style="text-align:center"><?php echo e($i + 1); ?></td><td><?php echo e($p); ?></td>
                        <?php $__currentLoopData = $distribution['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $v = $distribution['grid'][$p][$n] ?? 0; $sum += $v; ?>
                            <td style="text-align:center"><?php echo e($v ? $qty($v) : '-'); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td style="text-align:center" class="bold"><?php echo e($qty($sum)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Address for delivery of the items:</h2>
    <table class="bordered">
        <tr><td>
            <?php if($receiverName): ?><span class="bold"><?php echo e($receiverName); ?></span><br><?php endif; ?>
            <?php if($deliveryLocation): ?><?php echo e($deliveryLocation); ?><?php elseif($deliveryPlaces): ?><?php echo e(implode('; ', $deliveryPlaces)); ?><?php endif; ?>
            <?php if($receiverContact): ?><br>Mobile: <?php echo e($receiverContact); ?><?php endif; ?>
        </td></tr>
    </table>

    <table class="plain sig-block">
        <tr>
            <td style="width:55%">
                <span class="bold">(<?php echo e($signerName ?: '[Name]'); ?>)</span><br>
                  <?php echo e($signerLine); ?><br>
                ESDO, <?php echo e($committeeAddress); ?>

            </td>
            <td style="width:45%; text-align:right">Vendor/Recipient: signature of consent &amp; seal</td>
        </tr>
    </table>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('documents.layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Procument_Pro\Project_procrument\resources\views/documents/work-order.blade.php ENDPATH**/ ?>