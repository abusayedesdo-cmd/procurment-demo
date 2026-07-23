@extends('layouts.app')
@section('title', 'PR Print Format')
@section('content')

@php
  if (!function_exists('bdWords')) {
  function bdWords($n) {
      if (!$n) return 'Zero';
      $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
      $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
      $two = fn ($x) => $x < 20 ? $ones[$x] : $tens[intdiv($x, 10)] . ($x % 10 ? ' ' . $ones[$x % 10] : '');
      $three = fn ($x) => ($x >= 100 ? $ones[intdiv($x, 100)] . ' Hundred' . ($x % 100 ? ' ' : '') : '') . ($x % 100 ? $two($x % 100) : '');
      $out = '';
      foreach ([['Crore', 10000000], ['Lakh', 100000], ['Thousand', 1000]] as [$word, $div]) {
          $v = intdiv($n, $div); $n %= $div;
          if ($v) $out .= ($div === 10000000 ? $three($v) : $two($v)) . " $word ";
      }
      if ($n) $out .= $three($n);
      return trim($out);
  }
  }
  $total = $pr->total();
@endphp

<div class="no-print" style="display:flex;justify-content:flex-end">
  <button class="btn btn-primary" onclick="window.print()">🖨 Print</button>
</div>

<div class="sheet">
  <div style="display:flex;align-items:center;gap:14px;justify-content:center;text-align:center;border-bottom:2px solid var(--ink);padding-bottom:14px">
    <img src="{{ asset('img/esdo-logo.png') }}" alt="ESDO" style="width:52px;height:52px;object-fit:contain">
    <div>
      <div style="font-size:18px;font-weight:800">Eco-Social Development Organization (ESDO)</div>
      <div style="font-size:12.5px;color:#44465E">748 ESDO House, Road # 8, Dhaka-1207</div>
      <div style="font-size:14px;font-weight:700;margin-top:6px;text-decoration:underline">Purchase Requisition</div>
    </div>
  </div>

  <div style="display:flex;justify-content:space-between;margin-top:14px;font-size:13px">
    <span>PR Sl. No: <b>{{ $pr->pr_no }}</b></span>
    <span>Date: <b>{{ $pr->pr_date->format('d M Y') }}</b></span>
  </div>
  <div style="font-size:13px;margin-top:6px">Name of Program/Project/Unit: <b>{{ $pr->project }}</b></div>
  <div style="display:flex;gap:24px;font-size:13px;margin-top:4px">
    <span>Name of Requestor: <b>{{ $pr->requestor }}</b></span>
    <span>Designation: <b>{{ $pr->designation }}</b></span>
  </div>

  <table style="margin-top:14px">
    <thead><tr><th>Sl. No.</th><th>Particulars</th><th>Unit</th><th>Quantity</th><th>Unit Price</th><th>Estimated Amount</th><th>A/C Code / Remarks</th></tr></thead>
    <tbody>
    @foreach ($pr->items as $i => $item)
      <tr>
        <td style="text-align:center">{{ $i + 1 }}</td>
        <td>{{ $item->name }}</td>
        <td style="text-align:center">{{ $item->unit }}</td>
        <td style="text-align:center">{{ $item->qty + 0 }}</td>
        <td style="text-align:right">{{ number_format($item->rate, 2) }}</td>
        <td style="text-align:right">{{ number_format($item->total(), 2) }}</td>
        <td>{{ $item->ac_code }}</td>
      </tr>
    @endforeach
      <tr>
        <td colspan="5" style="text-align:right;font-weight:800">Total</td>
        <td style="text-align:right;font-weight:800">{{ number_format($total, 2) }}</td>
        <td></td>
      </tr>
    </tbody>
  </table>
  <div style="font-size:12.5px;margin-top:8px">In Words: <b>{{ bdWords((int) round($total)) }} Taka Only</b></div>

  <div style="display:flex;gap:24px;margin-top:16px;flex-wrap:wrap;font-size:12.5px">
    <div style="flex:1;min-width:260px;line-height:2">
      <div style="display:flex;justify-content:space-between;border-bottom:1px dotted var(--faint)"><span>Total allocated Budget</span><b>{{ number_format($pr->allocated_budget, 2) }}</b></div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px dotted var(--faint)"><span>Remaining Budget B/F</span><b>{{ number_format($pr->allocated_budget, 2) }}</b></div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px dotted var(--faint)"><span>Amount of PR</span><b>{{ number_format($total, 2) }}</b></div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px dotted var(--faint)"><span>Remaining Budget C/F</span><b>{{ number_format($pr->allocated_budget - $total, 2) }}</b></div>
      <div style="display:flex;justify-content:space-between;border-bottom:1px dotted var(--faint)"><span>Name of Accountant</span><b>____________________</b></div>
    </div>
    <div style="min-width:200px">
      <div>Date for Estimated Delivery: <b>{{ $pr->delivery_date?->format('d M Y') ?? '____________' }}</b></div>
      <div style="margin-top:6px">Remarks: ______________________</div>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:18px;margin-top:38px">
    @foreach ([['Requested by', $pr->requestor], ['Endorsed by', 'Unit / Project Head'], ['Finance Coordinator', 'Finance & Accounts'], ['Recommend by', 'PC/DPC/APC/Focal Person'], ['Approved by', 'Executive Director']] as [$title, $sub])
      <div style="text-align:center;font-size:12px">
        <div style="border-top:1px solid var(--ink);padding-top:6px;font-weight:700">{{ $title }}</div>
        <div style="color:#44465E;margin-top:2px">{{ $sub }}</div>
      </div>
    @endforeach
  </div>
</div>
@endsection
