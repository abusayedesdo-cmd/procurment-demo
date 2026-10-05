<?php
    use App\Services\CommitteeDocumentText as Txt;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meeting Attendance <?php echo e($meeting->attendance_number); ?></title>
    <style>
        <?php echo $__env->make('documents._pad-css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; line-height: 1.2; }

        table.plain { width: 100%; border-collapse: collapse; margin: 2px 0; }
        table.plain td { border: none; padding: 1px 0; vertical-align: top; }
        .bold { font-weight: bold; }

        h1.doc-title { font-size: 11.5px; text-align: center; margin: 6px 0 6px; line-height: 1.3; }

        h2.section { font-size: 10.5px; font-weight: bold; margin: 6px 0 2px; }

        ol.agenda { margin: 2px 0 0 16px; padding: 0; }
        ol.agenda li { margin-bottom: 2px; }

        table.attend { width: 100%; border-collapse: collapse; margin: 4px 0; }
        table.attend th, table.attend td { border: 1px solid #333; padding: 4px 6px; font-size: 9.5px; vertical-align: middle; }
        table.attend th { background: #eee; text-align: left; }
        table.attend td.row-num { text-align: center; }

        .sig-block { margin-top: 14px; font-size: 9.5px; page-break-inside: avoid; }
        .sig-block p { margin: 1px 0; }
    </style>
</head>
<body>
    <?php echo $__env->make('documents._pad', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <table class="plain">
        <tr>
            <td style="width:70%">
                <span class="bold">Attendance Number:</span> <i><?php echo e($meeting->attendance_number); ?></i>
            </td>
            <td style="width:30%; text-align: right;">
                <span class="bold">Attendance Date:</span> <i><?php echo e($meeting->meeting_date->format('d F, Y')); ?></i>
            </td>
        </tr>
    </table>

    <h1 class="doc-title">Attendance of Procurement Committee Meeting to <i><?php echo e(Txt::agendaLine($case)); ?></i></h1>

    <h2 class="section">Meeting Summary:</h2>
    <table class="plain">
        <tr><td style="width:46%">Name of Project/Program/Department:</td><td><i><?php echo e(Txt::projectName($case) ?? 'N/A'); ?></i></td></tr>
        <tr><td>Location of Name of Project/Program/Department:</td><td><i><?php echo e(Txt::projectLocation($case) ?? 'N/A'); ?></i></td></tr>
        <tr><td>Subject of Purchase Requisition (PR):</td><td><i><?php echo e(Txt::subCategoryName($case)); ?> for the <?php echo e(Txt::categoryName($case)); ?></i></td></tr>
        <tr><td>Total Amount of Purchase Requisition (PR):</td><td><i><?php echo e(number_format(Txt::totalAmount($case), 2)); ?> Tk</i></td></tr>
    </table>

    <h2 class="section">Meeting Agenda:</h2>
    <ol class="agenda">
        <?php $__currentLoopData = Txt::agendaItems($case, $meeting->agenda); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($item); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>

    <h2 class="section">Attendance of Procurement Committee Meeting:</h2>
    <table class="attend">
        <thead>
            <tr>
                <th style="width:8%">Sl. No.</th>
                <th style="width:30%">Name</th>
                <th style="width:24%">Designation</th>
                <th style="width:20%">Signature</th>
                <th style="width:18%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $meeting->attendees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $attendee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr style="height:22px">
                    <td class="row-num"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                    <td><?php echo e($attendee->name); ?></td>
                    <td><?php echo e($attendee->designation); ?></td>
                    <td>&nbsp;</td>
                    <td><?php echo e($attendee->remarks); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php for($i = 0; $i < 4; $i++): ?>
                    <tr style="height:22px">
                        <td class="row-num"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="sig-block">
        <p>Approved</p>
        <?php if($signerName): ?><p class="bold" style="margin-top:12px">(<?php echo e($signerName); ?>)</p><?php endif; ?>
        <?php if($signerDesignation): ?><p><?php echo e($signerDesignation); ?>,</p><?php endif; ?>
        <p>Central Procurement Committee, <?php echo e($committeeLocation); ?>.</p>
    </div>
</body>
</html>
<?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/meeting-attendance.blade.php ENDPATH**/ ?>