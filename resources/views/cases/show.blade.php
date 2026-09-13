@extends('layouts.app')
@section('title', 'Case Detail')
@section('content')

@php
    $backToStep = request()->query('focus') === 'meetings' ? request()->query('step') : null;
@endphp
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
    @if ($backToStep)
        <a href="{{ route('process-steps.show', ['slug' => $backToStep, 'skip_redirect' => 1]) }}" style="font-size:12.5px;font-weight:600;text-decoration:none">← Back to Step</a>
        <a href="{{ route('cases.create') }}" class="btn btn-primary" style="font-size:12.5px">+ New Case</a>
    @else
        <a href="{{ url()->previous() ?: route('dashboard') }}"
        onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }"
        style="font-size:12.5px;font-weight:600;text-decoration:none">← back </a>
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
    <span>Source PR: <b style="color:var(--ink)">{{ $case->purchaseRequisition?->pr_number ?? '—' }}</b></span>
    <span>Estimate: <b style="color:var(--ink)">৳ {{ number_format($case->amount, 2) }}</b></span>
    <span>Solicitation docs: <b style="color:var(--ink)">{{ ['RFQ' => 'Specification', 'RFP' => 'TOR', 'RFT' => 'BOQ, drawing & design'][$case->method] }}</b></span>
  </div>
  <div style="display:flex;align-items:center;gap:12px;margin-top:16px">
    <div class="progress" style="flex:1;height:8px"><div style="width:{{ $case->progressPct() }}%"></div></div>
    <!-- <span style="font-size:12.5px;font-weight:700;color:var(--brand)">Step {{ min($case->current_step + 1, 23) }} of 23</span> -->
  </div>
</div>

<!-- <div class="card card-pad">
  @php
    $plan = $case->purchaseRequisition?->procurementPlan;
    $latestTransfer = $plan?->subCommitteeTransfers->first();
  @endphp
  <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
    <div>
      <b style="font-size:14px">Sub-Committee</b>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">
        @if ($latestTransfer)
          Currently with <b style="color:var(--ink)">{{ $latestTransfer->toCommittee?->name }}</b>
          (from {{ $latestTransfer->fromCommittee?->name ?? '—' }}, {{ $latestTransfer->transfer_date->format('d M Y') }}) — that committee's members can see this case on their dashboard.
        @else
          Not yet transferred to a sub-committee — it's still with the main/central committee.
        @endif
      </div>
    </div>
    @if ($plan)
      <a href="{{ route('modules.show', 'sub-committee-transfers') }}?new=1&field_procurement_plan_id={{ $plan->id }}"
         class="btn btn-primary" style="padding:6px 12px;font-size:12.5px">
        {{ $latestTransfer ? 'Transfer again' : 'Transfer to Sub-Committee' }}
      </a>
    @else
      <span style="font-size:12px;color:var(--muted)">No Procurement Plan linked to this case's PR yet.</span>
    @endif
  </div>
</div> -->

<div class="card card-pad">
  <b style="font-size:14px">Committee Meetings</b>
  <div style="font-size:12px;color:var(--muted);margin-top:2px">1st meeting sets the tender schedule; 2nd meeting records the tender opening &amp; award decision. Each one is recorded in 3 steps — Notice, Attendance, Resolution.</div>
  <div style="display:flex;flex-direction:column;gap:14px;margin-top:12px">
    @foreach (['first' => '1st Meeting — Tender Schedule', 'second' => '2nd Meeting — Opening & Award'] as $type => $label)
      @php $m = $case->meetings->firstWhere('meeting_type', $type); @endphp
      <div style="border:1px solid var(--line-soft);border-radius:10px;padding:12px 14px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
          <div style="font-size:13px;font-weight:600">{{ $label }}</div>
          @if ($m && $m->rezulation_no)
            <span style="font-size:12px;color:var(--muted)">Rezulation No. {{ $m->rezulation_no }} — {{ $m->meeting_date->format('d M Y') }}</span>
          @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:6px;margin-top:10px">
          {{-- Step 1: Notice --}}
          <div style="display:flex;align-items:center;gap:10px">
            <span style="width:18px;font-size:12px;color:{{ $m ? 'var(--good, #0D9488)' : 'var(--muted)' }}">{{ $m ? '✓' : '1' }}</span>
            <span style="flex:1;font-size:12.5px">Notice{{ $m ? ' — ' . $m->notice_number : '' }}</span>
            @if (! $m)
              <a href="{{ route('meetings.notice.create', [$case, $type]) }}" class="btn btn-primary" style="padding:5px 10px;font-size:12px">Send notice</a>
            @else
              <a href="{{ route('api.meetings.notice-document', $m) }}" class="btn btn-outline" style="padding:5px 10px;font-size:12px">Notice PDF</a>
            @endif
          </div>

          {{-- Step 2: Attendance --}}
          <div style="display:flex;align-items:center;gap:10px">
            <span style="width:18px;font-size:12px;color:{{ $m?->attendance_number ? 'var(--good, #0D9488)' : 'var(--muted)' }}">{{ $m?->attendance_number ? '✓' : '2' }}</span>
            <span style="flex:1;font-size:12.5px">Attendance{{ $m?->attendance_number ? ' — ' . $m->attendance_number : '' }}</span>
            @if ($m && ! $m->attendance_number)
              <a href="{{ route('meetings.attendance.create', $m) }}" class="btn btn-primary" style="padding:5px 10px;font-size:12px">Record attendance</a>
            @elseif ($m?->attendance_number)
              <a href="{{ route('api.meetings.attendance-document', $m) }}" class="btn btn-outline" style="padding:5px 10px;font-size:12px">Attendance PDF</a>
            @else
              <span style="font-size:12px;color:var(--muted)">Waiting on notice</span>
            @endif
          </div>

          {{-- Step 3: Resolution --}}
          <div style="display:flex;align-items:center;gap:10px">
            <span style="width:18px;font-size:12px;color:{{ $m?->rezulation_no ? 'var(--good, #0D9488)' : 'var(--muted)' }}">{{ $m?->rezulation_no ? '✓' : '3' }}</span>
            <span style="flex:1;font-size:12.5px">Resolution{{ $m?->rezulation_no ? ' — Rezulation No. ' . $m->rezulation_no : '' }}</span>
            @if ($m?->attendance_number && ! $m->rezulation_no)
              <a href="{{ route('meetings.resolution.create', $m) }}" class="btn btn-primary" style="padding:5px 10px;font-size:12px">Finalize resolution</a>
            @elseif ($m?->rezulation_no)
              <a href="{{ route('api.meetings.minutes-document', $m) }}" class="btn btn-outline" style="padding:5px 10px;font-size:12px">Resolution PDF</a>
              <a href="{{ route('meetings.show', $m) }}" class="btn btn-outline" style="padding:5px 10px;font-size:12px">View minutes</a>
            @else
              <span style="font-size:12px;color:var(--muted)">Waiting on attendance</span>
            @endif
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>

@endsection