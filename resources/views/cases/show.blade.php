@extends('layouts.app')
@section('title', 'Case Detail')
@section('content')

<div><a href="{{ route('cases.index') }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← All cases</a></div>

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

@foreach ($phases as $phase => $steps)
  <div class="card" style="overflow:hidden">
    <div style="padding:12px 18px;background:#F7F8FC;border-bottom:1px solid #EEEFF6;display:flex;align-items:center;gap:10px">
      <span style="font-size:13px;font-weight:800;color:var(--brand)">{{ $phase }}</span>
      <span style="font-size:11.5px;font-weight:600;color:var(--muted)">{{ $steps->whereNotNull('completed_at')->count() }} of {{ $steps->count() }} complete</span>
    </div>
    @foreach ($steps as $step)
      @php $done = $step->isDone(); $current = !$done && $step->step_no === $case->current_step + 1; @endphp
      <div style="display:flex;align-items:center;gap:12px;padding:11px 18px;border-bottom:1px solid var(--line-soft)">
        <span style="font-size:11px;font-weight:700;color:var(--faint);width:22px">{{ str_pad($step->step_no, 2, '0', STR_PAD_LEFT) }}</span>
        <div style="width:22px;height:22px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;
          background:{{ $done ? 'var(--ok)' : ($current ? '#fff' : '#F7F8FC') }};
          color:{{ $done ? '#fff' : 'var(--brand)' }};
          border:2px solid {{ $done ? 'var(--ok)' : ($current ? 'var(--brand)' : 'var(--line)') }}">{{ $done ? '✓' : '' }}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13.5px;font-weight:600;color:{{ $done || $current ? 'var(--ink)' : 'var(--faint)' }}">{{ $step->name }}</div>
          @if ($step->detail)<div style="font-size:12px;color:var(--muted)">{{ $step->detail }}</div>@endif
        </div>
        @if ($current)
          <form method="POST" action="{{ route('cases.complete-step', $case) }}">@csrf
            <button class="btn btn-primary" style="padding:7px 13px;font-size:12px">Mark complete</button>
          </form>
        @elseif ($done)
          <span style="font-size:11.5px;color:var(--muted)">{{ $step->completed_at->format('d M') }}</span>
        @endif
      </div>
    @endforeach
  </div>
@endforeach
@endsection
