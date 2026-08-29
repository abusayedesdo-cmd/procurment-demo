@extends('layouts.app')
@section('title', 'Case Detail')
@section('content')

@php
    $backToStep = request()->query('focus') === 'meetings' ? request()->query('step') : null;
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
    @if ($backToStep)
        <a href="{{ route('process-steps.show', $backToStep) }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← Back to Step</a>
        <a href="{{ route('cases.create') }}" class="btn btn-primary" style="font-size:12.5px">+ New Case</a>
    @else
        <a href="{{ route('dashboard') }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← Dashboard</a>
    @endif
</div>

<div class="card" style="padding:22px">
  <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
    <b style="font-size:18px;flex:1;min-width:220px">{{ $case->title }}</b>
    <span class="chip chip-method" style="font-size:12px;padding:4px 10px">{{ $case->method }}</span>
    <span class="chip chip-{{ strtolower($case->category) }}" style="font-size:12px;padding:4px 10px">{{ $case->category }}</span>
  </div>
  <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:12px;font-size:13px;color:var(--muted)">
    <span>Ref: <b style="color:var(--ink)">{{ $case->ref }}</b></span>
    <span>Source PR: <b style="color:var(--ink)">{{ $case->purchaseRequisition?->pr_no ?? '—' }}</b></span>
    <span>Estimate: <b style="color:var(--ink)">৳ {{ number_format($case->amount, 2) }}</b></span>
    <span>Solicitation docs: <b style="color:var(--ink)">{{ ['RFQ' => 'Specification', 'RFP' => 'TOR', 'RFT' => 'BOQ, drawing & design'][$case->method] }}</b></span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;margin-top:16px">
    <div class="progress" style="flex:1;height:8px"><div style="width:{{ $case->progressPct() }}%"></div></div>
    <span style="font-size:12.5px;font-weight:700;color:var(--brand)">Step {{ min($case->current_step + 1, 23) }} of 23</span>
  </div>
</div>

<div class="card card-pad">
  <b style="font-size:14px">Committee Meetings</b>
  <div style="font-size:12px;color:var(--muted);margin-top:2px">1st meeting sets the tender schedule; 2nd meeting records the tender opening &amp; award decision.</div>
  <div style="display:flex;flex-direction:column;gap:8px;margin-top:12px">
    @foreach (['first' => '1st Meeting — Tender Schedule', 'second' => '2nd Meeting — Opening & Award'] as $type => $label)
      @php $m = $case->meetings->firstWhere('meeting_type', $type); @endphp
      <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;border:1px solid var(--line-soft);border-radius:10px">
        <div style="flex:1;font-size:13px;font-weight:600">{{ $label }}</div>
        @if ($m)
          <span style="font-size:12px;color:var(--muted)">Rezulation No. {{ $m->rezulation_no }} — {{ $m->meeting_date->format('d M Y') }}</span>
          <a href="{{ route('meetings.show', $m) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12px">View minutes</a>
        @else
          <a href="{{ route('meetings.create', [$case, $type]) }}" class="btn btn-primary" style="padding:6px 12px;font-size:12px">Record meeting</a>
        @endif
      </div>
    @endforeach
  </div>
</div>

@endsection