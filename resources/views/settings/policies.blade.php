@extends('layouts.app')
@section('title', 'Procurement Policy')
@section('content')

<div style="font-size:13px;color:var(--muted);max-width:680px;margin-bottom:16px">
  These values drive the RFQ/OTM decision and the auto-calculated Procurement Plan milestone dates.
  Update them to match ESDO's actual procurement policy — the figures below are placeholders.
</div>

@if (session('ok'))
  <div class="card card-pad" style="border-left:3px solid var(--ok);margin-bottom:16px;font-size:13px;font-weight:600">{{ session('ok') }}</div>
@endif

<form method="POST" action="{{ route('settings.policies.update') }}">
  @csrf

  @foreach ($policies as $group => $rows)
    <div class="card card-pad" style="margin-bottom:16px">
      <b style="font-size:14px">{{ $group }}</b>
      <div style="margin-top:12px;display:grid;grid-template-columns:1fr auto;gap:10px 16px;align-items:center">
        @foreach ($rows as $p)
          <label style="font-size:13px;color:var(--ink)">{{ $p->label }}</label>
          <div style="display:flex;align-items:center;gap:6px">
            <input type="number" step="0.01" min="0" name="values[{{ $p->key }}]" value="{{ old('values.' . $p->key, $p->value) }}"
                   style="width:130px;border:1px solid #D9DAE8;border-radius:8px;padding:7px 10px;font-size:13px;text-align:right">
            <span style="font-size:11.5px;color:var(--muted);width:32px">{{ $p->unit }}</span>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach

  <button type="submit" class="btn btn-primary">Save policy</button>
</form>

@endsection
