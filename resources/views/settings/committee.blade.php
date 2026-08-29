@extends('layouts.app')
@section('title', 'Central Procurement Committee')
@section('content')

<div style="font-size:13px;color:var(--muted);max-width:680px;margin-bottom:16px">
  This roster is used as the default attendee list when recording committee meetings. Deactivating a member keeps their past meeting minutes intact but removes them from future default rosters.
</div>

@if (session('ok'))
  <div class="card card-pad" style="border-left:3px solid var(--ok);margin-bottom:16px;font-size:13px;font-weight:600">{{ session('ok') }}</div>
@endif

<div class="card" style="overflow:hidden;margin-bottom:16px">
  <table class="data">
    <thead><tr><th>Name</th><th>Designation</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach ($members as $m)
        <tr>
          <td>{{ $m->name }}</td>
          <td>{{ $m->designation }}</td>
          <td>
            <form method="POST" action="{{ route('settings.committee.update-email', $m) }}" style="display:flex;gap:6px;align-items:center">
              @csrf
              <input type="email" name="email" value="{{ $m->email }}" placeholder="add email" style="border:1px solid #D9DAE8;border-radius:6px;padding:5px 7px;font-size:12px;width:170px">
              <button class="btn btn-outline" style="padding:5px 8px;font-size:12px">Save</button>
            </form>
          </td>
          <td>{{ $m->roleLabel() }}</td>
          <td>{{ $m->active ? 'Active' : 'Inactive' }}</td>
          <td>
            <form method="POST" action="{{ route('settings.committee.toggle', $m) }}">
              @csrf
              <button class="btn btn-outline" style="padding:5px 10px;font-size:12px">{{ $m->active ? 'Deactivate' : 'Reactivate' }}</button>
            </form>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="card card-pad">
  <b style="font-size:14px">Add committee member</b>
  <form method="POST" action="{{ route('settings.committee.store') }}" style="margin-top:12px">
    @csrf
    <div class="form-grid">
      <div class="field"><label>Name</label><input name="name" required></div>
      <div class="field"><label>Designation</label><input name="designation" placeholder="e.g. Sr. Procurement Manager" required></div>
      <div class="field"><label>Email</label><input type="email" name="email" placeholder="for meeting notice emails"></div>
      <div class="field"><label>Role</label>
        <select name="role">
          <option value="convener">Convener</option>
          <option value="member">Member</option>
          <option value="member_secretary">Member Secretary</option>
        </select>
      </div>
    </div>
    <button class="btn btn-primary" style="margin-top:12px">Add member</button>
  </form>
</div>

@endsection