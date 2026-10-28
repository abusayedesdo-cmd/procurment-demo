@extends('layouts.app')
@section('title', 'Committee Roster')
@section('content')

<div style="font-size:13px;color:var(--muted);max-width:720px;margin-bottom:16px">
  Pick a committee to see its members. Only the selected committee's members are listed here.
  They receive that committee's meeting notices and are pre-filled on its attendance sheet.
  Deactivating a person removes them from notices and default attendance lists; past meeting minutes stay intact.
</div>

@if (session('ok'))
  <div class="card card-pad" style="border-left:3px solid var(--ok);margin-bottom:16px;font-size:13px;font-weight:600">{{ session('ok') }}</div>
@endif
@if ($errors->any())
  <div style="background:#FBF1EF;border:1px solid #E5C0BB;color:var(--bad);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:16px;max-width:720px">{{ $errors->first() }}</div>
@endif

@if ($committees->isEmpty())
  <div class="card card-pad" style="font-size:13px">
    No committee has been created yet.
    @if ($isAdmin) Create the Main Committee under <a href="{{ route('admin.committees.index') }}">Admin → Committees</a>.
    @else Ask an administrator to create it under Admin → Committees. @endif
  </div>
@else

  {{-- ===== Committee picker ===== --}}
  <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px">
    @foreach ($committees as $c)
      @php $active = $selected && $selected->id === $c->id; @endphp
      <a href="{{ route('settings.committee.index', ['committee' => $c->id]) }}"
         style="text-decoration:none;border:1px solid {{ $active ? 'var(--accent)' : 'var(--line)' }};background:{{ $active ? 'var(--accent)' : 'transparent' }};color:{{ $active ? '#fff' : 'var(--ink)' }};border-radius:999px;padding:6px 14px;font-size:13px;font-weight:600">
        {{ $c->type === 'main' ? 'Main / Central' : 'Sub' }} · {{ $c->name }}@if ($c->type !== 'main' && $c->project?->name && ! str_contains($c->name, $c->project->name)) — {{ $c->project->name }}@endif
      </a>
    @endforeach
  </div>

  {{-- ===== Selected committee ===== --}}
  <div class="card" style="overflow:hidden;margin-bottom:16px">
    <div style="padding:14px 16px;border-bottom:1px solid var(--line)">
      <b style="font-size:15px">{{ $selected->name }}</b>
      <span style="font-size:12px;color:var(--muted);margin-left:6px">
        {{ $isMain ? 'Main / Central Committee' : 'Sub-Committee' . ($selected->project?->name ? ' — ' . $selected->project->name : '') }}
      </span>
      <div style="font-size:12.5px;margin-top:4px;color:{{ $members->count() < $minMembers || $members->count() > $maxMembers ? '#B45309' : 'var(--muted)' }}">
        {{ $members->count() }} member(s) — policy: {{ $minMembers }}–{{ $maxMembers }} members.
      </div>
    </div>

    @if ($members->isEmpty())
      <div style="padding:16px;font-size:13px;color:var(--muted)">
        This committee has no members yet, so nobody will be notified about its meetings.
        @if ($isMain) Add the first member below.
        @elseif ($isAdmin) Add members under <a href="{{ route('admin.committees.index') }}">Admin → Committees</a>.
        @else Ask an administrator to add members under Admin → Committees. @endif
      </div>
    @else
      <table class="data">
        <thead><tr><th>Name</th><th>Designation</th><th>Role</th><th>Email</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach ($members as $m)
            <tr>
              <td>{{ $m['name'] }}</td>
              <td>{{ $m['designation'] ?: '—' }}</td>
              <td>{{ $m['role'] ?: '—' }}</td>
              <td>
                @if ($m['roster'])
                  <form method="POST" action="{{ route('settings.committee.update-email', $m['roster']) }}" style="display:flex;gap:6px;align-items:center">
                    @csrf
                    <input type="email" name="email" value="{{ $m['roster']->email }}" placeholder="add email" style="border:1px solid #D9DAE8;border-radius:6px;padding:5px 7px;font-size:12px;width:170px">
                    <button class="btn btn-outline" style="padding:5px 8px;font-size:12px">Save</button>
                  </form>
                @else
                  <span style="font-size:12.5px">{{ $m['email'] ?: '—' }}</span>
                  <div style="font-size:11px;color:var(--muted)">Login account — change it under User Management</div>
                @endif
              </td>
              <td>{{ $m['active'] ? 'Active' : 'Inactive' }}</td>
              <td>
                @if ($m['roster'])
                  <form method="POST" action="{{ route('settings.committee.toggle', $m['roster']) }}">
                    @csrf
                    <button class="btn btn-outline" style="padding:5px 10px;font-size:12px">{{ $m['active'] ? 'Deactivate' : 'Reactivate' }}</button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- ===== Add member (Main committee only) ===== --}}
  @if ($isMain)
    <div class="card card-pad" style="margin-bottom:16px">
      <b style="font-size:14px">Add a member to {{ $selected->name }}</b>
      <form method="POST" action="{{ route('settings.committee.store') }}" style="margin-top:12px">
        @csrf
        <input type="hidden" name="committee_id" value="{{ $selected->id }}">
        <div class="form-grid">
          <div class="field"><label>Name</label><input name="name" value="{{ old('name') }}" required></div>
          <div class="field"><label>Designation</label><input name="designation" value="{{ old('designation') }}" placeholder="e.g. Sr. Procurement Manager" required></div>
          <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="for meeting notice emails"></div>
          <div class="field"><label>Role</label>
            <select name="role">
              <option value="convener">Convener</option>
              <option value="member" selected>Member</option>
              <option value="member_secretary">Member Secretary</option>
            </select>
          </div>
        </div>
        <button class="btn btn-primary" style="margin-top:12px">Add member</button>
      </form>
    </div>
  @else
    <div style="font-size:12.5px;color:var(--muted);margin-bottom:16px">
      Members of a Sub-Committee are added or removed under
      @if ($isAdmin)<a href="{{ route('admin.committees.index') }}">Admin → Committees</a>@else Admin → Committees (administrator only)@endif.
    </div>
  @endif

  {{-- ===== Roster people who are in no committee ===== --}}
  @if ($orphans->isNotEmpty())
    <div class="card" style="overflow:hidden">
      <div style="padding:14px 16px;border-bottom:1px solid var(--line)">
        <b style="font-size:14px">Not in any committee</b>
        <div style="font-size:12.5px;color:var(--muted);margin-top:3px">
          These people are on the old roster but belong to no committee, so they receive no meeting notices.
          @if ($isAdmin) Add them to a committee under <a href="{{ route('admin.committees.index') }}">Admin → Committees</a>.@endif
        </div>
      </div>
      <table class="data">
        <thead><tr><th>Name</th><th>Designation</th><th>Old roster</th><th>Email</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach ($orphans as $o)
            <tr>
              <td>{{ $o->name }}</td>
              <td>{{ $o->designation }}</td>
              <td>{{ $o->committee_type === 'purchase_committee' ? 'Purchase Committee' : 'Central Procurement' }}</td>
              <td>
                <form method="POST" action="{{ route('settings.committee.update-email', $o) }}" style="display:flex;gap:6px;align-items:center">
                  @csrf
                  <input type="email" name="email" value="{{ $o->email }}" placeholder="add email" style="border:1px solid #D9DAE8;border-radius:6px;padding:5px 7px;font-size:12px;width:170px">
                  <button class="btn btn-outline" style="padding:5px 8px;font-size:12px">Save</button>
                </form>
              </td>
              <td>{{ $o->active ? 'Active' : 'Inactive' }}</td>
              <td>
                <form method="POST" action="{{ route('settings.committee.toggle', $o) }}">
                  @csrf
                  <button class="btn btn-outline" style="padding:5px 10px;font-size:12px">{{ $o->active ? 'Deactivate' : 'Reactivate' }}</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif

@endif

@endsection