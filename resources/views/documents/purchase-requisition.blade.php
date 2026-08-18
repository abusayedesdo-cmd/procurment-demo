@extends('documents.layout')

@section('title', 'Purchase Requisition ' . $pr->pr_number)

@section('content')


    <!--<table class="plain" style="margin-bottom:0;">-->
    <!--    <tr>-->
    <!--        <td style="width:18%; vertical-align:middle; text-align:center;">-->
    <!--            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/esdo-logo.png'))) }}"-->
    <!--                 style="max-width:80px; max-height:80px;">-->
    <!--        </td>-->
    <!--        <td style="width:52%; text-align:center; vertical-align:middle;">-->
    <!--            <div class="bold" style="font-size:14px;">Eso-Social Development Organization (ESDO)</div>-->
    <!--            <div class="bold" style="font-size:12px;">Collegepara(Gobindanagar), Thakurgaon-5100, Bangladesh</div>-->
    <!--            <div class="bold" style="font-size:13px; margin-top:4px;">Purchase Requisition</div>-->
    <!--        </td>-->
    <!--        <td style="width:30%; text-align:right; vertical-align:top;">-->
    <!--            <div>PR NO. {{ $pr->pr_number }}</div>-->
    <!--            <div style="color:#c00;">Date: {{ optional($pr->requisition_date)->format('d.m.Y') }}</div>-->
    <!--        </td>-->
    <!--    </tr>-->
    <!--</table>-->

    <!--<div class="bold" style="margin-top:10px;">Project : {{ $pr->project_name ?? '' }}</div>-->
    <!--<table class="plain" style="margin-top:10px;">-->
    <!--    <tr>-->
    <!--        <td style="width:50%;">Name of Requestor: {{ $pr->requestor_name ?? '' }}</td>-->
    <!--        <td style="width:50%;">Designation: {{ $pr->requestor_designation ?? '' }}</td>-->
    <!--    </tr>-->
    <!--</table>-->
    
        <table class="plain" style="margin-bottom:0;">
        <tr>
            <td style="width:18%; text-align:left; vertical-align:top;">
                @if (file_exists(public_path('img/esdo-logo.png')))
                    <img src="{{ public_path('img/esdo-logo.png') }}" style="max-width:70px; max-height:70px;">
                @endif
            </td>
            <td style="width:52%; text-align:center; vertical-align:top;">
                <div class="bold" style="font-size:14px;">Eso-Social Development Organization (ESDO)</div>
                <div style="font-size:10px;">Collegepara(Gobindanagar), Thakurgaon-5100, Bangladesh</div>
                <div class="bold" style="font-size:13px; margin-top:4px;">Purchase Requisition</div>
            </td>
            <td style="width:30%; text-align:right; vertical-align:top;">
                <div class="bold">PR NO. {{ $pr->pr_number }}</div>
                <div>Date: {{ optional($pr->requisition_date)->format('d.m.Y') }}</div>
            </td>
        </tr>
    </table>
    <hr class="doc-header-rule">

    <table class="plain" style="margin-top:0;">
        <tr>
            <td style="width:34%;"><span class="bold">Project:</span> {{ $pr->project_name ?? '' }}</td>
            <td style="width:33%;"><span class="bold">Requestor:</span> {{ $pr->requestor_name ?? '' }}</td>
            <td style="width:33%;"><span class="bold">Designation:</span> {{ $pr->requestor_designation ?? '' }}</td>
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
                    <!-- <td>{{ $line->unit->name ?? '' }}</td>
                    <td>{{ number_format((float) $line->quantity, 2) }}</td> -->
                    <td>{{ $line->unit->name ?? '' }}</td>
                    <td>{{ (int) $line->quantity }}</td>
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
     <div class="bold" style="margin-top:5px; font-size:12px;">In-word : ৳ {{ $amountInWords }}</div>
    <table class="bordered">
        <tr><td style="width:50%;">Delivery Locations:</td><td>{{ $pr->delivery_location ?? '' }}</td></tr>
        <tr><td>Estimated Delivery Date:</td><td>{{ optional($pr->estimated_delivery_date)->format('d.m.Y') }}</td></tr>
        <tr><td>Estimated Delivery Time:</td><td>{{ $pr->estimated_delivery_time ?? '' }}</td></tr>
        
        <tr><td colspan="2">Receiver Name: {{ $pr->receiver_name ?? '' }}</td></tr>
        <tr><td colspan="2">Receiver Contact: {{ $pr->receiver_contact ?? '' }}</td></tr>
    </table>

    <table class="bordered">
        <tr>
            <td colspan="2" class="bold">Budgetary Check: (by Accounts personnel)</td>
        </tr>
        <tr>
            <td style="width:60%;">
                Total allocated Budget : {{ $budgetCheck ? number_format((float) $budgetCheck->allocated_budget, 2) : '...............................................................................' }}<br><br>
                Remaining Budget B/F : {{ $budgetCheck ? number_format((float) $budgetCheck->remaining_budget_bf, 2) : '..............................................................................' }}<br><br>
                Amount of PR&emsp;&emsp;&emsp;&emsp;&nbsp;: {{ number_format((float) $pr->total_estimated_amount, 2) }}<br><br>
                Remaining Budget C/F: {{ $budgetCheck ? number_format((float) $budgetCheck->remaining_budget_cf, 2) : '..............................................................................' }}<br><br>
                Name of Accountant : {{ $budgetCheck?->checkedBy?->name ?? '..................' }}&emsp;&emsp;Signature.................................
            </td>
            <td style="width:40%; vertical-align:top;">Remarks{{ $budgetCheck?->remarks ? ': '.$budgetCheck->remarks : '' }}</td>
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
            <td>Endorsed by: {{ $endorsedBy?->user?->name ?? '.................' }}</td>
            <td>Designation: {{ $endorsedBy?->user?->designation ?? '.................' }}</td>
            <td>Signature: .................</td>
        </tr>
        <tr><td colspan="3">&nbsp;</td></tr>
        <tr>
            <td>Finance Requested by: {{ $financeRequestedBy?->user?->name ?? '.................' }}</td>
            <td>Designation: {{ $financeRequestedBy?->user?->designation ?? '.................' }}</td>
            <td>Signature: .................</td>
        </tr>
    </table>

    <table class="plain" style="margin-top:30px;">
        <tr>
            <td style="width:50%;">
                Recommend by: {{ $recommendedBy?->user?->name ?? '.................' }}<br>
                PC/DPC/APC/Focal Person
            </td>
            <td style="width:50%;">Approved by: {{ $approvedBy?->user?->name ?? '.................' }}</td>
        </tr>
    </table>
@endsection