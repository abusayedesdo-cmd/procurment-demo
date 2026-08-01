@extends('layouts.app')
@section('title', 'Reports')
@section('content')

<div class="stat-grid">
  <div class="card card-pad"><div class="stat-label">Total PRs (FY)</div><div class="stat-value">{{ $totalPrs }}</div></div>
  <div class="card card-pad"><div class="stat-label">Cases completed</div><div class="stat-value">{{ $completed }}</div></div>
  <div class="card card-pad"><div class="stat-label">Contracted value</div><div class="stat-value">৳ {{ number_format($contracted / 100000, 1) }}L</div></div>
  <div class="card card-pad"><div class="stat-label">Active cases</div><div class="stat-value">{{ $cases->where('current_step', '<', 23)->count() }}</div></div>
</div>

<div class="card" style="overflow-x:auto">
  <div style="padding:14px 18px;border-bottom:1px solid #EEEFF6;font-weight:700;font-size:14px">Procurement report — FY {{ now()->year }}–{{ now()->addYear()->format('y') }}</div>
  <table class="data" style="min-width:800px">
    <thead><tr><th>Ref</th><th>Description</th><th>Method</th><th>Category</th><th class="num">Estimate (BDT)</th><th class="num">Awarded (BDT)</th><th>Status</th></tr></thead>
    <tbody>
    @foreach ($cases as $case)
      <tr>
        <td style="font-weight:700;color:var(--brand)">{{ $case->ref }}</td>
        <td style="font-weight:600">{{ $case->title }}</td>
        <td>{{ $case->method }}</td>
        <td>{{ $case->category }}</td>
        <td class="num">{{ number_format($case->amount, 2) }}</td>
        <td class="num" style="font-weight:600">{{ $case->current_step >= 17 ? number_format($case->amount * 0.94, 2) : '—' }}</td>
        <td>
          <span style="font-size:12px;font-weight:600;color:{{ $case->current_step >= 23 ? 'var(--ok)' : 'var(--warn-ink)' }}">
            <span class="status-dot" style="background:{{ $case->current_step >= 23 ? 'var(--ok)' : 'var(--warn)' }}"></span>
            {{ $case->current_step >= 23 ? 'Completed' : \App\Models\ProcurementCase::STEPS[min($case->current_step, 22)]['phase'] }}
          </span>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endsection
