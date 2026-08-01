<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: sans-serif; font-size: 8px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #333; padding: 2px; text-align: center; }
    h2, p { text-align: center; margin: 2px 0; }
</style>
</head>
<body>
    <!-- <h2><?php echo e($plan->title); ?></h2>
    <p><strong>Project Name/Title:</strong> <?php echo e($plan->project_name ?? $plan->title); ?></p>
    <p><strong>Project Location (Office):</strong> <?php echo e($plan->project_location); ?> </p> 
    <p><strong>Project Working Area:</strong> <?php echo e($plan->working_area); ?></p>
    <p><strong>Project Duration:</strong> <?php echo e($plan->project_duration ?? (optional($plan->fiscal_year_start)->format('d M Y') . ' to ' . optional($plan->fiscal_year_end)->format('d M Y'))); ?> </p>
    <p><strong>Date of Agreement/Awarded:</strong> <?php echo e(optional($plan->agreement_date)->format('d M Y')); ?></p>
    <p><strong>Donor Name:</strong> <?php echo e($plan->donor_name); ?></p>
    <p><strong>Activity Summary:</strong> <?php echo e($plan->activity_summary); ?></p>
    <p style="margin-bottom:10px;"></p> -->

    <h2><?php echo e($plan->title); ?></h2>
    <p style="text-align:center;"><strong>Project Name/Title:</strong> <?php echo e($plan->project_name ?? $plan->title); ?></p>
    <p style="text-align:center;"><strong>Project Location (Office):</strong> <?php echo e($plan->project_location); ?> </p> 
    <p style="text-align:center;"><strong>Project Working Area:</strong> <?php echo e($plan->working_area); ?></p>
    <p style="text-align:center;"><strong>Project Duration:</strong> <?php echo e($plan->project_duration ?? (optional($plan->fiscal_year_start)->format('d M Y') . ' to ' . optional($plan->fiscal_year_end)->format('d M Y'))); ?> </p>
    <p style="text-align:center;"><strong>Date of Agreement/Awarded:</strong> <?php echo e(optional($plan->agreement_date)->format('d M Y')); ?></p>
    <p style="text-align:center;"><strong>Donor Name:</strong> <?php echo e($plan->donor_name); ?></p>
    <p style="text-align:center;"><strong>Activity Summary:</strong> <?php echo e($plan->activity_summary); ?></p>
    <p style="margin-bottom:10px;"></p>

 <table>
        <thead>
            <tr>
                <th rowspan="3">Sl.No</th><th rowspan="3">Category</th><th rowspan="3">Budgeted Head</th><th rowspan="3">Specification</th><th rowspan="3">Unit</th>
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
                        <th>No. of Unit</th><th>Rate</th><th>Total</th>
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
                    <td><?php echo e($pkg->sl_no); ?></td>
                    <td><?php echo e($pkg->category->name); ?></td>
                    <td><?php echo e($pkg->budgeted_head); ?></td>
                    <td><?php echo e($pkg->specification); ?></td>
                    <td><?php echo e($pkg->unit); ?></td>
                    <?php $__currentLoopData = $layout; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $__currentLoopData = $pkg->alignedValuesFor($group['key'], $group['sublabels']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <td><?php echo e($v['no_of_unit'] !== null ? number_format($v['no_of_unit'], 2) : '-'); ?></td>
                            <td><?php echo e($v['rate'] !== null ? number_format($v['rate'], 2) : '-'); ?></td>
                            <td><?php echo e($v['total'] !== null ? number_format($v['total'], 2) : '-'); ?></td>
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
                    <td colspan="5">Total</td>
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