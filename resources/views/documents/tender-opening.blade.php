@extends('documents.layout')

@section('title', 'Tender Opening ' . ($rfq->rfq_number ?? ''))

@section('content')
    <h1>TENDER OPENING REPORT</h1>
    <br>
    <p class="bold">RFQ/Tender Reference: {{ $rfq->rfq_number ?? '' }}</p>
    <p>Opening Date: {{ optional($opening->opening_date)->format('d F, Y') }}</p>
    <p>Opened By: {{ $opening->openedBy->name ?? '[Name]' }}</p>

    <h2>Tender Opening Committee</h2>
    <table class="bordered">
        <thead>
            <tr><th style="width:8%">SL</th><th style="width:32%">Name</th><th style="width:32%">Designation</th><th style="width:28%">Signature</th></tr>
        </thead>
        <tbody>
            @forelse ($committee as $i => $member)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $member->user->name ?? '' }}</td>
                    <td>{{ $member->designation_in_committee ?? '' }}</td>
                    <td></td>
                </tr>
            @empty
                <tr><td>1</td><td colspan="3">[No committee members seeded]</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Bidder List &amp; Document Checklist</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:5%">SL</th>
                <th style="width:25%">Bidder / Vendor</th>
                <th style="width:15%">Bid Price (BDT)</th>
                <th style="width:13%">Trade License</th>
                <th style="width:11%">TIN</th>
                <th style="width:11%">BIN</th>
                <th style="width:20%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($quotations as $i => $q)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $q->vendor->name ?? '' }}</td>
                    <td>{{ number_format((float) $q->quoted_amount, 2) }}</td>
                    <td class="center">{{ $checkDoc($q->vendor_id, 'trade_license') }}</td>
                    <td class="center">{{ $checkDoc($q->vendor_id, 'tax_certificate') }}</td>
                    <td class="center">{{ $checkDoc($q->vendor_id, 'vat_certificate') }}</td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="7">[No quotations recorded against this RFQ yet]</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Bidder Representative Attendance</h2>
    <table class="bordered">
        <thead>
            <tr><th style="width:8%">SL</th><th style="width:23%">Vendor</th><th style="width:23%">Representative Name</th><th style="width:23%">Contact No.</th><th style="width:23%">Signature</th></tr>
        </thead>
        <tbody>
            @forelse ($quotations as $i => $q)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $q->vendor->name ?? '' }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @empty
                <tr><td colspan="5">[No bidders to list]</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:20px;">Remarks: {{ $opening->remarks ?: '__________________________________________________' }}</p>
@endsection
