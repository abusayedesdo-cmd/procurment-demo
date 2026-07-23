@extends('layouts.app')
@section('title', 'Approvals Inbox')
@section('content')

<div style="font-size:13px;color:var(--muted)">Items waiting on <b style="color:var(--ink)">{{ $roleLabel }}</b>. Switch role in the top bar to see other queues.</div>

@forelse ($rows as $pr)
  <div class="card" style="padding:16px 20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    <span class="chip chip-{{ strtolower($pr->category) }}">{{ $pr->category }}</span>
    <div style="flex:1;min-width:200px">
      <div style="font-size:14px;font-weight:700">{{ $pr->pr_no }} — {{ $pr->title }}</div>
      <div style="font-size:12.5px;color:var(--muted);margin-top:2px">{{ $pr->project }} · Raised by {{ $pr->requestor }} · {{ $pr->pr_date->format('d M Y') }}</div>
    </div>
    <b style="font-size:14px;font-variant-numeric:tabular-nums">৳ {{ number_format($pr->total(), 2) }}</b>
    <div style="display:flex;gap:8px">
      <a href="{{ route('prs.show', $pr) }}" class="btn btn-outline" style="padding:8px 14px;font-size:12.5px">Open</a>
      <form method="POST" action="{{ route('prs.approve', $pr) }}">@csrf
        <button class="btn btn-ok" style="padding:8px 14px;font-size:12.5px">Approve</button>
      </form>
    </div>
  </div>
@empty
  <div style="background:#fff;border:1px dashed #D9DAE8;border-radius:12px;padding:40px;text-align:center;color:var(--muted);font-size:13.5px">Nothing waiting for this role. ✓</div>
@endforelse
@endsection
