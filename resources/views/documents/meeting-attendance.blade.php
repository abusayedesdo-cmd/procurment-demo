@extends('documents.layout')

@section('title', 'Meeting Attendance ' . $meeting->attendance_number)

@section('content')
    <div class="center">
        <p class="bold" style="font-size:13px;margin-bottom:0">Eco-Social Development Organization (ESDO)</p>
        <p class="muted" style="margin-top:0">Adabor, Dhaka-1207.</p>
    </div>

    <div class="meta" style="margin-top:14px">
        <p><span class="bold">Attendance No:</span> {{ $meeting->attendance_number }}</p>
        <p><span class="bold">Date:</span> {{ $meeting->meeting_date->format('d F, Y') }}</p>
    </div>

    <h1>MEETING ATTENDANCE</h1>
    <p class="center">Central Procurement Committee</p>

    <table class="bordered">
        <tr><td class="bold" style="width:32%">Procurement Reference</td><td>{{ $case->ref }}</td></tr>
        <tr><td class="bold">Subject</td><td>{{ $case->title }}</td></tr>
        <tr><td class="bold">Venue</td><td>{{ $meeting->location }}</td></tr>
    </table>

    <h2>Meeting Agenda</h2>
    <ol class="terms">
        <li>{{ $meeting->agenda ?: 'Discussion and decision on Procurement Reference ' . $case->ref . ' — ' . $case->title }}</li>
        <li>Miscellaneous.</li>
    </ol>

    <h2>Attendance</h2>
    <table class="bordered">
        <thead>
            <tr><th style="width:6%">SL</th><th style="width:28%">Name</th><th style="width:28%">Designation</th><th style="width:20%">Signature</th><th>Remarks</th></tr>
        </thead>
        <tbody>
            @forelse ($meeting->attendees as $i => $a)
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $a->name }}</td>
                    <td>{{ $a->designation }}</td>
                    <td>&nbsp;</td>
                    <td>{{ $a->remarks }}</td>
                </tr>
            @empty
                @foreach ($roster as $i => $m)
                    <tr>
                        <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->designation }}</td>
                        <td>&nbsp;</td>
                        <td></td>
                    </tr>
                @endforeach
            @endforelse
        </tbody>
    </table>

    <div class="sig-block">
        <p class="bold">Approved</p>
        <p style="margin-top:24px">({{ $convener->name ?? '[Convener Name]' }})</p>
        <p class="bold">Convener, Central Procurement Committee, ESDO.</p>
    </div>
@endsection
