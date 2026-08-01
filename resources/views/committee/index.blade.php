@extends('layouts.app')
@section('title', 'Committee Meetings')
@section('content')

<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
  <div style="font-size:13px;color:var(--muted);max-width:620px;flex:1">
    Procurement Committee sittings run on a fixed cadence — every {{ (int) \App\Models\ProcurementPolicy::get(\App\Models\ProcurementPolicy::COMMITTEE_INTERVAL_DAYS, 15) }} days, on Saturdays.
    {{ $memberCount }} active member{{ $memberCount === 1 ? '' : 's' }} on file.
  </div>
  <a href="{{ route('committee.create') }}" class="btn btn-primary">Schedule meeting</a>
</div>

@if ($awaiting->isNotEmpty())
  <div class="card card-pad" style="border-left:3px solid var(--warn)">
    <b style="font-size:14px">Awaiting a committee sitting</b>
    <div style="margin-top:10px;display:flex;flex-direction:column;gap:8px">
      @foreach ($awaiting as $case)
        <div style="display:flex;align-items:center;gap:10px;font-size:13px">
          <span class="chip chip-method">{{ \App\Models\CommitteeMeeting::AGENDA_LABELS[$case->pendingAgendaType()] }}</span>
          <a href="{{ route('cases.show', $case) }}" style="font-weight:600;text-decoration:none">{{ $case->ref }} — {{ $case->title }}</a>
        </div>
      @endforeach
    </div>
  </div>
@endif

<div class="card" style="overflow:auto">
  <table class="data">
    <thead><tr><th>Meeting No.</th><th>Date</th><th>Status</th><th>Agenda items</th><th>Declarations</th><th></th></tr></thead>
    <tbody>
      @forelse ($meetings as $m)
        <tr>
          <td><b>{{ $m->meeting_no }}</b></td>
          <td>{{ $m->meeting_date->format('d M Y (D)') }}</td>
          <td><span class="chip {{ $m->status === 'held' ? 'chip-ok' : ($m->status === 'cancelled' ? 'chip-bad' : 'chip-method') }}">{{ ucfirst($m->status) }}</span></td>
          <td class="num">{{ $m->cases()->count() }}</td>
          <td class="num">{{ $m->declarations_count }}</td>
          <td><a href="{{ route('committee.show', $m) }}" style="font-size:12.5px;font-weight:700;text-decoration:none">Open →</a></td>
        </tr>
      @empty
        <tr><td colspan="6" style="text-align:center;color:var(--muted)">No committee meetings scheduled yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

@endsection
