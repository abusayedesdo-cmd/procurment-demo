@extends('documents.layout')

@section('title', 'RFQ ' . $rfq->rfq_number)

@section('content')
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    @if (file_exists(public_path('img/esdo-logo.png')))
        <img src="{{ public_path('img/esdo-logo.png') }}" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    @endif
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 15px; font-weight: bold; margin-top: 2px;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 13px; font-weight: bold; line-height: 1.3;">{{ $committeeAddress }}</div>
            <!-- <div style="font-size: 13px; font-weight: bold; line-height: 1.3;">Gobindanagar (Collegepara), Thakurgaon-5100</div> -->
            
        </div>
    </div>

    <table class="plain" style="margin-bottom:8px;">
        <tr>
            <td class="bold" style="text-align:left;">Memo: {{ $rfq->rfq_number }}</td>
            <td class="bold" style="text-align:right;">Date: {{ optional($rfq->issue_date)->format('d F, Y') }}</td>
        </tr>
    </table>

    <h1>Request for Quotation (RFQ)</h1>
    <br>

    <p>
        Eco-Social Development Organization (ESDO) is hereby requesting quotation as per following
        <strong>{{ $rfq->procurementCase->purchaseRequisition->category->name ?? '[Category Name]' }}</strong> vendors/suppliers for supplying
        <strong>{{ $rfq->procurementCase->purchaseRequisition->category->name ?? '[Category Name]' }}</strong>
        equipment under "<strong>{{ $rfq->subject ?: '[Project Name]' }}</strong>" in
        <strong>{{ $rfq->procurementCase->purchaseRequisition->delivery_location ?? '[Project Location]' }}</strong>. Interested Vendors/Suppliers are requested to submit
        quotation through courier or directly according to the below mentioned terms &amp; conditions by
        or before on {{ optional($rfq->closing_date)->format('d F, Y') }} at 04:00 PM addressing to
        "Convener, {{ $committeeName }}, Eco-Social Development Organization (ESDO),
        {{ $committeeAddress }}".
    </p>

    <p class="bold">Items Details:</p>

    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%">Sl. No.</th>
                <th style="width:16%">Item Name</th>
                <th style="width:29%">Item Specification</th>
                <th style="width:8%">Unit</th>
                <th style="width:7%">Qty.</th>
                <th style="width:16%">Unit Price</th>
                <th style="width:19%">Total Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->name ?? '' }}</td>
                    <td>{{ $line->specification }}</td>
                    <td>{{ $line->unit->symbol ?? $line->unit->name ?? '' }}</td>
                    <td>{{ $line->quantity }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @empty
                <tr><td>1</td><td>[No linked PR items found]</td><td></td><td></td><td></td><td></td><td></td></tr>
            @endforelse
            <tr>
                <td colspan="5" class="bold">Total Amount with VAT &amp; Tax</td>
                <td></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <h2>Terms &amp; Conditions</h2>
    @php
        // Same rule as the Word builder: the terms the officer ticked on the RFQ form;
        // RFQs with nothing ticked (created before the feature) keep the old standard list.
        $fallbackTerms = \App\Models\Rfq::FALLBACK_TERMS;
        $pickedTerms = ($termsConditions ?? collect())->isNotEmpty()
            ? $termsConditions->pluck('text')->all()
            : $fallbackTerms;
        // "Additional Terms (optional, free text)": one extra numbered term per non-empty line.
        $extraTerms = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $rfq->terms_conditions))));
    @endphp
    <ol class="terms">
        <li>Quotation will be Opened on {{ optional($rfq->closing_date)->format('d F, Y') ?: '[date]' }} at 04:00 PM (Those who will submit the quotation are invited to present at the opening time).</li>
        <li>Delivery Location: equipment must be delivered to "[Delivery Schedule]" office in {{ $rfq->procurementCase->purchaseRequisition->delivery_location ?? '[Project Location]' }}.</li>
        @foreach (array_merge($pickedTerms, $extraTerms) as $term)
            <li>{{ $term }}</li>
        @endforeach
    </ol>

    <p style="margin-top:20px;">Thanks, with best regards</p>

    <table class="plain" style="margin-top:30px;">
        <tr>
            <td style="width:50%; vertical-align:top;">
                <p class="bold">({{ $signatoryName }})</p>
                <p>{{ $signatoryTitle }}</p>
                <p>{{ $committeeName }}, ESDO, {{ $committeeAddress }}.</p>
            </td>
            <td style="width:50%; vertical-align:top; text-align:right;">
                <p>Receivers Signature &amp; Seal</p>
            </td>
        </tr>
    </table>
@endsection