<!DOCTYPE html>
<html>
<head>
<style>
    body { font-family: sans-serif; font-size: 8px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #333; padding: 2px; text-align: center; }
    h2, p { text-align: center; margin: 2px 0; }
</style>
</head>
<body>


    <!-- <div style="text-align:center; margin-bottom:8px;">
        @if (file_exists(public_path('img/esdo-logo.png')))
            <img src="{{ public_path('img/esdo-logo.png') }}" style="height:60px; width:auto; display:block; margin:0 auto 4px;">
        @endif
        <div style="font-size:14px; font-weight:bold;">Eco-Social Development Organization (ESDO)</div>
        <div style="font-size:9px; color:#555;">www.esdo.net.bd</div>
    </div> -->
    <div style="width: 100%; margin-bottom: 8px;">
        <table style="width: 100%; border-collapse: collapse; border: none;">
            <tr>
                @if (file_exists(public_path('img/esdo-logo.png')))
                    <td style="width: 70px; vertical-align: middle; border: none; padding: 0;">
                        <img src="{{ public_path('img/esdo-logo.png') }}" style="height: 50px; width: auto; display: block;">
                    </td>
                @endif
                <td style="vertical-align: middle; padding-left: 10px; border: none;">
                    <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
                    <div style="font-size: 10px; color: #555; margin-top: 2px;">www.esdo.net.bd</div>
                </td>
            </tr>
        </table>
    </div>

    <h2>{{ $plan->title }}</h2>
    <p style="text-align:center;"><strong>Project Name/Title:</strong> {{ $plan->project_name ?? $plan->title }}</p>
    <p style="text-align:center;"><strong>Project Location (Office):</strong> {{ $plan->project_location }} </p> 
    <p style="text-align:center;"><strong>Project Working Area:</strong> {{ $plan->working_area }}</p>
    <p style="text-align:center;"><strong>Project Duration:</strong> {{ $plan->project_duration ?? (optional($plan->fiscal_year_start)->format('d M Y') . ' to ' . optional($plan->fiscal_year_end)->format('d M Y')) }} </p>
    <p style="text-align:center;"><strong>Date of Agreement/Awarded:</strong> {{ optional($plan->agreement_date)->format('d M Y') }}</p>
    <p style="text-align:center;"><strong>Donor Name:</strong> {{ $plan->donor_name }}</p>
    <p style="text-align:center;"><strong>Activity Summary:</strong> {{ $plan->activity_summary }}</p>
    <p style="margin-bottom:10px;"></p>

 <table>
        <thead>
            <tr>
                <th rowspan="3">Sl.No</th><th rowspan="3">Category</th><th rowspan="3">Item Name</th><th rowspan="3">Specification</th><th rowspan="3">Unit</th>
                @foreach ($layout as $group)
                    <th colspan="{{ count($group['sublabels']) * 3 }}">{{ $group['title'] }}</th>
                @endforeach
                <th rowspan="3">Already Procured</th>
                <th rowspan="3">Remaining Balance</th>
                <th rowspan="3">Remarks</th>
            </tr>
            <tr>
                @foreach ($layout as $group)
                    @foreach ($group['sublabels'] as $sub)
                        <th colspan="3">{{ $sub }}</th>
                    @endforeach
                @endforeach
            </tr>
            <tr>
                @foreach ($layout as $group)
                    @foreach ($group['sublabels'] as $sub)
                        <th>No. of Unit</th><th>Rate</th><th>Total</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $groupSums = [];
                foreach ($layout as $g) {
                    $groupSums[$g['key']] = array_fill(0, count($g['sublabels']), 0.0);
                }
                $alreadyProcuredSum = 0;
                $remainingBalanceSum = 0;
            @endphp
            @foreach ($plan->packages as $pkg)
                <tr>
                    <td>{{ $pkg->sl_no }}</td>
                    <td>{{ $pkg->category->name }}</td>
                    <td>{{ $pkg->budgeted_head }}</td>
                    <td>{{ $pkg->specification }}</td>
                    <td>{{ $pkg->unit }}</td>
                    @foreach ($layout as $group)
                        @foreach ($pkg->alignedValuesFor($group['key'], $group['sublabels']) as $i => $v)
                            <td>{{ $v['no_of_unit'] !== null ? number_format($v['no_of_unit'], 2) : '-' }}</td>
                            <td>{{ $v['rate'] !== null ? number_format($v['rate'], 2) : '-' }}</td>
                            <td>{{ $v['total'] !== null ? number_format($v['total'], 2) : '-' }}</td>
                            @php $groupSums[$group['key']][$i] += $v['total'] ?? 0; @endphp
                        @endforeach
                    @endforeach
                    <td>{{ number_format($pkg->already_procured, 2) }}</td>
                    <td>{{ number_format($pkg->remaining_balance, 2) }}</td>
                    <td>{{ $pkg->remarks }}</td>
                </tr>
                @php
                    $alreadyProcuredSum += $pkg->already_procured;
                    $remainingBalanceSum += $pkg->remaining_balance;
                @endphp
            @endforeach
            @if ($plan->packages->count())
                <tr style="font-weight:bold; background:#f0f0f0;">
                    <td colspan="5">Total</td>
                    @foreach ($layout as $group)
                        @foreach ($groupSums[$group['key']] as $sum)
                            <td></td><td></td><td>{{ number_format($sum, 2) }}</td>
                        @endforeach
                    @endforeach
                    <td>{{ number_format($alreadyProcuredSum, 2) }}</td>
                    <td>{{ number_format($remainingBalanceSum, 2) }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
</body>
</html>