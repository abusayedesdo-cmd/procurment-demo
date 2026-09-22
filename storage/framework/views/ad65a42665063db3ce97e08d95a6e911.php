<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 14px; color: #111; line-height: 1.5; }
        .box { max-width: 560px; margin: 0 auto; padding: 24px; }
        .header { font-size: 16px; font-weight: bold; margin-bottom: 4px; }
        .sub { color: #555; font-size: 12px; margin-bottom: 20px; }
        table.meta { width: 100%; border-collapse: collapse; margin: 14px 0; }
        table.meta td { padding: 5px 0; vertical-align: top; }
        table.meta td.label { color: #555; width: 130px; }
        .agenda-box { background: #F7F8FC; border-radius: 8px; padding: 12px 14px; margin-top: 16px; white-space: pre-wrap; }
        .footer { margin-top: 24px; font-size: 11.5px; color: #777; }
    </style>
</head>
<body>
    <div class="box">
        <div class="header">Eco-Social Development Organization (ESDO)</div>
        <div class="sub">Procurement Management System</div>

        <p>Dear <?php echo e($recipientName); ?>,</p>

        <p>
            You are hereby notified of the following
            <strong><?php echo e(ucfirst($meeting->meeting_type)); ?> Meeting</strong>
            of the Central Procurement Committee.
        </p>

        <table class="meta">
            <tr><td class="label">Case Reference</td><td><?php echo e($meeting->procurementCase->ref ?? '—'); ?></td></tr>
            <tr><td class="label">Rezulation No.</td><td><?php echo e($meeting->rezulation_no ?? '—'); ?></td></tr>
            <tr><td class="label">Date</td><td><?php echo e(optional($meeting->meeting_date)->format('d F, Y')); ?></td></tr>
            <tr><td class="label">Time</td><td><?php echo e($meeting->meeting_time ?? '—'); ?></td></tr>
            <tr><td class="label">Location</td><td><?php echo e($meeting->location); ?></td></tr>
        </table>

        <div class="agenda-box">
            <strong>Agenda</strong><br>
            <?php echo e($meeting->agenda); ?>

        </div>

        <p style="margin-top:20px;">Please make yourself available at the above date, time, and location.</p>

        <p>Thank you.</p>

        <div class="footer">
            This is a system-generated notice from the ESDO Procurement Management System.
        </div>
    </div>
</body>
</html><?php /**PATH D:\New Poject\Project_procrument\resources\views/emails/meeting-notice.blade.php ENDPATH**/ ?>