@php
    use App\Services\CommitteeDocumentText as Txt;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meeting Notice {{ $meeting->notice_number }}</title>
    <style>
        @page { margin: 20px 28px 65px 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 0; }

        /* Left rail: rotated tagline + vertical rule, running the height of the page */
        .rail {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 30px;
            border-right: 1px solid #555;
        }
        .rail-text {
            position: absolute;
            top: 50%; left: 50%;
            width: 420px;
            margin-left: -210px;
            margin-top: -8px;
            transform: rotate(-90deg);
            font-size: 8.5px;
            font-style: italic;
            font-weight: bold;
            color: #222;
            text-align: center;
        }

        .page { margin-left: 42px; padding-right: 4px; }

        .letterhead { width: 100%; position: relative; min-height: 54px; margin-bottom: 6px; }
        .letterhead img { position: absolute; left: 0; top: 2px; height: 48px; width: auto; }
        .org-name { margin-left: 60px; font-size: 17px; font-weight: bold; color: #111; line-height: 1.25; padding-top: 4px; }

        .address { text-align: center; font-size: 12.5px; font-weight: bold; color: #1f4e9c; line-height: 1.35; margin: 2px 0 8px; }

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

        .doc-footer {
            position: fixed;
            bottom: -58px; left: 42px; right: 4px;
            border-top: 1px solid #333;
            padding-top: 4px;
            text-align: center;
            font-size: 8.5px;
            color: #111;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="rail">
        <div class="rail-text">We seek an equitable society free from all discriminations</div>
    </div>

    <div class="page">
        <div class="letterhead">
            @if (file_exists(public_path('img/esdo-logo.png')))
                <img src="{{ public_path('img/esdo-logo.png') }}">
            @endif
            <div class="org-name">Eco-Social Development Organization (ESDO)</div>
        </div>

        <div class="address">
            House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207<br>
            Gobindanagar (Collegepara), Thakurgaon-5100
        </div>

        <table class="plain">
            <tr>
                <td style="width:50%">
                    <span class="bold">Notice Number:</span> <i>{{ $meeting->notice_number }}</i>
                </td>
                <td style="width:50%; text-align: right;">
                    <span class="bold">Notice Date:</span> <i>{{ optional($meeting->notice_date)->format('d F, Y') }}</i>
                </td>
            </tr>
        </table>

        <h1 class="notice-title">Notice for Procurement Committee Meeting to <i>{{ Txt::agendaLine($case) }}</i></h1>

        <p>Dear Hon'ble <i>{{ $memberDesignation }}</i> of Procurement Committee,</p>

        <p>Greetings from Central Procurement Committee {{ $committeeLocation }}!</p>

        <p>
            Central Procurement Committee {{ $committeeLocation }} has requested you to attend the
            <i>{{ Txt::agendaLine($case) }}</i> for below mentioned Purchase Requisition (PR):
            @if ($case->purchaseRequisition?->attachment_path)
                (<i>Details PR as PDF Linked</i>)
            @endif
        </p>

        <h2 class="section">Summary of Purchase Requisition (PR):</h2>
        <table class="plain">
            <tr><td style="width:46%">Name of Project/Program/Department:</td><td><i>{{ Txt::projectName($case) ?? 'N/A' }}</i></td></tr>
            <tr><td>Location of Name of Project/Program/Department:</td><td><i>{{ Txt::projectLocation($case) ?? 'N/A' }}</i></td></tr>
            <tr><td>Subject of Purchase Requisition (PR):</td><td><i>{{ Txt::subCategoryName($case) }} for the {{ Txt::categoryName($case) }}</i></td></tr>
            <tr><td>Total Amount of Purchase Requisition (PR):</td><td><i>{{ number_format(Txt::totalAmount($case), 2) }} Tk</i></td></tr>
        </table>

        <p>
            <span class="bold">Meeting Date &amp; Time:</span>
            <i>{{ $meeting->meeting_date->format('d F, Y') }}{{ $meeting->meeting_time ? ', '.$meeting->meeting_time : '' }}</i>
        </p>

        <h2 class="section">Meeting Agenda:</h2>
        <ol class="agenda">
            <li>Regarding the <i>{{ Txt::verb($case) }} {{ Txt::subCategoryName($case) }}</i> for the <i>{{ Txt::categoryName($case) }}</i>.</li>
            <li>Miscellaneous.</li>
        </ol>

        <p style="margin-top:24px;">With Thanks</p>

        <div class="sig-block">
            <!-- <p>({{ $convener->name ?? '[Convener Name]' }})</p> -->
            <p>Convener,</p>
            <p>Central Procurement Committee, {{ $committeeLocation }}.</p>
        </div>
    </div>

    <div class="doc-footer">
        <div class="bold">Dhaka Office: ESDO House: House # 748, Road No: 08, Baitul Aman Housing Society, Adabar, Dhaka-1207, Bangladesh, Phone No: +88-02-58154857,<br>
        Contact No: 01713149259, Email: esdobangladesh@hotmail.com, Web: www.esdo.net.bd</div>
        <div class="bold">Head Office: Collegepara, Thakurgaon-5100, Tel: 0561-52149, 0561-61614 Mobile: 0174-063360 Fax: 0561-61599, E-mail: esdobangladesh@hotmail.com, web: www.esdo.net.bd</div>
        <div>Registration No: DSS: Thakur-440/88, NGO Bureau-694/93 (Renewed 2018), MRA 0000204</div>
    </div>
</body>
</html>