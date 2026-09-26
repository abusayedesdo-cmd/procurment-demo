@extends('layouts.app')
@section('title', 'My Committee Work')
@section('content')

<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
  <div style="font-size:13px;color:var(--muted);max-width:680px">
    Cases currently transferred to a committee you're on. When a case moves to another committee later, it drops off this list automatically.
  </div>
  <a href="{{ route('dashboard') }}" class="btn btn-outline" style="padding:6px 14px;font-size:12.5px;white-space:nowrap">← Back to Dashboard</a>
</div>

@if ($myCommittees->isNotEmpty())
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:4px">
    @foreach ($myCommittees as $membership)
      <span class="chip" style="font-size:11.5px">{{ $membership->committee?->name }} — {{ $membership->designation_in_committee ?? 'Member' }}</span>
    @endforeach
  </div>
@endif

@if ($transfers->isNotEmpty())
  <div style="margin-top:14px">
    <input
      type="text"
      id="committeeWorkSearch"
      placeholder="Search by case name, PR number, or committee..."
      style="width:100%;max-width:420px;padding:8px 12px;font-size:13px;border:1px solid #E2E8F0;border-radius:8px;"
    >
  </div>
@endif

@if ($transfers->isEmpty())
  <div class="card card-pad" style="margin-top:14px">
    <div style="font-size:13.5px;color:var(--muted)">No cases are currently with your committee(s).</div>
  </div>
@else
  <div id="committeeWorkGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;margin-top:14px">
    @foreach ($transfers as $t)
      @php
        $case = $t->procurementPlan?->purchaseRequisition?->procurementCase;
        $pr = $t->procurementPlan?->purchaseRequisition;
        $searchHaystack = strtolower(
            ($case->title ?? '') . ' ' .
            ($pr->pr_number ?? '') . ' ' .
            ($t->toCommittee?->name ?? '') . ' ' .
            ($t->fromCommittee?->name ?? '')
        );
      @endphp
      <div class="card card-pad" data-search="{{ $searchHaystack }}" style="display:flex;flex-direction:column;gap:10px">
        <div style="display:flex;align-items:center;gap:8px">
          <span class="chip" style="font-size:11.5px">{{ $t->toCommittee?->name }}</span>
          <span style="margin-left:auto;font-size:11.5px;color:var(--muted)">Transferred {{ $t->transfer_date->format('d M Y') }}</span>
        </div>
        <div style="font-size:14.5px;font-weight:700;line-height:1.4">{{ $case->title ?? ($pr->pr_number ?? 'PR #' . $t->procurementPlan?->pr_id) }}</div>
        <div style="font-size:12.5px;color:var(--muted)">{{ $pr?->pr_number ?? '—' }} @if($t->fromCommittee) · from {{ $t->fromCommittee->name }} @endif</div>
        @if ($t->transfer_note)
          <div style="font-size:12px;color:var(--muted);font-style:italic">"{{ $t->transfer_note }}"</div>
        @endif
        <div style="display:flex;gap:8px;margin-top:4px">
          @if ($case)
            <a href="{{ route('cases.show', $case) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">Open Case</a>
          @endif
          @if ($pr)
            <a href="{{ route('purchase-requisitions.show', $pr) }}" class="btn btn-outline" style="padding:6px 12px;font-size:12.5px">Open PR</a>
          @endif
        </div>
      </div>
    @endforeach
  </div>
  <div id="committeeWorkEmpty" class="card card-pad" style="margin-top:14px;display:none">
    <div style="font-size:13.5px;color:var(--muted)">No cases match your search.</div>
  </div>
@endif

@endsection

@section('scripts')
<script>
    const cwSearch = document.getElementById('committeeWorkSearch');
    if (cwSearch) {
        cwSearch.addEventListener('input', () => {
            const q = cwSearch.value.trim().toLowerCase();
            const cards = document.querySelectorAll('#committeeWorkGrid > div[data-search]');
            let visibleCount = 0;

            cards.forEach(card => {
                const matches = (card.getAttribute('data-search') || '').includes(q);
                card.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            const emptyState = document.getElementById('committeeWorkEmpty');
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 ? '' : 'none';
            }
        });
    }
</script>
@endsection
