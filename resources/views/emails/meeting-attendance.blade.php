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
        .footer { margin-top: 24px; font-size: 11.5px; color: #777; }
    </style>
</head>
<body>
    <div class="box">
        <div class="header">Eco-Social Development Organization (ESDO)</div>
        <div class="sub">Procurement Management System</div>

        <p>Dear {{ $recipientName }},</p>

        <p>
            Your attendance has been recorded for the following
            <strong>{{ ucfirst($meeting->meeting_type) }} Meeting</strong>
            of the Central Procurement Committee.
        </p>

        <table class="meta">
            <tr><td class="label">Case Reference</td><td>{{ $meeting->procurementCase->ref ?? '—' }}</td></tr>
            @if (!empty($prNumber))
                <tr><td class="label">PR No.</td><td>{{ $prNumber }}</td></tr>
            @endif
            <tr><td class="label">Attendance No.</td><td>{{ $meeting->attendance_number ?? '—' }}</td></tr>
            <tr><td class="label">Date</td><td>{{ optional($meeting->meeting_date)->format('d F, Y') }}</td></tr>
            <tr><td class="label">Designation (this meeting)</td><td>{{ $designation }}</td></tr>
            @if (!empty($authorName))
                <tr>
                    <td class="label">Sent by</td>
                    <td>{{ $authorName }}@if (!empty($authorDesignation)), {{ $authorDesignation }}@endif</td>
                </tr>
            @endif
        </table>

        @if (!empty($hasPrAttachment))
            <p>
                The Purchase Requisition (<strong>{{ $prNumber }}</strong>) for this meeting is attached to this
                email as a PDF — open the attachment to view or download it.
            </p>
        @endif

        <p>Thank you for attending.</p>

        @if (!empty($authorName))
            <p style="margin-top:18px">
                Regards,<br>
                <strong>{{ $authorName }}</strong>
                @if (!empty($authorDesignation))<br>{{ $authorDesignation }}@endif
                <br>Central Procurement Committee, ESDO
            </p>
        @endif

        <div class="footer">
            This is a system-generated confirmation from the ESDO Procurement Management System.
        </div>
    </div>
</body>
</html>