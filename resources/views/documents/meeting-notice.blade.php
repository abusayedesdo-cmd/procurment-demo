@extends('documents.layout')

@section('title', 'Meeting Notice ' . $meeting->notice_number)

@section('content')
    <div class="center">
        <p class="bold" style="font-size:13px;margin-bottom:0">Eco-Social Development Organization (ESDO)</p>
        <p class="muted" style="margin-top:0">Adabor, Dhaka-1207.</p>
    </div>

    <div class="meta" style="margin-top:14px">
        <p><span class="bold">Notice No:</span> {{ $meeting->notice_number }}</p>
        <p><span class="bold">Notice Date:</span> {{ optional($meeting->notice_date)->format('d F, Y') }}</p>
    </div>

    <h1>MEETING NOTICE</h1>
    <p class="center">Central Procurement Committee</p>

    <p style="margin-top:14px">
        A meeting of the Central Procurement Committee will be held for the below-mentioned Purchase Requisition (PR),
        as follows:
    </p>

    <table class="bordered">
        <tr><td class="bold" style="width:32%">Procurement Reference</td><td>{{ $case->ref }}</td></tr>
        <tr><td class="bold">Project</td><td>{{ $case->purchaseRequisition->project_name ?? '—' }}</td></tr>
        <tr><td class="bold">Delivery Location</td><td>{{ $case->purchaseRequisition->delivery_location ?? '—' }}</td></tr>
        <tr><td class="bold">Subject</td><td>{{ $case->title }}</td></tr>
        <tr><td class="bold">Category</td><td>{{ $case->category }}</td></tr>
        <tr><td class="bold">Estimated Amount</td><td>৳ {{ number_format($case->amount, 2) }}</td></tr>
    </table>

    <table class="bordered" style="margin-top:14px">
        <tr><td class="bold" style="width:32%">Meeting Date</td><td>{{ $meeting->meeting_date->format('d F, Y') }}</td></tr>
        <tr><td class="bold">Time</td><td>{{ $meeting->meeting_time ?? '—' }}</td></tr>
        <tr><td class="bold">Venue</td><td>{{ $meeting->location }}</td></tr>
    </table>

    <h2>Meeting Agenda</h2>
    <ol class="terms">
        <li>{{ $meeting->agenda ?: 'Discussion and decision on Procurement Reference ' . $case->ref . ' — ' . $case->title }}</li>
        <li>Miscellaneous.</li>
    </ol>

    <p style="margin-top:16px">All concerned committee members are requested to attend the meeting at the scheduled time.</p>

    <div class="sig-block">
        <p>({{ $convener->name ?? '[Convener Name]' }})</p>
        <p class="bold">Convener, Central Procurement Committee, ESDO.</p>
    </div>
@endsection
