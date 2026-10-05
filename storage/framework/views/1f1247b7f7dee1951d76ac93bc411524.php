<?php
    use App\Services\CommitteeDocumentText as Txt;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meeting Notice <?php echo e($meeting->notice_number); ?></title>
    <style>
        <?php echo $__env->make('documents._pad-css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; }

        table.plain { width: 100%; border-collapse: collapse; margin: 4px 0; }
        table.plain td { border: none; padding: 1px 0; vertical-align: top; }
        .bold { font-weight: bold; }

        h1.notice-title { font-size: 13px; text-align: center; margin: 12px 0 10px; line-height: 1.4; }

        p { margin: 8px 0; line-height: 1.4; }

        h2.section { font-size: 11.5px; font-weight: bold; margin: 12px 0 4px; }

        ol.agenda { margin: 4px 0 0 18px; padding: 0; }
        ol.agenda li { margin-bottom: 4px; }

        .sig-block { margin-top: 26px; }
        .sig-block p { margin: 2px 0; }

    </style>
</head>
<body>
    <?php echo $__env->make('documents._pad', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="page">
        <table class="plain">
            <tr>
                <td style="width:50%">
                    <span class="bold">Notice Number:</span> <i><?php echo e($meeting->notice_number); ?></i>
                </td>
                <td style="width:50%; text-align: right;">
                    <span class="bold">Notice Date:</span> <i><?php echo e(optional($meeting->notice_date)->format('d F, Y')); ?></i>
                </td>
            </tr>
        </table>

        <h1 class="notice-title">Notice for Procurement Committee Meeting to <i><?php echo e(Txt::agendaLine($case)); ?></i></h1>

        <p>Dear Hon'ble <i><?php echo e($memberDesignation); ?></i> of Procurement Committee,</p>

        <p>Greetings from Central Procurement Committee <?php echo e($committeeLocation); ?>!</p>

        <p>
            Central Procurement Committee <?php echo e($committeeLocation); ?> has requested you to attend the
            <i><?php echo e(Txt::agendaLine($case)); ?></i> for below mentioned Purchase Requisition (PR):
            <?php if($case->purchaseRequisition?->attachment_path): ?>
                (<i>Details PR as PDF Linked</i>)
            <?php endif; ?>
        </p>

        <h2 class="section">Summary of Purchase Requisition (PR):</h2>
        <table class="plain">
            <tr><td style="width:46%">Name of Project/Program/Department:</td><td><i><?php echo e(Txt::projectName($case) ?? 'N/A'); ?></i></td></tr>
            <tr><td>Location of Name of Project/Program/Department:</td><td><i><?php echo e(Txt::projectLocation($case) ?? 'N/A'); ?></i></td></tr>
            <tr><td>Subject of Purchase Requisition (PR):</td><td><i><?php echo e(Txt::subCategoryName($case)); ?> for the <?php echo e(Txt::categoryName($case)); ?></i></td></tr>
            <tr><td>Total Amount of Purchase Requisition (PR):</td><td><i><?php echo e(number_format(Txt::totalAmount($case), 2)); ?> Tk</i></td></tr>
        </table>

        <p>
            <span class="bold">Meeting Date &amp; Time:</span>
            <i><?php echo e($meeting->meeting_date->format('d F, Y')); ?><?php echo e($meeting->meeting_time ? ', '.$meeting->meeting_time : ''); ?></i>
        </p>

        <h2 class="section">Meeting Agenda:</h2>
        <ol class="agenda">
            <?php $__currentLoopData = Txt::agendaItems($case, $meeting->agenda); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($item); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ol>

        <p style="margin-top:24px;">With Thanks</p>

        <div class="sig-block">
            <?php if($signerName): ?><p class="bold">(<?php echo e($signerName); ?>)</p><?php endif; ?>
            <?php if($signerDesignation): ?><p><?php echo e($signerDesignation); ?>,</p><?php endif; ?>
            <p>Central Procurement Committee, <?php echo e($committeeLocation); ?>.</p>
        </div>
    </div>

</body>
</html><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/meeting-notice.blade.php ENDPATH**/ ?>