@extends('layouts.app')
@section('title', 'Committee Meeting')
@section('content')

<div><a href="{{ route('committee.index') }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← All meetings</a></div>

<div class="card" style="padding:22px">
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <b style="font-size:18px;flex:1;min-width:220px">{{ $meeting->meeting_no }}</b>
    <span class="chip {{ $meeting->status === 'held' ? 'chip-ok' : ($meeting->status === 'cancelled' ? 'chip-bad' : 'chip-method') }}" style="font-size:12px;padding:4px 10px">{{ ucfirst($meeting->status) }}</span>
  </div>
  <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:12px;font-size:13px;color:var(--muted)">
    <span>Date: <b style="color:var(--ink)">{{ $meeting->meeting_date->format('d M Y (D)') }}</b></span>
    @if ($meeting->isHeld())
      <span>Held at: <b style="color:var(--ink)">{{ $meeting->held_at->format('d M Y, h:i A') }}</b></span>
    @endif
    <span>Conflicts declared: <b style="color:var(--ink)">{{ $meeting->conflictedCount() }}</b></span>
  </div>
</div>

<div class="card card-pad">
  <b style="font-size:14px">Conflict of Interest declarations</b>
  <div style="margin-top:12px;display:flex;flex-direction:column;gap:8px">
    @forelse ($meeting->declarations as $d)
      <div style="display:flex;align-items:center;gap:12px;padding:10px 12px;border:1px solid var(--line);border-radius:8px;flex-wrap:wrap">
        <div style="flex:1;min-width:160px">
          <div style="font-size:13.5px;font-weight:600">{{ $d->committeeMember->name }}</div>
          <div style="font-size:11.5px;color:var(--muted)">{{ $d->committeeMember->designation ?? 'Member' }}{{ $d->committeeMember->is_chair ? ' · Chair' : '' }}</div>
        </div>
        @if ($d->isDeclared())
          <span class="chip {{ $d->has_conflict ? 'chip-bad' : 'chip-ok' }}">{{ $d->has_conflict ? 'Conflict declared' : 'No conflict' }}</span>
          @if ($d->notes)<span style="font-size:12px;color:var(--muted)">{{ $d->notes }}</span>@endif
        @elseif (!$meeting->isHeld())
          <form method="POST" action="{{ route('committee.declare', $meeting) }}" style="display:flex;align-items:center;gap:6px">
            @csrf
            <input type="hidden" name="committee_member_id" value="{{ $d->committee_member_id }}">
            <input type="text" name="notes" placeholder="Notes (optional)" style="border:1px solid #D9DAE8;border-radius:8px;padding:6px 9px;font-size:12px;width:150px">
            <button type="submit" name="has_conflict" value="0" class="btn btn-outline" style="padding:6px 10px;font-size:11.5px">No conflict</button>
            <button type="submit" name="has_conflict" value="1" class="btn btn-danger" style="padding:6px 10px;font-size:11.5px">Has conflict</button>
          </form>
        @else
          <span class="chip chip-bad">Not declared</span>
        @endif
      </div>
    @empty
      <div style="font-size:13px;color:var(--muted)">No committee members on file. Seed members via <code>CommitteeMemberSeeder</code>.</div>
    @endforelse
  </div>
</div>

<div class="card card-pad">
  <b style="font-size:14px">Meeting minutes</b>
  <form method="POST" action="{{ route('committee.minutes', $meeting) }}" style="margin-top:10px">
    @csrf
    <textarea name="minutes" rows="5" placeholder="Record discussion, decisions, and attendance..."
      style="width:100%;border:1px solid #D9DAE8;border-radius:8px;padding:10px 12px;font-size:13px;font-family:inherit" {{ $meeting->isHeld() ? 'readonly' : '' }}>{{ old('minutes', $meeting->minutes) }}</textarea>
    @unless ($meeting->isHeld())
      <div style="margin-top:10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <button type="submit" class="btn btn-outline">Save minutes</button>
      </div>
    @endunless
  </form>

  @unless ($meeting->isHeld())
    <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--line-soft)">
      <form method="POST" action="{{ route('committee.hold', $meeting) }}">
        @csrf
        <button type="submit" class="btn btn-primary" {{ (!$meeting->minutes || !$meeting->allDeclared()) ? 'disabled style="opacity:.5"' : '' }}>Mark meeting held</button>
        @if (!$meeting->minutes || !$meeting->allDeclared())
          <div style="font-size:12px;color:var(--muted);margin-top:6px">
            Needs: {{ !$meeting->minutes ? 'minutes saved' : '' }}{{ (!$meeting->minutes && !$meeting->allDeclared()) ? ' · ' : '' }}{{ !$meeting->allDeclared() ? 'all member declarations submitted' : '' }}
          </div>
        @endif
      </form>
    </div>
  @endunless
</div>

<div class="card card-pad">
  <b style="font-size:14px">Agenda — cases on this sitting</b>
  <div style="margin-top:10px;display:flex;flex-direction:column;gap:8px">
    @forelse ($meeting->cases as $case)
      <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px solid var(--line);border-radius:8px;flex-wrap:wrap">
        <span class="chip chip-method">{{ \App\Models\CommitteeMeeting::AGENDA_LABELS[$case->pivot->agenda_type] }}</span>
        <a href="{{ route('cases.show', $case) }}" style="flex:1;min-width:180px;font-weight:600;text-decoration:none">{{ $case->ref }} — {{ $case->title }}</a>
        @if ($case->pivot->resolved)
          <span class="chip chip-ok">Resolved</span>
        @elseif ($meeting->isHeld())
          <form method="POST" action="{{ route('committee.resolve', [$meeting, $case]) }}">
            @csrf
            <button type="submit" class="btn btn-ok" style="padding:7px 13px;font-size:12px">Resolve agenda item</button>
          </form>
        @else
          <span style="font-size:12px;color:var(--muted)">Mark meeting held to resolve</span>
        @endif
      </div>
    @empty
      <div style="font-size:13px;color:var(--muted)">No cases attached yet.</div>
    @endforelse
  </div>

  @if (!$meeting->isHeld() && $awaiting->isNotEmpty())
    <form method="POST" action="{{ route('committee.cases.store', $meeting) }}" style="margin-top:14px;padding-top:14px;border-top:1px solid var(--line-soft);display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      @csrf
      <select name="case_id" style="border:1px solid #D9DAE8;border-radius:8px;padding:8px 10px;font-size:13px;flex:1;min-width:220px">
        @foreach ($awaiting as $case)
          <option value="{{ $case->id }}">{{ $case->ref }} — {{ $case->title }} ({{ \App\Models\CommitteeMeeting::AGENDA_LABELS[$case->pendingAgendaType()] }})</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-outline">Add to agenda</button>
    </form>
  @endif
</div>

@endsection
