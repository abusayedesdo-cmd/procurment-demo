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
        <select name="case_id" id="caseSelect">
          <option value="">— None (general committee meeting) —</option>
          @foreach ($openCases as $c)
            <option value="{{ $c->id }}" {{ (string) old('case_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->ref }} — {{ $c->title }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="field">
      <label>Committee</label>
      <select name="committee_id" id="committeeSelect">
        @foreach ($committeeOptions as $o)
          <option value="{{ $o['id'] }}" {{ (string) old('committee_id', $defaultCommitteeId) === (string) $o['id'] ? 'selected' : '' }}>{{ $o['label'] }}</option>
        @endforeach
      </select>
      <div id="committeeHint" style="font-size:11.5px;color:var(--muted);margin-top:4px">Choose which committee this meeting is for — only its members will be emailed.</div>
    </div>
  @endunless

  {{-- Who will actually receive this notice (updates when the committee/case changes) --}}
  <div style="border:1px solid #E2E8F0;border-radius:8px;padding:12px 14px;background:#F8FAFC">
    <div style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)">Who will receive this notice</div>
    <div id="rcpTitle" style="font-weight:700;font-size:14px;margin-top:4px">—</div>
    <div id="rcpNote" style="font-size:12px;margin-top:2px"></div>
    <ul id="rcpList" style="list-style:none;margin:8px 0 0;padding:0;font-size:13px"></ul>
  </div>

  <div class="form-grid">
    <div class="field"><label>Location</label><input name="location" value="{{ old('location', 'ESDO Board Meeting Room') }}" required></div>
    <div class="field"><label>Meeting Date</label><input type="date" name="meeting_date" value="{{ old('meeting_date', now()->format('Y-m-d')) }}" required></div>
    <div class="field"><label>Time</label><input name="meeting_time" value="{{ old('meeting_time', '10:00 AM') }}" placeholder="e.g. 9:00 AM"></div>
  </div>

  <div class="field">
    <label>Office (address printed on the notice, resolution &amp; attendance)</label>
    <select name="office">
      <option value="">— Committee's saved address —</option>
      @foreach (array_keys(config('esdo.offices', [])) as $officeName)
        <option value="{{ $officeName }}" {{ old('office') === $officeName ? 'selected' : '' }}>{{ $officeName }}</option>
      @endforeach
    </select>
    <div style="font-size:11.5px;color:var(--muted);margin-top:4px">Applies to the Main committee only. A Sub-Committee always uses its own address.</div>
  </div>

  <div class="field"><label>Agenda</label>
    <textarea name="agenda" rows="3" required placeholder="e.g. Discussion on the requisition for ...">{{ old('agenda') }}</textarea>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="{{ $case ? route('cases.show', $case) : url()->previous() }}" class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary" id="sendBtn">Send notice</button>
  </div>
</form>

<script>
(function () {
  const data = @json($committeeData);          // committee id => {label, fallback, members[]}
  const caseHolder = @json($caseCommitteeMap); // case id => committee id currently holding it
  const fixedId = @json($fixedCommitteeId);    // set when the notice is for one specific case

  const sel = document.getElementById('committeeSelect');
  const caseSel = document.getElementById('caseSelect');
  const hint = document.getElementById('committeeHint');
  const title = document.getElementById('rcpTitle');
  const note = document.getElementById('rcpNote');
  const list = document.getElementById('rcpList');
  const btn = document.getElementById('sendBtn');

  function render(id) {
    const c = data[id];
    list.innerHTML = '';
    note.textContent = '';
    if (!c) { title.textContent = 'No committee selected'; btn.textContent = 'Send notice'; return; }

    title.textContent = c.label;
    const withEmail = c.members.filter(m => m.email).length;
    const missing = c.members.length - withEmail;

    c.members.forEach(m => {
      const li = document.createElement('li');
      li.style.cssText = 'display:flex;justify-content:space-between;gap:12px;padding:4px 0;border-top:1px solid #EDF1F6';
      const left = document.createElement('span');
      left.textContent = m.name + (m.title ? ' — ' + m.title : '');
      const right = document.createElement('span');
      right.style.cssText = 'white-space:nowrap;font-size:12px';
      if (m.email) { right.textContent = m.email; right.style.color = '#64748B'; }
      else { right.textContent = 'no email — will NOT be notified'; right.style.color = '#B91C1C'; }
      li.append(left, right);
      list.appendChild(li);
    });

    const notes = [];
    if (c.members.length === 0) notes.push('This committee has no members yet — add them under Committees first.');
    if (c.fallback) notes.push('The Main Committee has no members set up yet, so the Central Procurement roster is used.');
    if (missing > 0 && c.members.length > 0) notes.push(missing + ' member(s) have no email and will not get the notice.');
    note.textContent = notes.join(' ');
    note.style.color = (c.members.length === 0 || missing > 0) ? '#B45309' : '#64748B';

    btn.textContent = withEmail > 0
      ? 'Send notice to ' + withEmail + ' member' + (withEmail > 1 ? 's' : '')
      : 'Record notice (no emails will be sent)';
  }

  function syncCase() {
    const holder = caseSel && caseSel.value ? caseHolder[caseSel.value] : null;
    if (holder && data[holder]) {
      if (![...sel.options].some(o => o.value == holder)) sel.add(new Option(data[holder].label, holder));
      sel.value = holder;
      sel.disabled = true;
      hint.textContent = 'The committee follows the chosen case — it is currently with this committee.';
    } else if (sel) {
      sel.disabled = false;
      hint.textContent = 'Choose which committee this meeting is for — only its members will be emailed.';
    }
    render(sel.value);
  }

  if (fixedId) {
    render(fixedId);
  } else if (sel) {
    sel.addEventListener('change', () => render(sel.value));
    if (caseSel) caseSel.addEventListener('change', syncCase);
    syncCase();
  }
})();
</script>

@endsection