@extends('layouts.app')
@section('title', 'Procurement Plan')
@section('content')

<div style="font-size:13px;color:var(--muted);max-width:640px">Plan lines are auto-created from approved purchase requisitions. Milestone dates are calculated from PR date and procurement policy.</div>

@foreach ($rows as $row)
  @php $pr = $row['pr']; @endphp
  <div class="card card-pad">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
      <b style="font-size:14px;flex:1;min-width:200px">{{ $pr->pr_no }} — {{ $pr->title }}</b>
      <span class="chip chip-method">{{ $pr->method() }}</span>
      <span class="chip" style="background:{{ $pr->is_otm ?? $pr->determineIsOtm() ? 'var(--brand)' : '#EEF0FA' }};color:{{ $pr->is_otm ?? $pr->determineIsOtm() ? '#fff' : 'var(--muted)' }}">{{ $row['nature'] }}</span>
      <span class="chip chip-{{ strtolower($pr->category) }}">{{ $pr->category }}</span>
      <b style="font-size:13.5px;font-variant-numeric:tabular-nums">৳ {{ number_format($pr->total(), 2) }}</b>
    </div>
    <div style="display:flex;margin-top:16px;overflow-x:auto;padding-bottom:4px">
      @foreach ($row['milestones'] as $i => $m)
        @php $done = $pr->isApproved() && $i < 2; $currentM = $pr->isApproved() && $i === 2; @endphp
        <div style="min-width:118px;flex:1;padding-right:8px">
          <div style="display:flex;align-items:center">
            <div style="width:11px;height:11px;border-radius:50%;flex-shrink:0;background:{{ $done ? 'var(--ok)' : '#fff' }};border:2px solid {{ $done ? 'var(--ok)' : ($currentM ? 'var(--brand)' : '#D9DAE8') }}"></div>
            <div style="height:2px;flex:1;background:{{ $done ? 'var(--ok)' : 'var(--line)' }}"></div>
          </div>
          <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;margin-top:8px;line-height:1.35">{{ $m['label'] }}</div>
          <div style="font-size:12px;font-weight:600;margin-top:2px;color:{{ $done || $currentM ? 'var(--ink)' : 'var(--faint)' }}">{{ $m['date']->format('d M') }}</div>
        </div>
      @endforeach
    </div>
    <div style="font-size:12px;color:var(--muted);margin-top:10px">Estimated completion: <b style="color:var(--ink)">{{ $row['days'] }} days</b> (PR to delivery)</div>
  </div>
@endforeach
@endsection
