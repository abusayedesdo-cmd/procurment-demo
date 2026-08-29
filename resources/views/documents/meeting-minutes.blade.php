@extends('documents.layout')

@section('title', 'Rezulation-Minutes -' . $meeting->rezulation_no)

@section('content')
    <div class="center">
        <p class="bold" style="font-size:13px;margin-bottom:0">Eco-Social Development Organization (ESDO)</p>
        <p class="muted" style="margin-top:0">Adabor, Dhaka-1207.</p>
    </div>

    <div class="meta" style="margin-top:14px">
        <p><span class="bold">Rezulation/Minutes No:</span> -{{ $meeting->rezulation_no }}</p>
        <p><span class="bold">{{ $meeting->typeLabel() }}</span></p>
    </div>

    <h1>MINUTES OF THE MEETING</h1>
    <p class="center">Central Procurement Committee</p>

    <table class="bordered">
        <tr><td class="bold" style="width:32%">Venue</td><td>{{ $meeting->location }}</td></tr>
        <tr><td class="bold">Date</td><td>{{ $meeting->meeting_date->format('d F, Y') }}@if ($meeting->meeting_time), Time: {{ $meeting->meeting_time }}@endif</td></tr>
        <tr><td class="bold">Procurement Reference</td><td>{{ $case->ref }} — {{ $case->title }}</td></tr>
    </table>

    <p style="margin-top:14px">
        The meeting was led by {{ $convener->name ?? 'the Convener' }}, Convener of the ESDO Central Procurement
        Committee. At the beginning, he welcomed all the members and thanked them for joining, after which the
        meeting officially began.
    </p>

    <h2>Attendance of Procurement Committee Meeting</h2>
    <table class="bordered">
        <thead><tr><th style="width:8%">SL</th><th style="width:40%">Name</th><th>Designation</th></tr></thead>
        <tbody>
            @foreach ($meeting->attendees as $i => $a)
                <tr><td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td><td>{{ $a->name }}</td><td>{{ $a->designation }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h2>Meeting Agenda</h2>
    <ol class="terms">
        <li>Reading and approval of the minutes of the previous meeting.</li>
        <li>{{ $meeting->agenda ?: 'Discussion and decision on Procurement Reference ' . $case->ref }}</li>
        <li>Miscellaneous.</li>
    </ol>

    <h2>Decisions of Today's Meeting</h2>
    <ol class="terms">
        <li>The Member Secretary read out the minutes of the previous meeting, which were approved by the committee without any amendment.</li>
        <li>
            @if ($meeting->meeting_type === 'first')
                After discussion, the committee approved the following tender schedule for {{ $case->ref }}:
                <ul style="margin:6px 0 0 18px">
                    <li>Tender Publication Date: {{ optional($meeting->publish_date)->format('d F Y') ?? '[TBD]' }}</li>
                    <li>Tender Submission Deadline: {{ optional($meeting->closing_date)->format('d F Y') ?? '[TBD]' }}</li>
                    <li>Tender Opening Date: {{ optional($meeting->opening_date)->format('d F Y') ?? '[TBD]' }}</li>
                </ul>
                @if ($meeting->schedule_override_reason)
                    <p class="muted" style="margin-top:6px">Note: {{ $meeting->schedule_override_reason }}</p>
                @endif
            @else
                After evaluation, the committee approved the following award(s) for {{ $case->ref }}:
                <table class="bordered" style="margin-top:8px">
                    <thead><tr><th>Vendor</th><th>Scope / Lot</th><th style="width:22%">Awarded Amount</th></tr></thead>
                    <tbody>
                        @foreach ($meeting->awards as $a)
                            <tr><td>{{ $a->vendor_name }}</td><td>{{ $a->scope_note }}</td><td>৳ {{ number_format($a->amount, 2) }}</td></tr>
                        @endforeach
                        <tr><td colspan="2" class="bold" style="text-align:right">Total awarded</td><td class="bold">৳ {{ number_format($meeting->totalAwarded(), 2) }}</td></tr>
                    </tbody>
                </table>
            @endif
            @if ($meeting->decisions)
                <p style="margin-top:6px">{{ $meeting->decisions }}</p>
            @endif
        </li>
        <li>There being no further matters to discuss, the meeting was adjourned with thanks to all present.</li>
    </ol>

    <div class="sig-block">
        <p>With thanks,</p>
        <p style="margin-top:24px" class="bold">({{ $convener->name ?? '[Convener Name]' }})</p>
        <p>Convener, Central Procurement Committee,</p>
        <p>ESDO, Dhaka.</p>
    </div>
@endsection
