@extends('layouts.app')
@section('title', 'Vendor Register')
@section('content')

<div style="font-size:13px;color:var(--muted);max-width:680px">Enlisted suppliers with eligibility documents. A vendor is <b style="color:var(--ok)">responsive</b> when all mandatory documents are valid: Trade License, TIN, PSR, BIN and relevant experience.</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:14px">
  @foreach ($vendors as $v)
    @php
      $docs = ['Trade License' => $v->trade_license, 'TIN' => $v->tin, 'PSR' => $v->psr, 'BIN' => $v->bin, 'Experience' => $v->experience];
      $resp = $v->isResponsive();
    @endphp
    <div class="card card-pad" style="display:flex;flex-direction:column;gap:10px">
      <div style="display:flex;align-items:flex-start;gap:10px">
        <div style="width:38px;height:38px;border-radius:9px;background:#EEF0FA;color:var(--brand);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;flex-shrink:0">
          {{ strtoupper(collect(explode(' ', str_replace('M/S ', '', $v->name)))->map(fn ($w) => $w[0])->take(2)->implode('')) }}
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:14px;font-weight:700;line-height:1.3">{{ $v->name }}</div>
          <div style="font-size:12px;color:var(--muted);margin-top:2px">{{ $v->type }} · {{ $v->address }}</div>
        </div>
        <span class="chip {{ $resp ? 'chip-ok' : 'chip-bad' }}">{{ $resp ? 'Responsive' : 'Non-responsive' }}</span>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:6px">
        @foreach ($docs as $label => $ok)
          <span style="display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;border-radius:6px;padding:3px 8px;
            border:1px solid {{ $ok ? '#CBE5D5' : '#E5C0BB' }};color:{{ $ok ? 'var(--ok)' : 'var(--bad)' }};background:{{ $ok ? '#F4FAF6' : '#FBF1EF' }}">
            {{ $ok ? '✓' : '✗' }} {{ $label }}
          </span>
        @endforeach
      </div>
      <div style="font-size:12px;color:var(--muted)">Awarded contracts: <b style="color:var(--ink)">{{ $v->awards }}</b> · Last participation: {{ $v->last_participation?->format('M Y') ?? '—' }}</div>
    </div>
  @endforeach
</div>
@endsection
