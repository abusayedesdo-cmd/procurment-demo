@extends('documents.layout')

@section('title', 'Procurement Report ' . ($case->ref ?? ''))

@section('content')
@php
    $d = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d F, Y') : '-';
    $m = fn ($v) => $v === null ? '-' : number_format((float) $v, 2);
    $mark = fn ($v) => $v === null ? '-' : number_format((float) $v, 2);
    $ord = function ($n) { if (! $n) return '-'; $s = ['th', 'st', 'nd', 'rd']; $v = $n % 100; return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]); };
@endphp
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    @if (file_exists(public_path('img/esdo-logo.png')))
        <img src="{{ public_path('img/esdo-logo.png') }}" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    @endif
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;">{{ $committeeName }}, {{ $committeeAddress }}</div>
        </div>
    </div>

    <h1>PROCUREMENT REPORT</h1>
    <p class="center muted">Report date: {{ $generatedAt->format('d F, Y') }}</p>

    <h2>1. Procurement Summary</h2>
    <table class="bordered">
        <tbody>
            <tr><td style="width:30%" class="bold">Case reference</td><td>{{ $case->ref }}</td></tr>
            <tr><td class="bold">Subject</td><td>{{ $case->title }}</td></tr>
            <tr><td class="bold">Purchase Requisition No.</td><td>{{ $pr->pr_number ?? '-' }} (dated {{ $d($pr->requisition_date ?? null) }})</td></tr>
            <tr><td class="bold">Project</td><td>{{ $pr->project_name ?? '-' }}</td></tr>
            <tr><td class="bold">Category / Method</td><td>{{ $case->category }} — {{ $case->natureLabel() }}</td></tr>
            <tr><td class="bold">Requested by</td><td>{{ $pr->requestor_name ?? '-' }}@if (!empty($pr->requestor_designation)), {{ $pr->requestor_designation }}@endif</td></tr>
            <tr><td class="bold">Delivery location</td><td>{{ $pr->delivery_location ?? '-' }}</td></tr>
            <tr><td class="bold">Procurement committee</td><td>{{ $committeeName }}</td></tr>
            <tr><td class="bold">Estimated cost (BDT)</td><td>{{ $m($estimated) }}</td></tr>
            <tr><td class="bold">Awarded amount (BDT)</td><td>{{ $m($awarded) }}</td></tr>
            <tr><td class="bold">Difference (estimate − award)</td><td>{{ $saving === null ? '-' : $m($saving) . ($saving >= 0 ? ' (below estimate)' : ' (above estimate)') }}</td></tr>
            <tr><td class="bold">Selected vendor</td><td>{{ $award->vendor->name ?? ($recommended ? $recommended . ' (recommended)' : 'Not awarded yet') }}</td></tr>
        </tbody>
    </table>

    @if ($items->isNotEmpty())
        <h2>2. Items Procured</h2>
        <table class="bordered">
            <thead><tr><th style="width:6%">SL</th><th>Item</th><th>Specification</th><th style="width:9%">Unit</th><th style="width:9%">Qty</th><th style="width:14%">Rate (BDT)</th><th style="width:15%">Total (BDT)</th></tr></thead>
            <tbody>
                @foreach ($items as $i => $it)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $it->item->name ?? '' }}</td><td>{{ $it->specification }}</td><td>{{ $it->unit->name ?? '' }}</td>
                        <td style="text-align:right">{{ rtrim(rtrim(number_format((float) $it->quantity, 2), '0'), '.') }}</td>
                        <td style="text-align:right">{{ $m($it->rate_bdt) }}</td><td style="text-align:right">{{ $m($it->total_amount) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>3. Key Dates</h2>
    <table class="bordered">
        <thead><tr><th style="width:42%">Step</th><th style="width:22%">Date</th><th>Reference</th></tr></thead>
        <tbody>
            @forelse ($timeline as $t)
                <tr><td>{{ $t['label'] }}</td><td>{{ $d($t['date']) }}</td><td>{{ $t['ref'] }}</td></tr>
            @empty
                <tr><td colspan="3">No dated records yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($meetings->isNotEmpty())
        <h2>4. Committee Meetings</h2>
        <table class="bordered">
            <thead><tr><th style="width:6%">SL</th><th style="width:16%">Meeting</th><th style="width:20%">Date</th><th style="width:18%">Resolution No.</th><th>Agenda / Decision</th></tr></thead>
            <tbody>
                @foreach ($meetings as $i => $mt)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $mt->meeting_type === 'second' ? '2nd meeting' : '1st meeting' }}</td><td>{{ $d($mt->meeting_date) }}</td><td>{{ $mt->rezulation_no ?? '-' }}</td><td>{{ \Illuminate\Support\Str::limit(trim(($mt->decisions ?: $mt->agenda) ?? ''), 220) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>5. Quotations Received @if ($rfq)<span class="muted" style="font-weight:normal;font-size:11px">— RFQ {{ $rfq->rfq_number }}@if ($rfq_count > 1) (latest of {{ $rfq_count }} RFQs)@endif, closing {{ $d($rfq->closing_date) }}</span>@endif</h2>
    <table class="bordered">
        <thead><tr><th style="width:6%">SL</th><th>Vendor</th><th style="width:20%">Quoted amount (BDT)</th><th style="width:20%">Status</th></tr></thead>
        <tbody>
            @forelse ($quotations as $i => $q)
                <tr><td>{{ $i + 1 }}</td><td>{{ $q->vendor->name ?? '' }}</td><td style="text-align:right">{{ $m($q->quoted_amount) }}</td><td>{{ ucfirst((string) $q->status) }}</td></tr>
            @empty
                <tr><td colspan="4">No quotation recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($ranking->isNotEmpty())
        <h2>6. Evaluation Result</h2>
        <table class="bordered">
            <thead><tr><th style="width:6%">SL</th><th>Vendor</th><th style="width:14%">Bid (BDT)</th><th>Technical</th><th>Financial</th>@if ($has_sample)<th>Sample</th>@endif<th>Total</th><th style="width:7%">Rank</th></tr></thead>
            <tbody>
                @foreach ($ranking as $i => $r)
                    <tr><td>{{ $i + 1 }}</td><td>{{ $r->vendor->name ?? '' }}</td><td style="text-align:right">{{ $m($r->amount) }}</td>
                        <td style="text-align:center">{{ $mark($r->technical_marks) }}</td><td style="text-align:center">{{ $mark($r->financial_marks) }}</td>
                        @if ($has_sample)<td style="text-align:center">{{ $mark($r->sample_marks) }}</td>@endif
                        <td style="text-align:center" class="bold">{{ $mark($r->total_marks) }}</td><td style="text-align:center">{{ $ord($r->rank) }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @if ($recommended)<p>Recommended vendor: <span class="bold">{{ $recommended }}</span></p>@endif
    @endif

    <h2>7. Award, Contract & Delivery</h2>
    <table class="bordered">
        <tbody>
            <tr><td style="width:30%" class="bold">Notification of Award</td><td>@if ($award){{ $award->noa_number }} — {{ $d($award->noa_date) }} — {{ $award->vendor->name ?? '' }}@else Not issued yet @endif</td></tr>
            <tr><td class="bold">Pay Order</td><td>@if ($payOrder)Pay order {{ $m($payOrder->pay_order_amount) }} BDT; received {{ $m($payOrder->received_amount) }} BDT on {{ $d($payOrder->received_date) }}@else - @endif</td></tr>
            <tr><td class="bold">Contract Agreement</td><td>@if ($agreement){{ $agreement->agreement_number }} — {{ $d($agreement->agreement_date) }}@else - @endif</td></tr>
            <tr><td class="bold">Work Order</td><td>@if ($workOrder){{ $workOrder->wo_number }} — {{ $d($workOrder->wo_date) }}@else - @endif</td></tr>
            <tr><td class="bold">Delivery</td><td>@forelse ($deliveries as $dv){{ $d($dv->delivery_date) }}@if ($dv->received_by), received by {{ $dv->received_by }}@endif @if ($dv->remarks)({{ $dv->remarks }})@endif<br>@empty - @endforelse</td></tr>
        </tbody>
    </table>

    <h2>8. Document Checklist</h2>
    <table class="bordered">
        <thead><tr><th style="width:8%">SL</th><th>Document</th><th style="width:16%">Records</th><th style="width:18%">Status</th></tr></thead>
        <tbody>
            @foreach ($checklist as $i => $row)
                <tr><td>{{ $i + 1 }}</td><td>{{ $row[0] }}</td><td style="text-align:center">{{ $row[1] }}</td><td>{{ $row[1] > 0 ? 'Available' : 'Not yet' }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <table class="plain sig-block">
        <tr>
            <td style="width:33%">Prepared by<br><br><br>{{ $preparedBy ?? '' }}</td>
            <td style="width:34%; text-align:center">Member Secretary<br><br><br>{{ $committeeName }}</td>
            <td style="width:33%; text-align:right">Convener<br><br><br>{{ $committeeName }}</td>
        </tr>
    </table>
@endsection
