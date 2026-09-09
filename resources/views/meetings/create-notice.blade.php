@extends('layouts.app')
@section('title', ($type === 'first' ? '1st' : '2nd') . ' Meeting Notice')
@section('content')

<div><a href="{{ route('cases.show', $case) }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← {{ $case->ref }}</a></div>

<form method="POST" action="{{ route('meetings.notice.store', [$case, $type]) }}" class="card card-pad" style="display:flex;flex-direction:column;gap:16px;max-width:640px">
  @csrf
  <div>
    <div style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)">Step 1 of 3 — Notice</div>
    <b style="font-size:16px">{{ $type === 'first' ? '1st Meeting — Tender Schedule' : '2nd Meeting — Opening & Award' }}</b>
    <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Case: {{ $case->ref }} — {{ $case->title }}</div>
    <div style="font-size:12px;color:var(--muted);margin-top:6px">Schedule the meeting and notify the committee. Attendance and the resolution/minutes are recorded in the next steps, once the meeting has actually been held.</div>
  </div>

  @if ($errors->any())
    <div style="background:#FBF1EF;border:1px solid #E5C0BB;color:var(--bad);border-radius:8px;padding:10px 14px;font-size:13px">{{ $errors->first() }}</div>
  @endif

  <div class="form-grid">
    <div class="field"><label>Location</label><input name="location" value="{{ old('location', 'ESDO Board Meeting Room') }}" required></div>
    <div class="field"><label>Meeting Date</label><input type="date" name="meeting_date" value="{{ old('meeting_date', now()->format('Y-m-d')) }}" required></div>
    <div class="field"><label>Time</label><input name="meeting_time" value="{{ old('meeting_time', '10:00 AM') }}" placeholder="e.g. 9:00 AM"></div>
  </div>

  <div class="field"><label>Agenda</label>
    <textarea name="agenda" rows="3" required placeholder="e.g. Discussion on the requisition for ...">{{ old('agenda') }}</textarea>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary">Send notice</button>
  </div>
</form>

@endsection
