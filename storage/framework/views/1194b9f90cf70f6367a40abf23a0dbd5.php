<?php
    use App\Services\CommitteeDocumentText as Txt;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rezulation/Minutes <?php echo e($meeting->rezulation_no); ?></title>
    <style>
        <?php echo $__env->make('documents._pad-css', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; }

        table.plain { width: 100%; border-collapse: collapse; margin: 4px 0; }
        table.plain td { border: none; padding: 1px 0; vertical-align: top; }
        .bold { font-weight: bold; }

        p { margin: 8px 0; line-height: 1.45; }

        h2.section { font-size: 11.5px; font-weight: bold; text-decoration: underline; margin: 14px 0 6px; }

        ol.agenda, ol.decisions { margin: 4px 0 0 18px; padding: 0; }
        ol.agenda li, ol.decisions li { margin-bottom: 6px; }
        ol.decisions > li > .bold { display: block; margin-bottom: 2px; }

        ul.schedule { margin: 6px 0 6px 18px; padding: 0; list-style: none; }
        ul.schedule li { margin-bottom: 5px; }
        ul.schedule li:before { content: "o  "; }

        table.attend { width: 100%; border-collapse: collapse; margin: 6px 0 10px; }
        table.attend th, table.attend td { border: 1px solid #333; padding: 5px 8px; font-size: 10.5px; vertical-align: top; }
        table.attend th { background: #eee; text-align: left; }
        table.attend td.row-num { text-align: center; }

        .sig-block { margin-top: 26px; }
        .sig-block p { margin: 2px 0; }

    </style>
</head>
<body>
    <?php echo $__env->make('documents._pad', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <p><span class="bold">Rezulation/Minutes Number:</span> <i><?php echo e($meeting->rezulation_no); ?></i></p>

    <table class="plain">
        <tr>
            <td style="width:50%"><span class="bold">Meeting Location:</span> <i><?php echo e($meeting->location ?: 'N/A'); ?></i></td>
            <td style="width:50%">
                <span class="bold">Meeting Date &amp; Time:</span>
                <i><?php echo e($meeting->meeting_date->format('d F, Y')); ?><?php echo e($meeting->meeting_time ? ', '.$meeting->meeting_time : ''); ?></i>
            </td>
        </tr>
    </table>

    <p>
        The meeting was led by <i><?php echo e($signerName ?: ($convener->name ?? '[Convener Name]')); ?></i>, Convener of the
        Central Procurement Committee, <?php echo e($committeeLocation); ?>. At the beginning, he welcomed all the
        members and thanked them for joining. After that, he started the meeting officially.
    </p>

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
                <tr style="height:30px">
                    <td class="row-num"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                    <td><?php echo e($attendee->name); ?></td>
                    <td><?php echo e($attendee->designation); ?></td>
                    <td>&nbsp;</td>
                    <td><?php echo e($attendee->remarks); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <?php for($i = 0; $i < 3; $i++): ?>
                    <tr style="height:30px">
                        <td class="row-num"><?php echo e(str_pad($i + 1, 2, '0', STR_PAD_LEFT)); ?></td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                <?php endfor; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2 class="section">Meeting Agenda:</h2>
    <ol class="agenda">
        <li>Reading and approval of the minutes of the previous meeting.</li>
        <?php $__currentLoopData = Txt::agendaItems($case, $meeting->agenda); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($item); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ol>

    <h2 class="section">Decisions of Today's Meeting:</h2>
    <ol class="decisions">
        <li>
            <span class="bold">Reading and approval of the minutes of the previous meeting:</span>
            The Member Secretary of the Central Procurement Committee, <?php echo e($committeeLocation); ?>,
            <i><?php echo e($memberSecretaryName); ?></i>, read out the rezulation/minutes of the previous meeting.
            After review, the convener approved the rezulation/minutes without any amendment, addition, or deletion.
        </li>
        <li>
            <span class="bold">Regarding the <?php echo e(Txt::verb($case)); ?> <?php echo e(Txt::subCategoryName($case)); ?> for the <?php echo e(Txt::categoryName($case)); ?>:</span>
            <?php echo e($meeting->decisions ?: 'A requisition was submitted to the Central Procurement Committee, ' . $committeeLocation . ' for the ' . Txt::verb($case) . ' ' . Txt::subCategoryName($case) . ' for the ' . Txt::categoryName($case) . ' under the ' . (Txt::projectName($case) ?? 'N/A') . ' Project.'); ?>

            <br><br>

            <?php if($meeting->meeting_type === 'first'): ?>
                After discussion, all committee members agreed, and the Committee confirmed the following tender schedule:
                <ul class="schedule">
                    <li><span class="bold">Tender/RFQ/Sole Sourcing/Framework Agreement Vendor Publication Date:</span> <i><?php echo e(optional($meeting->publish_date)->format('d F, Y') ?: 'N/A'); ?></i></li>
                    <?php if($meeting->schedule_override_reason): ?>
                        <li><span class="bold">Special Note:</span> <i><?php echo e($meeting->schedule_override_reason); ?></i></li>
                    <?php endif; ?>
                    <li><span class="bold">Tender Submission Deadline:</span> <i><?php echo e(optional($meeting->closing_date)->format('d F, Y') ?: 'N/A'); ?></i></li>
                    <li><span class="bold">Tender Opening Time:</span> <i><?php echo e(optional($meeting->opening_date)->format('d F, Y') ?: 'N/A'); ?></i></li>
                </ul>
                <p>
                    The Committee has agreed that the procurement process will be carried out by selecting the
                    qualified bidder after comparing at least three eligible bids after proper technical and
                    financial evaluation as per the procurement policy of ESDO. The Committee will conduct
                    interviews with the initially selected vendors as required to verify their qualifications,
                    technical expertise and relevant experience.
                </p>
            <?php else: ?>
                After reviewing the Comparative Statement of Bids, the Committee discussed the technical and
                financial evaluation of the bidders and agreed on the recommended vendor for award, subject to
                final approval.
            <?php endif; ?>
        </li>
        <li>
            <span class="bold">Miscellaneous:</span>
            As there were no further issues for discussion, the Central Procurement Committee,
            <?php echo e($committeeLocation); ?> thanked all members for their active participation and declared the
            meeting adjourned.
        </li>
    </ol>

    <div class="sig-block">
        <p>Approved</p>
        <?php if($signerName): ?><p class="bold" style="margin-top:24px">(<?php echo e($signerName); ?>)</p><?php endif; ?>
        <?php if($signerDesignation): ?><p><?php echo e($signerDesignation); ?>,</p><?php endif; ?>
        <p>Central Procurement Committee, <?php echo e($committeeLocation); ?>.</p>
    </div>
</body>
</html><?php /**PATH D:\New Poject\Project_procrument\resources\views/documents/meeting-minutes.blade.php ENDPATH**/ ?>