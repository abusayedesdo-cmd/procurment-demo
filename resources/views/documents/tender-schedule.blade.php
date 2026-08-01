@extends('documents.layout')

@section('title', 'Tender Schedule ' . $rfq->rfq_number)

@section('content')
    <p class="bold">Reference: {{ $rfq->rfq_number }}</p>
    <p>Date: {{ optional($rfq->issue_date)->format('d.m.Y') }}</p>

    <h1>TENDER SCHEDULE</h1>
    <p class="center">({{ $rfq->type }})</p>

    <table class="bordered">
        <tr><td class="bold" style="width:35%">Procurement Reference</td><td>{{ $rfq->rfq_number }}</td></tr>
        <tr><td class="bold">Procurement Nature</td><td>{{ $rfq->type }}</td></tr>
        <tr><td class="bold">Date of Publication/Issue</td><td>{{ optional($rfq->issue_date)->format('d F, Y') }}</td></tr>
        <tr><td class="bold">Submission Deadline</td><td>{{ optional($rfq->closing_date)->format('d F, Y, h:i A') }}</td></tr>
        <tr><td class="bold">Tender Validity</td><td>{{ $validityDays }} days from the submission deadline</td></tr>
        <tr><td class="bold">Delivery Location</td><td>[Project Office / District — fill in]</td></tr>
        <tr><td class="bold">Expected Delivery Date</td><td>{{ optional($rfq->procurementPlan?->est_delivery_date)->format('d F, Y') ?: '[TBD]' }}</td></tr>
        <tr><td class="bold">Performance Security</td><td>{{ $performanceSecurityPercent }}% of contract value (Works/Goods contracts above threshold)</td></tr>
        <tr><td class="bold">Delay Penalty</td><td>{{ $delayPenaltyPercent }}% of contract value per week of delay, max 10%</td></tr>
    </table>

    <h2>ANNEX-II: PRICE SCHEDULE</h2>
    @forelse ($itemsByCategory as $categoryName => $lines)
        <p class="bold">Category: {{ $categoryName }}</p>
        <table class="bordered">
            <thead>
                <tr>
                    <th style="width:6%">SL</th>
                    <th style="width:44%">Item Details</th>
                    <th style="width:12%">Qty</th>
                    <th style="width:12%">Unit</th>
                    <th style="width:26%">Unit Price (BDT)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lines as $i => $line)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $line->item->name ?? '' }}{{ $line->item->specification ? ': '.$line->item->specification : '' }}</td>
                        <td>{{ $line->quantity }}</td>
                        <td>{{ $line->unit->symbol ?? $line->unit->name ?? '' }}</td>
                        <td></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @empty
        <p>[No linked PR items found — link a Procurement Plan with PR items to auto-fill this table.]</p>
    @endforelse

    <h2>ANNEX-III: TECHNICAL EVALUATION SHEET</h2>
    <p>Total Marks: 100 (Technical: 60, Financial: 40). Minimum 60% required on Technical to qualify for financial evaluation.</p>
    <table class="bordered">
        <thead>
            <tr><th style="width:8%">SL</th><th style="width:72%">Evaluation Criteria</th><th style="width:20%">Marks</th></tr>
        </thead>
        <tbody>
            @foreach ($technicalCriteria as $i => $criterion)
                <tr><td>{{ $i + 1 }}</td><td>{{ $criterion }}</td><td>10</td></tr>
            @endforeach
            <tr><td colspan="2" class="bold">Total</td><td class="bold">60</td></tr>
        </tbody>
    </table>

    <h2>ANNEX-IV: TERMS &amp; CONDITIONS</h2>
    <ol class="terms">
        <li>Bidders must submit valid Trade License, VAT Registration Certificate, TIN Certificate, and Proof of Return Submission (PSR) with the bid.</li>
        <li>Bids must be submitted in a sealed envelope, addressed to the Convener, Central Procurement Committee, ESDO, on or before the submission deadline above.</li>
        <li>Bids received after the deadline will not be accepted under any circumstances.</li>
        <li>Prices quoted must be inclusive of VAT &amp; Tax, and must remain valid for the period stated above.</li>
        <li>The successful bidder will be required to submit a Performance Security as stated above, within 7 days of receiving the Notification of Award.</li>
        <li>ESDO reserves the right to accept or reject any or all bids without assigning any reason.</li>
        <li>Any form of collusion, bribery, or fraudulent practice will result in immediate disqualification and may be reported to the appropriate authorities.</li>
    </ol>
@endsection
