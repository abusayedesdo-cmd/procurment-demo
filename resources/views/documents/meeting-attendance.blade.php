@extends('documents.layout')

@section('content')
@php
    use App\Services\CommitteeDocumentText as Txt;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Meeting Attendance {{ $meeting->attendance_number }}</title>
    <style>
        @page { margin: 15px 25px 15px 25px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; margin: 0; line-height: 1.2; }

        .letterhead { width: 100%; position: relative; min-height: 42px; margin-bottom: 2px; }
        .letterhead img { position: absolute; left: 0; top: 0px; height: 40px; width: auto; }
        .org-name { margin-left: 50px; font-size: 15px; font-weight: bold; color: #111; line-height: 1.1; padding-top: 2px; }

        .address { text-align: center; font-size: 11px; font-weight: bold; color: #1f4e9c; line-height: 1.2; margin: 2px 0 6px; }

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
            <td style="width:70%">
                <span class="bold">Attendance Number:</span> <i>{{ $meeting->attendance_number }}</i>
            </td>
            <td style="width:30%; text-align: right;">
                <span class="bold">Attendance Date:</span> <i>{{ $meeting->meeting_date->format('d F, Y') }}</i>
            </td>
        </tr>
    </table>

    <h1 class="doc-title">Attendance of Procurement Committee Meeting to <i>{{ Txt::agendaLine($case) }}</i></h1>

    <h2 class="section">Meeting Summary:</h2>
    <table class="plain">
        <tr><td style="width:46%">Name of Project/Program/Department:</td><td><i>{{ Txt::projectName($case) ?? 'N/A' }}</i></td></tr>
        <tr><td>Location of Name of Project/Program/Department:</td><td><i>{{ Txt::projectLocation($case) ?? 'N/A' }}</i></td></tr>
        <tr><td>Subject of Purchase Requisition (PR):</td><td><i>{{ Txt::subCategoryName($case) }} for the {{ Txt::categoryName($case) }}</i></td></tr>
        <tr><td>Total Amount of Purchase Requisition (PR):</td><td><i>{{ number_format(Txt::totalAmount($case), 2) }} Tk</i></td></tr>
    </table>

    <h2 class="section">Meeting Agenda:</h2>
    <ol class="agenda">
        <li>Regarding the <i>{{ Txt::verb($case) }} {{ Txt::subCategoryName($case) }}</i> for the <i>{{ Txt::categoryName($case) }}</i>.</li>
        <li>Miscellaneous.</li>
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
            @forelse ($roster as $i => $member)
                <tr style="height:22px">
                    <td class="row-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->roleLabel() === 'Convener' ? 'Convener' : ($member->designation ?: $member->roleLabel()) }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @empty
                @for ($i = 0; $i < 4; $i++)
                    <tr style="height:22px">
                        <td class="row-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    <div class="sig-block">
        <p>Approved</p>
        <!-- <p style="margin-top:12px">({{ $convener->name ?? '[Convener Name]' }})</p> -->
        <p>Convener,</p>
        <p>Central Procurement Committee, {{ $committeeLocation }}.</p>
    </div>
</body>
</html>
@endsection