@extends('layouts.app')
@section('title', $type === 'first' ? '1st Meeting — Tender Schedule' : '2nd Meeting — Opening & Award')
@section('content')

<div><a href="{{ route('cases.show', $case) }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← {{ $case->ref }}</a></div>

<form method="POST" action="{{ route('meetings.store', [$case, $type]) }}" class="card card-pad" style="display:flex;flex-direction:column;gap:16px">
  @csrf
  <div>
    <b style="font-size:16px">{{ $type === 'first' ? '1st Meeting — Tender Schedule' : '2nd Meeting — Tender Opening & Award' }}</b>
    <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Case: {{ $case->ref }} — {{ $case->title }}</div>
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
  <div class="field"><label>Decisions of Today's Meeting</label>
    <textarea name="decisions" rows="4" required placeholder="Summarize what the committee decided">{{ old('decisions') }}</textarea>
  </div>

  @if ($type === 'first')
    <div style="border-top:1px solid var(--line-soft);padding-top:14px">
      <b style="font-size:13.5px">Tender Schedule Decided</b>
      <div class="form-grid" style="margin-top:10px">
        <div class="field"><label>Publish / Advertisement Date</label><input type="date" name="publish_date" value="{{ old('publish_date') }}"></div>
        <div class="field"><label>Submission Closing Date</label><input type="date" name="closing_date" value="{{ old('closing_date') }}"></div>
        <div class="field"><label>Opening Date</label><input type="date" name="opening_date" value="{{ old('opening_date') }}"></div>
      </div>
      <div class="field" style="margin-top:10px"><label>Reason, if this differs from the standard policy schedule</label>
        <input name="schedule_override_reason" value="{{ old('schedule_override_reason') }}" placeholder="e.g. emergency response — shortened notice period approved by ED">
      </div>
    </div>
  @else
    <div style="border-top:1px solid var(--line-soft);padding-top:14px">
      <b style="font-size:13.5px">Award Decision</b>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">One row per vendor / lot — a case can be split across multiple vendors, as in the real Cyclone Shelter minutes.</div>
      <div style="border:1px solid var(--line);border-radius:10px;overflow-x:auto;margin-top:10px">
        <table class="data" id="awards" style="min-width:640px">
          <thead><tr><th>Vendor</th><th>Scope / Lot</th><th class="num">Awarded Amount</th><th></th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
      <button type="button" class="btn btn-outline" style="margin-top:10px" onclick="addAward()">+ Add award line</button>
    </div>
  @endif

  <div style="border-top:1px solid var(--line-soft);padding-top:14px">
    <b style="font-size:13.5px">Attendees</b>
    <div style="border:1px solid var(--line);border-radius:10px;overflow-x:auto;margin-top:10px">
      <table class="data" id="attendees" style="min-width:520px">
        <thead><tr><th>Name</th><th>Designation</th><th></th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <button type="button" class="btn btn-outline" style="margin-top:10px" onclick="addAttendee()">+ Add attendee</button>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="{{ route('cases.show', $case) }}" class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary">Save minutes</button>
  </div>
</form>

<script>
let roster = @json($roster->map(fn($m) => ['name' => $m->name, 'designation' => $m->roleLabel() . ', Central Procurement Committee']));
let vendors = @json($vendors->map(fn($v) => ['id' => $v->id, 'name' => $v->name]));

let ai = 0;
function addAttendee(name = '', designation = '') {
  const i = ai++;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input name="attendees[${i}][name]" value="${name}" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input name="attendees[${i}][designation]" value="${designation}" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><button type="button" onclick="this.closest('tr').remove()" style="border:none;background:none;color:var(--bad);font-size:15px;cursor:pointer">×</button></td>`;
  document.querySelector('#attendees tbody').appendChild(tr);
}

let wi = 0;
function addAward() {
  const i = wi++;
  const opts = vendors.map(v => `<option value="${v.id}">${v.name}</option>`).join('');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="awards[${i}][vendor_id]" onchange="this.nextElementSibling.value = this.options[this.selectedIndex].text !== 'Other / not in system' ? this.options[this.selectedIndex].text : ''" style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px;margin-bottom:4px">
        <option value="">Other / not in system</option>${opts}
      </select>
      <input name="awards[${i}][vendor_name]" placeholder="Vendor name" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px">
    </td>
    <td><input name="awards[${i}][scope_note]" placeholder="e.g. Najirhat A Malek GPS & Cyclone Shelter" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input type="number" name="awards[${i}][amount]" value="0" min="0" step="0.01" style="width:130px;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><button type="button" onclick="this.closest('tr').remove()" style="border:none;background:none;color:var(--bad);font-size:15px;cursor:pointer">×</button></td>`;
  document.querySelector('#awards tbody').appendChild(tr);
}

roster.forEach(m => addAttendee(m.name, m.designation));
@if ($type === 'second')
addAward();
@endif
</script>
@endsection
