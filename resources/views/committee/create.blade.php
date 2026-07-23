@extends('layouts.app')
@section('title', 'Schedule Committee Meeting')
@section('content')

<div><a href="{{ route('committee.index') }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← All meetings</a></div>

<div class="card card-pad">
  <div style="font-size:13px;color:var(--muted);margin-bottom:14px">
    Suggested next sitting — {{ (int) \App\Models\ProcurementPolicy::get(\App\Models\ProcurementPolicy::COMMITTEE_INTERVAL_DAYS, 15) }} days after the last meeting, snapped to a Saturday.
    Override the date below if needed.
  </div>

  <form method="POST" action="{{ route('committee.store') }}">
    @csrf
    <div class="field" style="max-width:220px;margin-bottom:18px">
      <label>Meeting date</label>
      <input type="date" name="meeting_date" value="{{ old('meeting_date', $suggested->format('Y-m-d')) }}" required>
      @error('meeting_date')<div style="color:var(--bad);font-size:12px;margin-top:4px">{{ $message }}</div>@enderror
    </div>

    <b style="font-size:14px">Agenda — cases awaiting a committee sitting</b>
    <div style="margin-top:10px;display:flex;flex-direction:column;gap:8px">
      @forelse ($awaiting as $case)
        <label style="display:flex;align-items:center;gap:10px;font-size:13px;padding:10px 12px;border:1px solid var(--line);border-radius:8px">
          <input type="checkbox" name="cases[]" value="{{ $case->id }}" {{ $preselectId === $case->id ? 'checked' : '' }}>
          <span class="chip chip-method">{{ \App\Models\CommitteeMeeting::AGENDA_LABELS[$case->pendingAgendaType()] }}</span>
          <span style="flex:1">{{ $case->ref }} — {{ $case->title }}</span>
        </label>
      @empty
        <div style="font-size:13px;color:var(--muted)">No cases are currently waiting on a committee decision. You can still schedule the sitting and attach cases later.</div>
      @endforelse
    </div>

    <div style="margin-top:18px"><button type="submit" class="btn btn-primary">Schedule meeting</button></div>
  </form>
</div>

@endsection
