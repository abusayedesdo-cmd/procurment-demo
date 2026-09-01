@extends('documents.layout')

@section('title', 'Tender Schedule ' . $rfq->rfq_number)

@section('content')
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    @if (file_exists(public_path('img/esdo-logo.png')))
        <img src="{{ public_path('img/esdo-logo.png') }}" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    @endif
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;">Collegepara(Gobindanagar),Thakurgaon, Rangpur, Bangladesh</div>
        </div>
    </div>

    <h1>{{ $case?->natureLabel() ?? 'Tender Schedule' }}</h1>
    <p class="center">(Tender Document/Schedule — {{ $rfq->type }})</p>

    <p><span class="bold">Description of Works:</span> {{ $rfq->subject ?: '[Description of Works]' }}</p>

    <table class="bordered" style="margin-top:6px">
        <tr><td class="bold">DATE:</td><td>{{ optional($rfq->issue_date)->format('d.m.Y') }}</td></tr>
        <tr><td class="bold">REFERENCE:</td><td>{{ $rfq->rfq_number }}</td></tr>
        <tr><td class="bold">Address:</td><td>Collegepara (Gobindanagar), Thakurgaon-5100</td></tr>
    </table>

    <p style="margin-top:10px">
        To<br>
        Bidder Name: .............................................................<br>
        Address: .....................................................................
    </p>

    <p>Dear Respected Bidder,</p>

    <p>
        The Eco-Social Development Organization (ESDO) is hereby requesting you to submit your bid proposal
        of <strong>{{ $rfq->subject ?: '[Description of Works]' }}</strong> as per the annexes of this Tender Document.
    </p>

    <p>
        Tender must be submitted on or before <strong>{{ optional($rfq->closing_date)->format('d.m.Y') }}; {{ optional($rfq->closing_date)->format('h:i A') }}</strong>
        via courier/post office or directly to the address below:
    </p>
    <p class="center bold" style="margin:4px 0">
        {{ $convenerName }}, Central Procurement Committee<br>
        Eco-Social Development Organization (ESDO)<br>
        Collegepara (Gobindanagar), Thakurgaon-5100
    </p>

    <p>
        Tender should be submitted in a sealed envelope marked <strong>"Quotation for {{ $rfq->subject ?: '[Description of Works]' }}"</strong>.
    </p>

    <p>
        It shall remain your responsibility to ensure that your tender reaches the address above on or before the
        deadline. Tenders received by ESDO after the deadline indicated above, for whatever reason, shall not be
        considered for evaluation.
    </p>

    <p class="bold" style="margin-top:10px">Please take note of the following requirements and conditions:</p>

    <table class="bordered">
        <tr><td class="bold" style="width:35%">Exact Address of Delivery Locations</td><td>As per Annex-I{{ $pr?->delivery_location ? ' — ' . $pr->delivery_location : '' }}</td></tr>
        <tr><td class="bold">Latest Expected Delivery Date and Time</td><td>{{ optional($pr?->procurementPlan?->est_delivery_date)->format('d F, Y') ?: '[TBD]' }}</td></tr>
        <tr><td class="bold">Packing Requirements</td><td>Secure, safe packing as necessary to avoid any damage or defects.</td></tr>
        <tr><td class="bold">Preferred Currency of Tender</td><td>Local Currency: BDT (Taka)</td></tr>
        <tr><td class="bold">Value Added Tax on Tender Price</td><td>Must be inclusive of Tax and other applicable indirect taxes</td></tr>
        <tr><td class="bold">After-sales Services</td><td>Replace the sub-standard items within possible short time. Any defect in manufacture will not be accepted.</td></tr>
        <tr><td class="bold">Deadline for the Submission of Tender</td>
            <td>{{ optional($rfq->closing_date)->format('d.m.Y') }} (Those who submit the tender are invited to present at the time of tender opening). Opening Time: {{ optional($rfq->closing_date)->format('h:i A') }}</td></tr>
        <tr><td class="bold">Price Tender / Bill / Invoice Language</td><td>English (Technical Specification and other correspondence from/to Suppliers may be in Bangla).</td></tr>
        <tr>
            <td class="bold">Documents to be Submitted for Eligibility Criteria</td>
            <td>
                Bidders must have legal capacity to enter the Contract. In support of its qualification, the bidder must submit:
                <ul style="margin:4px 0 0 16px; padding:0">
                    @foreach ($eligibilityDocuments as $doc)
                        <li>{{ $doc }}</li>
                    @endforeach
                </ul>
                Failure to submit the above shall result in disqualification.
            </td>
        </tr>
        <tr><td class="bold">Period of Validity of Quotes starting the Submission Date</td><td>{{ $validityDays }} days from the submission deadline</td></tr>
        <tr><td class="bold">Partial Bid</td><td>Not Permitted.</td></tr>
        <tr><td class="bold">Payment Terms</td><td>Payment will be made after satisfactory delivery as per Terms and Conditions.</td></tr>
        <tr><td class="bold">Performance Security</td><td>Selected vendor should deposit {{ $performanceSecurityPercent }}% of the total awarded amount in the form of a pay order. The Performance Security will be returned to the supplier after successful completion of the contract, 90 (ninety) days after award.</td></tr>
        <tr><td class="bold">Liquidated Damages</td><td>{{ $delayPenaltyPercent }}% per week on the total value of delayed delivery. In case the delay is more than 1 (one) week without any approval, the goods Order/PO might be cancelled.</td></tr>
        <tr><td class="bold">Evaluation Criteria</td><td>Full compliance with eligibility requirements, technical responsiveness, lowest price and goodwill; full acceptance of the Purchase Order (PO)/Terms and Conditions of the Contract; and Bid Validity (see Annex-III for the detailed evaluation sheet, where applicable).</td></tr>
        <tr><td class="bold">Procuring Entity will Award to</td><td>One Supplier.</td></tr>
        <tr><td class="bold">Type of Contract to be Signed</td><td>Purchase Order (PO) / Another Type(s) of Contract, as applicable.</td></tr>
        <tr><td class="bold">Special Conditions of Contract</td><td>Poor quality/unacceptable delivery and failure to make necessary corrections/replacements as requested by the procuring entity will result in cancellation of the PO.</td></tr>
        <tr><td class="bold">Conditions for Release of Payment</td><td>Written acceptance of goods based on full compliance with PO/Contract requirements after agreed delivery and (where applicable) successful installation at the delivery point.</td></tr>
        <tr>
            <td class="bold">Annexes to this Tender Document</td>
            <td>
                <ul style="margin:4px 0 0 16px; padding:0">
                    <li>Annex-I: Address of Delivery Locations</li>
                    <li>Annex-II: Price Schedule for Goods and Related Services</li>
                    <li>Annex-III: Description/Specifications and Rate Sheet</li>
                    <li>Annex-IV: Terms and Conditions for Supply of Goods and Payment</li>
                    <li>Annex-V: Tender Submission Letter</li>
                    <li>Annex-VI: Contract Agreement</li>
                </ul>
            </td>
        </tr>
        <tr>
            <td class="bold">Contact Person for Inquiries (Written inquiries only)</td>
            <td>
                {{ $signatoryName }}<br>
                {{ $signatoryTitle }}, Central Procurement Committee<br>
                Collegepara (Gobindanagar), Thakurgaon-5100<br>
                Email: {{ $signatoryEmail ?: '[email not on file]' }}
            </td>
        </tr>
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

    <table class="plain" style="margin-top:30px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                <p class="bold">Sincerely yours,</p>
                <p>({{ $signatoryName }})</p>
                <p>{{ $signatoryTitle }}</p>
                <p>Central Procurement Committee, ESDO</p>
            </td>
        </tr>
    </table>
@endsection