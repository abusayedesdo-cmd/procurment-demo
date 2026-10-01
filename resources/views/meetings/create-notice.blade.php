@extends('layouts.app')
@section('title', ($type === 'first' ? '1st' : ($type === 'second' ? '2nd' : '')) . ' Meeting Notice')
@section('content')

@if ($case)
<div><a href="{{ route('cases.show', $case) }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← {{ $case->ref }}</a></div>
@endif

<form method="POST" action="{{ $case ? route('meetings.notice.store', [$case, $type]) : route('meetings.notice.store.standalone') }}" class="card card-pad" style="display:flex;flex-direction:column;gap:16px;max-width:640px"
      onsubmit="const btn=this.querySelector('button'); if (btn) { btn.disabled = true; btn.textContent = 'Sending notice…'; }">
  @csrf
  <div>
    <div style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)">Step 1 of 3 — Notice</div>
    @if ($case)
      <b style="font-size:16px">{{ $type === 'first' ? '1st Meeting — Tender Schedule' : '2nd Meeting — Opening & Award' }}</b>
      <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Case: {{ $case->ref }} — {{ $case->title }}</div>
    @else
      <b style="font-size:16px">Meeting Notice</b>
      <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Not linked to a specific case (or optionally pick one below).</div>
    @endif
    <div style="font-size:12px;color:var(--muted);margin-top:6px">Schedule the meeting and notify the committee. Attendance and the resolution/minutes are recorded in the next steps, once the meeting has actually been held.</div>
  </div>

  @if ($errors->any())
    <div style="background:#FBF1EF;border:1px solid #E5C0BB;color:var(--bad);border-radius:8px;padding:10px 14px;font-size:13px">{{ $errors->first() }}</div>
  @endif

  @unless ($case)
    <div class="form-grid">
      <div class="field">
        <label>Meeting Type</label>
        <select name="meeting_type" required>
          <option value="first" {{ old('meeting_type') === 'first' ? 'selected' : '' }}>1st Meeting — Tender Schedule</option>
          <option value="second" {{ old('meeting_type') === 'second' ? 'selected' : '' }}>2nd Meeting — Opening & Award</option>
        </select>
      </div>
      <div class="field">
        <label>Link to a Case (optional)</label>
        <select name="case_id">
          <option value="">— None (general committee meeting) —</option>
          @foreach ($openCases as $c)
            <option value="{{ $c->id }}" {{ (string) old('case_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->ref }} — {{ $c->title }}</option>
          @endforeach
        </select>
      </div>
    </div>
  @endunless

  <div class="form-grid">
    <div class="field"><label>Location</label><input name="location" value="{{ old('location', 'ESDO Board Meeting Room') }}" required></div>
    <div class="field"><label>Meeting Date</label><input type="date" name="meeting_date" value="{{ old('meeting_date', now()->format('Y-m-d')) }}" required></div>
    <div class="field"><label>Time</label><input name="meeting_time" value="{{ old('meeting_time', '10:00 AM') }}" placeholder="e.g. 9:00 AM"></div>
  </div>

  <div class="field"><label>Agenda</label>
    <textarea name="agenda" rows="3" required placeholder="e.g. Discussion on the requisition for ...">{{ old('agenda') }}</textarea>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="{{ $case ? route('cases.show', $case) : url()->previous() }}" class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary">Send notice</button>
  </div>
</form>

@endsection