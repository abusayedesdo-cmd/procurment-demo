@extends('layouts.app')
@section('title', 'Procurement Cases')
@section('content')

<div style="font-size:13px;color:var(--muted);max-width:680px">Each case follows the 23-step ESDO procurement process — Goods → RFQ, Services → RFP, Works → RFT.</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px">
  @foreach ($cases as $case)
    <a href="{{ route('cases.show', $case) }}" class="card card-pad" style="display:flex;flex-direction:column;gap:10px;text-decoration:none;color:inherit">
      <div style="display:flex;align-items:center;gap:8px">
        <span class="chip chip-method">{{ $case->method }}</span>
        <span class="chip chip-{{ strtolower($case->category) }}">{{ $case->category }}</span>
        <span style="margin-left:auto;font-size:11.5px;font-weight:700;color:var(--muted)">{{ $case->ref }}</span>
      </div>
      <div style="font-size:14.5px;font-weight:700;line-height:1.4">{{ $case->title }}</div>
      <div style="font-size:12.5px;color:var(--muted)">{{ $case->purchaseRequisition?->pr_no ?? '—' }} · ৳ {{ number_format($case->amount, 2) }}</div>
      <div style="display:flex;align-items:center;gap:10px">
        <div class="progress" style="flex:1"><div style="width:{{ $case->progressPct() }}%"></div></div>
        <span style="font-size:11.5px;font-weight:700;color:var(--brand);white-space:nowrap">Step {{ min($case->current_step + 1, 23) }}/23</span>
      </div>
      <div style="font-size:12px;font-weight:600;color:#44465E">Now: {{ $case->currentStepName() }}</div>
    </a>
  @endforeach
</div>
@endsection
