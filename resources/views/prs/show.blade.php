@extends('layouts.app')
@section('title', 'Requisition Detail')
@section('content')

<div><a href="{{ route('prs.index') }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← All requisitions</a></div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;align-items:start" class="detail-grid">
  <div style="display:flex;flex-direction:column;gap:16px;min-width:0">
    <div class="card" style="padding:22px">
      <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
        <b style="font-size:18px">{{ $pr->pr_no }}</b>
        <span class="chip chip-{{ strtolower($pr->category) }}">{{ $pr->category }}</span>
        <span style="font-size:12.5px;font-weight:600;color:{{ $pr->rejected ? 'var(--bad)' : ($pr->isApproved() ? 'var(--ok)' : 'var(--warn-ink)') }}">{{ $pr->statusLabel() }}</span>
        <div style="flex:1"></div>
        <a href="{{ route('prs.print', $pr) }}" class="btn btn-outline" style="padding:7px 13px;font-size:12.5px">🖨 Print format</a>
      </div>
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:16px;font-size:13px">
        <div><div class="stat-label">Project / Unit</div><div style="font-weight:600;margin-top:3px">{{ $pr->project }}</div></div>
        <div><div class="stat-label">Requestor</div><div style="font-weight:600;margin-top:3px">{{ $pr->requestor }} — {{ $pr->designation }}</div></div>
        <div><div class="stat-label">PR Date</div><div style="font-weight:600;margin-top:3px">{{ $pr->pr_date->format('d M Y') }}</div></div>
        <div><div class="stat-label">Est. Delivery</div><div style="font-weight:600;margin-top:3px">{{ $pr->delivery_date?->format('d M Y') ?? '—' }}</div></div>
      </div>
    </div>

    <div class="card" style="overflow-x:auto">
      <div style="padding:14px 18px;border-bottom:1px solid #EEEFF6;font-weight:700;font-size:14px">Line items</div>
      <table class="data" style="min-width:560px">
        <thead><tr><th>Sl.</th><th>Particulars</th><th>Unit</th><th class="num">Qty</th><th class="num">Unit Price</th><th class="num">Amount</th></tr></thead>
        <tbody>
        @foreach ($pr->items as $i => $item)
          <tr>
            <td style="color:var(--muted)">{{ $i + 1 }}</td>
            <td style="font-weight:600">{{ $item->name }}</td>
            <td>{{ $item->unit }}</td>
            <td class="num">{{ $item->qty + 0 }}</td>
            <td class="num">{{ number_format($item->rate, 2) }}</td>
            <td class="num" style="font-weight:600">{{ number_format($item->total(), 2) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
      <div style="display:flex;justify-content:flex-end;gap:16px;padding:13px 18px;align-items:baseline">
        <span style="font-size:12.5px;color:var(--muted)">Total estimated amount</span>
        <b style="font-size:17px;font-variant-numeric:tabular-nums">৳ {{ number_format($pr->total(), 2) }}</b>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card" style="padding:20px">
      <b style="font-size:14px;display:block;margin-bottom:14px">Approval flow</b>
      @foreach (\App\Models\PurchaseRequisition::STAGES as $i => $stageName)
        @php
          $done = $pr->stage > $i;
          $current = !$pr->rejected && (int) $pr->stage === $i;
          $rejHere = $pr->rejected && (int) $pr->stage === $i;
        @endphp
        <div style="display:flex;gap:12px">
          <div style="display:flex;flex-direction:column;align-items:center">
            <div style="width:26px;height:26px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;
              background:{{ $done ? 'var(--ok)' : ($rejHere ? 'var(--bad)' : ($current ? '#fff' : '#F2F3F8')) }};
              color:{{ $done || $rejHere ? '#fff' : ($current ? 'var(--brand)' : 'var(--faint)') }};
              border:2px solid {{ $done ? 'var(--ok)' : ($rejHere ? 'var(--bad)' : ($current ? 'var(--brand)' : 'var(--line)')) }}">{{ $done ? '✓' : ($rejHere ? '!' : $i + 1) }}</div>
            @if ($i < 3)<div style="width:2px;flex:1;min-height:22px;background:{{ $done ? 'var(--ok)' : 'var(--line)' }}"></div>@endif
          </div>
          <div style="padding-bottom:18px">
            <div style="font-size:13.5px;font-weight:700;color:{{ $done || $current ? 'var(--ink)' : 'var(--faint)' }}">{{ $stageName }}</div>
            <div style="font-size:12.5px;color:var(--muted)">{{ $i === 0 ? $pr->requestor . ' — ' . $pr->designation : '' }}</div>
            <div style="font-size:11.5px;font-weight:600;color:{{ $done ? 'var(--ok)' : ($rejHere ? 'var(--bad)' : ($current ? 'var(--warn-ink)' : 'var(--faint)')) }}">
              {{ $done ? 'Signed & forwarded' : ($rejHere ? 'Sent back for correction' : ($current ? 'Awaiting action' : 'Not reached')) }}
            </div>
          </div>
        </div>
      @endforeach

      @if ($canAct)
        <div style="display:flex;gap:8px;margin-top:6px">
          <form method="POST" action="{{ route('prs.approve', $pr) }}" style="flex:1">@csrf<button class="btn btn-ok" style="width:100%">Approve</button></form>
          <form method="POST" action="{{ route('prs.reject', $pr) }}" style="flex:1">@csrf<button class="btn btn-danger" style="width:100%">Send back</button></form>
        </div>
      @endif
    </div>
    <div style="background:#F7F8FC;border:1px solid var(--line);border-radius:12px;padding:16px;font-size:12.5px;color:#44465E;line-height:1.6">
      <b>Policy note:</b> Once fully approved, this {{ strtolower($pr->category) }} requisition proceeds by
      {{ ['Goods' => 'RFQ (Request for Quotation)', 'Works' => 'RFT / Tender with BOQ, drawing & design', 'Services' => 'RFP with TOR'][$pr->category] }}.
    </div>
  </div>
</div>
<style>@media (max-width:860px){.detail-grid{grid-template-columns:1fr !important}}</style>
@endsection
