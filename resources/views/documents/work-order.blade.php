@extends('documents.layout')

@section('title', 'Work Order ' . ($memo ?? ''))

@section('content')
@php
    $fd = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v)->format('d F, Y') : '';
    $money = fn ($v) => $v === null ? '' : number_format((float) $v, 2);
    $qty = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    $quoted = $rfqDate ? 'on ' . $fd($rfqDate) : 'earlier';
    $where = $deliveryPlaces ? ' for ' . implode(', ', $deliveryPlaces) : '';
    $by = $deadline ? ' by ' . $fd($deadline) : ' within the time agreed';
@endphp
    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    @if (file_exists(public_path('img/esdo-logo.png')))
        <img src="{{ public_path('img/esdo-logo.png') }}" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    @endif
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;">{{ $committeeAddress }}</div>
        </div>
    </div>

    <table class="plain" style="margin-bottom:4px"><tr>
        <td class="bold">Memo: {{ $memo }}</td>
        <td class="bold" style="text-align:right">Date: {{ $fd($date) }}</td>
    </tr></table>

    <h1>WORK ORDER</h1>
    <br>
    <p>To,<br><span class="bold">{{ $vendorName }}</span>@if ($vendorAddress)<br>{{ $vendorAddress }}@endif</p>
    <p>Dear Sir/Madam,</p>
    <p style="text-align:justify">
        Eco-Social Development Organization (ESDO) invited quotations {{ $quoted }} from interested genuine businesses/suppliers for the supply of
        {{ $subject }}{{ $where }}. As per the quotations received, your organization is the lowest evaluated bidder, so this Work Order is being issued to you.
        You are requested to supply the items below{{ $by }}, according to the description and terms &amp; conditions given here.
    </p>

    <table class="bordered">
        <thead>
            <tr><th style="width:7%">Sl. No.</th><th>Description</th><th style="width:13%">Total Qty.</th><th style="width:16%">Unit Price</th><th style="width:18%">Total Price</th></tr>
        </thead>
        <tbody>
            @forelse ($lines as $i => $l)
                <tr>
                    <td style="text-align:center">{{ $i + 1 }}</td>
                    <td>{!! nl2br(e($l['description'])) !!}</td>
                    <td style="text-align:center">{{ $qty($l['qty']) }}{{ $l['unit'] ? ' ' . $l['unit'] : '' }}</td>
                    <td style="text-align:right">{{ $money($l['unit_price']) }}</td>
                    <td style="text-align:right">{{ $money($l['amount']) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No item recorded.</td></tr>
            @endforelse
            <tr>
                <td colspan="4" class="bold">Total price including VAT &amp; Tax (BDT)</td>
                <td style="text-align:right" class="bold">{{ $money($total) }}</td>
            </tr>
        </tbody>
    </table>
    <p class="bold">In words: {{ $amountWords }}</p>

    <h2>Terms &amp; Conditions applicable:</h2>
    <ol class="terms">
        @foreach ($terms as $term)
            <li>{{ $term }}</li>
        @endforeach
    </ol>

    @if ($distribution)
        <p style="margin-top:10px">The items must be delivered to the locations below according to these quantities:</p>
        <table class="bordered">
            <thead>
                <tr><th style="width:6%">Sl.</th><th>Location</th>@foreach ($distribution['items'] as $n)<th>{{ strtok($n, "\n") }}</th>@endforeach<th>Total</th></tr>
            </thead>
            <tbody>
                @foreach ($distribution['places'] as $i => $p)
                    @php $sum = 0; @endphp
                    <tr>
                        <td style="text-align:center">{{ $i + 1 }}</td><td>{{ $p }}</td>
                        @foreach ($distribution['items'] as $n)
                            @php $v = $distribution['grid'][$p][$n] ?? 0; $sum += $v; @endphp
                            <td style="text-align:center">{{ $v ? $qty($v) : '-' }}</td>
                        @endforeach
                        <td style="text-align:center" class="bold">{{ $qty($sum) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Address for delivery of the items:</h2>
    <table class="bordered">
        <tr><td>
            @if ($receiverName)<span class="bold">{{ $receiverName }}</span><br>@endif
            @if ($deliveryLocation){{ $deliveryLocation }}@elseif ($deliveryPlaces){{ implode('; ', $deliveryPlaces) }}@endif
            @if ($receiverContact)<br>Mobile: {{ $receiverContact }}@endif
        </td></tr>
    </table>

    <table class="plain sig-block">
        <tr>
            <td style="width:55%">
                <span class="bold">({{ $signerName ?: '[Name]' }})</span><br>
                  {{ $signerLine }}<br>
                ESDO, {{ $committeeAddress }}
            </td>
            <td style="width:45%; text-align:right">Vendor/Recipient: signature of consent &amp; seal</td>
        </tr>
    </table>
@endsection
