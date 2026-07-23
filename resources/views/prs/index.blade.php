@extends('layouts.app')
@section('title', 'Purchase Requisitions')
@section('content')

<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
  <div style="display:flex;gap:6px;flex:1;flex-wrap:wrap">
    @foreach (['All', 'Goods', 'Works', 'Services', 'Pending', 'Approved'] as $f)
      <a href="{{ route('prs.index', ['filter' => $f]) }}"
         class="btn" style="padding:7px 14px;font-size:12.5px;border-radius:20px;{{ $filter === $f ? 'background:var(--brand);color:#fff' : 'background:#fff;color:#44465E;border:1px solid #D9DAE8' }}">{{ $f }}</a>
    @endforeach
  </div>
  <a href="{{ route('prs.create') }}" class="btn btn-primary">+ New Requisition</a>
</div>

<div class="card" style="overflow-x:auto">
  <table class="data" style="min-width:760px">
    <thead><tr><th>PR No.</th><th>Project / Particulars</th><th>Category</th><th class="num">Amount (BDT)</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
    @foreach ($prs as $pr)
      <tr onclick="location='{{ route('prs.show', $pr) }}'" style="cursor:pointer">
        <td style="font-weight:700;color:var(--brand)">{{ $pr->pr_no }}</td>
        <td><div style="font-weight:600">{{ $pr->title }}</div><div style="font-size:12px;color:var(--muted)">{{ $pr->project }}</div></td>
        <td><span class="chip chip-{{ strtolower($pr->category) }}">{{ $pr->category }}</span></td>
        <td class="num" style="font-weight:600">{{ number_format($pr->total(), 2) }}</td>
        <td>
          @php $c = $pr->rejected ? 'var(--bad)' : ($pr->isApproved() ? 'var(--ok)' : 'var(--warn)'); @endphp
          <span style="font-size:12px;font-weight:600;color:{{ $pr->rejected ? 'var(--bad)' : ($pr->isApproved() ? 'var(--ok)' : 'var(--warn-ink)') }}">
            <span class="status-dot" style="background:{{ $c }}"></span>{{ $pr->statusLabel() }}
          </span>
        </td>
        <td style="color:var(--muted);font-size:12.5px">{{ $pr->pr_date->format('d M Y') }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
@endsection
