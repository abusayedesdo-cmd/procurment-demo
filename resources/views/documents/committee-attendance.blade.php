@extends('documents.layout')

@php
    use App\Services\CommitteeDocumentText as Txt;
@endphp

@section('title', 'Attendance ' . $meeting->attendance_number)

@section('content')
    <table class="plain" style="margin-bottom:4px">
        <tr>
            <td style="vertical-align:top">
                <b style="font-size:13px">Eco-Social Development Organization (ESDO)</b><br>
                <i>House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207</i><br>
                <i>Gobindanagar (Collegepara), Thakurgaon-5100</i>
            </td>
        </tr>
    </table>
    <hr class="doc-header-rule">

    <table class="plain">
        <tr>
            <td style="width:50%"><b>Attendence Number:</b> <u>{{ $meeting->attendance_number }}</u></td>
            <td style="width:50%;text-align:right"><b>Attendence Date:</b> <u>{{ optional($meeting->attendance_generated_at)->format('d.m.Y') }}</u></td>
        </tr>
    </table>

    <h1 style="text-decoration:underline">Attendence of Procurement Committee Meeting to {{ Txt::agendaLine($case) }}</h1>

    <h2>Meeting Summary:</h2>
    <table class="plain">
        <tr><td style="width:45%">Name of Project/Program/Department:</td><td><u>{{ Txt::projectName($case) ?? 'N/A' }}</u></td></tr>
        <tr><td>Location of Name of Project/Program/Department:</td><td><u>{{ Txt::projectLocation($case) ?? 'N/A' }}</u></td></tr>
        <tr><td>Subject of Purchase Requisition (PR):</td><td><u>{{ Txt::subCategoryName($case) }} for the {{ Txt::categoryName($case) }}</u></td></tr>
        <tr><td>Total Amount of Purchase Requisition (PR):</td><td><u>{{ number_format(Txt::totalAmount($case), 2) }} Tk</u></td></tr>
    </table>

    <h2>Meeting Agenda:</h2>
    <ol class="terms">
        <li>Regarding the {{ Txt::agendaLine($case) }}.</li>
        <li>Miscellaneous.</li>
    </ol>

    <h2>Attendence of Procurement Committee Meeting:</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:8%">Sl. No.</th>
                <th style="width:28%">Name</th>
                <th style="width:22%">Designation</th>
                <th style="width:22%">Signature</th>
                <th style="width:20%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roster as $i => $member)
                <tr style="height:34px">
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->roleLabel() === 'Convener' ? 'Convener' : ($member->designation ?: $member->roleLabel()) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="sig-block">
        <p>Approved</p>
        <br><br>
        <p>({{ $convener->name ?? '' }})<br>
        Convener,<br>
        Central Procurement Committee, {{ $committeeLocation }}.</p>
    </div>
@endsection
