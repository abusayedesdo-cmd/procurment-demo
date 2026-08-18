<!DOCTYPE html>
<html>
<head>
<style>
    @page { margin: 8px 10px; }
    body { font-family: sans-serif; font-size: 7px; }
    table { border-collapse: collapse; width: 100%; table-layout: fixed; }
    th, td { border: 1px solid #333; padding: 1px 2px; text-align: center; word-wrap: break-word; overflow-wrap: break-word; }
    h2, p { text-align: center; margin: 2px 0; }
</style>
</head>
<body>

    <!-- Header Section -->
    <!-- <div style="width: 100%; margin-bottom: 8px;">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                <td style="width: 80px; vertical-align: middle; border: none; padding: 0;">
                    <?php if(file_exists(public_path('img/esdo-logo.png'))): ?>
                        <img src="<?php echo e(public_path('img/esdo-logo.png')); ?>" style="height: 50px; width: auto; display: block;">
                    <?php endif; ?>
                </td>
                <td style="vertical-align: middle; text-align: center; border: none; padding: 0;">
                    <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
                    <div style="font-size: 10px; color: #555; margin-top: 2px;">Collegepara(Gobindanagar),Thakurgaon, Rangpur, Bangladesh</div>
                </td>
                <td style="width: 80px; border: none; padding: 0;"></td>
            </tr>
        </table>
    </div> -->
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    <?php if(file_exists(public_path('img/esdo-logo.png'))): ?>
        <img src="<?php echo e(public_path('img/esdo-logo.png')); ?>" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    <?php endif; ?>
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;">Collegepara(Gobindanagar),Thakurgaon, Rangpur, Bangladesh</div>
        </div>
    </div>

    <!-- Project Info Section -->
    <table style="border:none; margin: 4px auto 10px; width:auto;">
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Name/Title</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e($plan->project_name ?? $plan->title); ?></td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Location (Office)</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e($plan->project_location); ?></td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Working Area</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e($plan->working_area); ?></td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Duration</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e($plan->project_duration ?? (optional($plan->fiscal_year_start)->format('d M Y') . ' to ' . optional($plan->fiscal_year_end)->format('d M Y'))); ?></td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Date of Agreement/Awarded</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e(optional($plan->agreement_date)->format('d M Y')); ?></td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Donor Name</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e($plan->donor_name); ?></td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Activity Summary</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;"><?php echo e($plan->activity_summary); ?></td>
        </tr>
    </table>

    <!-- Data Table Section -->
    <table>
        <?php
            $totalSubCols = collect($layout)->sum(fn ($g) => count($g['sublabels']) * 3);
            $subColWidth = $totalSubCols > 0 ? round(59.5 / $totalSubCols, 3) : 0;
        ?>
        <colgroup>
            <col style="width:1.5%;"><col style="width:5%;"><col style="width:6%;"><col style="width:6%;"><col style="width:6%;"><col style="width:3%;">
            <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php for($i = 0; $i < count($group['sublabels']) * 3; $i++): ?>
                    <col style="width:<?php echo e($subColWidth); ?>%;">
                <?php endfor; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <col style="width:4%;"><col style="width:4%;"><col style="width:5%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="3">Sl.No</th><th rowspan="3">Category</th><th rowspan="3">Sub Category</th><th rowspan="3">Item Name</th><th rowspan="3">Specification</th><th rowspan="3">Unit</th>
                <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th colspan="<?php echo e(count($group['sublabels']) * 3); ?>"><?php echo e($group['title']); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <th rowspan="3">Already Procured</th>
                <th rowspan="3">Remaining Balance</th>
                <th rowspan="3">Remarks</th>
            </tr>
            <tr>
                <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $group['sublabels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th colspan="3"><?php echo e($sub); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
            <tr>
                <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $__currentLoopData = $group['sublabels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th> Unit</th><th>Rate</th><th>Total</th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php
                $groupSums = [];
                foreach ($layout as $g) {
                    $groupSums[$g['key']] = array_fill(0, count($g['sublabels']), 0.0);
                }
                $alreadyProcuredSum = 0;
                $remainingBalanceSum = 0;
            ?>
            <?php $__currentLoopData = $plan->packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pkg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($pkg->sl_no ?? $loop->iteration); ?></td>
                    <td><?php echo e($pkg->category->name); ?></td>
                    <td><?php echo e($pkg->chartOfAccount->name ?? $pkg->item?->chartOfAccount?->name ?? ''); ?></td>
                    <td><?php echo e($pkg->budgeted_head); ?></td>
                    <td><?php echo e($pkg->specification); ?></td>
                    <td><?php echo e($pkg->unit); ?></td>
                    <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $pkg->alignedValuesFor($group['key'], $group['sublabels']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td><?php echo e($v['no_of_unit'] ? number_format($v['no_of_unit'], 0) : ''); ?></td>
                            <td><?php echo e($v['rate'] ? number_format($v['rate'], 2) : ''); ?></td>
                            <td><?php echo e($v['total'] ? number_format($v['total'], 2) : ''); ?></td>
                            <?php $groupSums[$group['key']][$i] += $v['total'] ?? 0; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td><?php echo e(number_format($pkg->already_procured, 2)); ?></td>
                    <td><?php echo e(number_format($pkg->remaining_balance, 2)); ?></td>
                    <td><?php echo e($pkg->remarks); ?></td>
                </tr>
                <?php
                    $alreadyProcuredSum += $pkg->already_procured;
                    $remainingBalanceSum += $pkg->remaining_balance;
                ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($plan->packages->count()): ?>
                <tr style="font-weight:bold; background:#f0f0f0;">
                    <td colspan="6">Total</td>
                    <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $groupSums[$group['key']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td></td><td></td><td><?php echo e(number_format($sum, 2)); ?></td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <td><?php echo e(number_format($alreadyProcuredSum, 2)); ?></td>
                    <td><?php echo e(number_format($remainingBalanceSum, 2)); ?></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/annual-plan-pdf.blade.php ENDPATH**/ ?>