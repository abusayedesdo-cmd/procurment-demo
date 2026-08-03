@extends('documents.layout')

@section('title', 'Purchase Requisition ' . $pr->pr_number)

@section('content')
    <table class="plain" style="margin-bottom:0;">
        <tr>
            <td style="width:70%; text-align:center;">
                <div class="bold" style="font-size:14px;">Eso-Social Development Organization (ESDO)</div>
                <div>Collegepara(Gobindanagar), Thakurgaon(5100), Bangladesh</div>
                <div class="bold" style="font-size:13px; margin-top:4px;">Purchase Requisition</div>
            </td>
            <td style="width:30%; text-align:right; vertical-align:top;">
                <div>PR NO. {{ $pr->pr_number }}</div>
                <div style="color:#c00;">Date: {{ optional($pr->requisition_date)->format('d.m.Y') }}</div>
            </td>
        </tr>
    </table>

    <div>Project : {{ $pr->project_name ?? '' }}</div>
    <table class="plain" style="margin-top:10px;">
        <tr>
            <td style="width:50%;">Name of Requestor: {{ $pr->requestor_name ?? '' }}</td>
            <td style="width:50%;">Designation: {{ $pr->requestor_designation ?? '' }}</td>
        </tr>
    </table>

    <table class="bordered" style="margin-top:8px;">
        <thead>
            <tr>
                <th style="width:5%;">Sl.No</th>
                <th style="width:20%;">Item Name</th>
                <th style="width:24%;">Specification</th>
                <th style="width:8%;">Unit</th>
                <th style="width:9%;">Quantity</th>
                <th style="width:11%;">Unit Price</th>
                <th style="width:11%;">Total Price</th>
                <th style="width:12%;">A/C Code/Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pr->items as $i => $line)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $line->item->name ?? '' }}</td>
                    <td>{{ $line->specification ?? $line->item->specification ?? '' }}</td>
                    <td>{{ $line->unit->name ?? '' }}</td>
                    <td>{{ number_format((float) $line->quantity, 2) }}</td>
                    <td>{{ number_format((float) $line->rate_bdt, 2) }}</td>
                    <td>{{ number_format((float) $line->total_amount, 2) }}</td>
                    <td>{{ $line->ac_code ?? '' }}</td>
                </tr>
            @endforeach
            @for ($i = count($pr->items); $i < 6; $i++)
                <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
            @endfor
            <tr>
                <td colspan="6" style="text-align:right;" class="bold">Total Tk</td>
                <td class="bold">{{ number_format((float) $pr->total_estimated_amount, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
     <div in word style="margin-top:5px; font-size:12px;">In-word : ৳ {{ $amountInWords }}</div>
    <table class="bordered">
        <tr><td style="width:50%;">Delivery Locations: (Mandatory)</td><td>{{ $pr->delivery_location ?? '' }}</td></tr>
        <tr><td>Estimated Delivery Date: (Mandatory)</td><td>{{ optional($pr->estimated_delivery_date)->format('d.m.Y') }}</td></tr>
        <tr><td>Estimated Delivery Time: (Optional)</td><td>{{ $pr->estimated_delivery_time ?? '' }}</td></tr>
        
        <tr><td colspan="2">Receiver Name: {{ $pr->receiver_name ?? '' }}</td></tr>
        <tr><td colspan="2">Receiver Contact: {{ $pr->receiver_contact ?? '' }}</td></tr>
    </table>

    <table class="bordered">
        <tr>
            <td colspan="2" class="bold">Budgetary Check: (by Accounts personnel)</td>
        </tr>
        <tr>
            <td style="width:60%;">
                Total allocated Budget : ...............................................................................<br><br>
                Remaining Budget B/F : ..............................................................................<br><br>
                Amount of PR&emsp;&emsp;&emsp;&emsp;&nbsp;: {{ number_format((float) $pr->total_estimated_amount, 2) }}<br><br>
                Remaining Budget C/F: ..............................................................................<br><br>
                Name of Accountant : ..................&emsp;&emsp;Signature.................................
            </td>
            <td style="width:40%; vertical-align:top;">Remarks</td>
        </tr>
    </table>

    <table class="plain sig-block">
        <tr>
            <td style="width:34%;">Requested by: {{ $pr->requestor_name ?? '.................' }}</td>
            <td style="width:33%;">Designation: {{ $pr->requestor_designation ?? '.................' }}</td>
            <td style="width:33%;">Signature: .................</td>
        </tr>
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
            <td>Endorsed by: .................</td>
            <td>Designation: .................</td>
            <td>Signature: .................</td>
        </tr>
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
            <td>Finance Requested by: .................</td>
            <td>Designation: .................</td>
            <td>Signature: .................</td>
        </tr>
    </table>

    <table class="plain" style="margin-top:30px;">
        <tr>
            <td style="width:50%;">
                Recommend by:<br>
                PC/DPC/APC/Focal Person
            </td>
            <td style="width:50%;">Approved by</td>
        </tr>
    </table>
@endsection
