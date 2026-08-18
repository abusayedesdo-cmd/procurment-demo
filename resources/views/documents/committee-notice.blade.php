@extends('documents.layout')

@php
    use App\Services\CommitteeDocumentText as Txt;
@endphp

@section('title', 'Notice ' . $meeting->notice_number)

@section('content')
    <table class="plain" style="margin-bottom:4px">
        <tr>
            <td style="width:60%;vertical-align:top">
                <b style="font-size:13px">Eco-Social Development Organization (ESDO)</b><br>
                <i>House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207</i><br>
                <i>Gobindanagar (Collegepara), Thakurgaon-5100</i>
            </td>
        </tr>
    </table>
    <hr class="doc-header-rule">

    <table class="plain">
        <tr>
            <td style="width:50%"><b>Notice Number:</b> <u>{{ $meeting->notice_number }}</u></td>
            <td style="width:50%;text-align:right"><b>Notice Date:</b> <u>{{ optional($meeting->notice_generated_at)->format('d.m.Y') }}</u></td>
        </tr>
    </table>

    <h1 style="text-decoration:underline">Notice for Procurement Committee Meeting to {{ Txt::agendaLine($case) }}</h1>

    <p>Dear Hon'ble <u>{{ $memberDesignation }}</u> of Procurement Committee,</p>
    <p>Greetings from <u>Central Procurement Committee {{ $committeeLocation }}!</u></p>
    <p>
        Central Procurement Committee {{ $committeeLocation }} has requested you to attend the
        <u>{{ Txt::agendaLine($case) }}</u> for below mentioned Purchase Requisition (PR):
        @if ($case->purchaseRequisition?->attachment_path)
            (<u>Details PR as PDF Linked</u>)
        @endif
    </p>

    <h2>Summary of Purchase Requisition (PR):</h2>
    <table class="plain">
        <tr><td style="width:45%">Name of Project/Program/Department:</td><td><u>{{ Txt::projectName($case) ?? 'N/A' }}</u></td></tr>
        <tr><td>Location of Name of Project/Program/Department:</td><td><u>{{ Txt::projectLocation($case) ?? 'N/A' }}</u></td></tr>
        <tr><td>Subject of Purchase Requisition (PR):</td><td><u>{{ Txt::subCategoryName($case) }} for the {{ Txt::categoryName($case) }}</u></td></tr>
        <tr><td>Total Amount of Purchase Requisition (PR):</td><td><u>{{ number_format(Txt::totalAmount($case), 2) }} Tk</u></td></tr>
    </table>

    <table class="plain">
        <tr><td><b>Meeting Date &amp; Time:</b> <u>{{ $meeting->meeting_date->format('d F, Y') }}{{ $meeting->meeting_time ? ', '.$meeting->meeting_time : '' }}</u></td></tr>
    </table>

    <h2>Meeting Agenda:</h2>
    <ol class="terms">
        <li>Regarding the {{ Txt::agendaLine($case) }}.</li>
        <li>Miscellaneous.</li>
    </ol>

    <div class="sig-block">
        <p>With Thanks</p>
        <br><br>
        <p>({{ $convener->name ?? '' }})<br>
        Convener,<br>
        Central Procurement Committee, {{ $committeeLocation }}.</p>
    </div>
@endsection
