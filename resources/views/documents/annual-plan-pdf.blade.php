<!DOCTYPE html>
<html>
<head>
<style>
    @page { margin: 8px 10px; }
    body { font-family: sans-serif; font-size: 7px; }
    table { border-collapse: collapse; width: 100%; table-layout: fixed; }
    th, td { border: 1px solid #333; padding: 1px 2px; text-align: center; word-wrap: break-word; overflow-wrap: break-word; }
    h2, p { text-align: center; margin: 2px 0; }
</style>
</head>
<body>

    <!-- Header Section -->

    <div style="width: 100%; position: relative; margin-bottom: 12px; min-height: 50px;">
    @if (file_exists(public_path('img/esdo-logo.png')))
        <img src="{{ public_path('img/esdo-logo.png') }}" style="position: absolute; left: 0; top: 0; height: 50px; width: auto;">
    @endif
        <div style="text-align: center; width: 100%;">
            <div style="font-size: 16px; font-weight: bold; line-height: 1.2;">Eco-Social Development Organization (ESDO)</div>
            <div style="font-size: 10px; color: #555; margin-top: 2px;">Collegepara(Gobindanagar),Thakurgaon, Rangpur, Bangladesh</div>
        </div>
    </div>

    <!-- Project Info Section -->
    <table style="border:none; margin: 4px auto 10px; width:auto;">
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Name/Title</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ $plan->project_name ?? $plan->title }}</td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Location (Office)</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ $plan->project_location }}</td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Working Area</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ $plan->working_area }}</td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Project Duration</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ $plan->project_duration ?? (optional($plan->fiscal_year_start)->format('d M Y') . ' to ' . optional($plan->fiscal_year_end)->format('d M Y')) }}</td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Date of Agreement/Awarded</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ optional($plan->agreement_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Donor Name</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ $plan->donor_name }}</td>
        </tr>
        <tr>
            <td style="border:none; text-align:right; padding:1px 6px 1px 0; font-weight:bold; white-space:nowrap;">Activity Summary</td>
            <td style="border:none; padding:1px 0;">:</td>
            <td style="border:none; text-align:left; padding:1px 0 1px 6px; white-space:nowrap;">{{ $plan->activity_summary }}</td>
        </tr>
    </table>

    <!-- Data Table Section -->
    <table>
        @php
            $totalSubCols = collect($layout)->sum(fn ($g) => count($g['sublabels']) * 3);
            $subColWidth = $totalSubCols > 0 ? round(59.5 / $totalSubCols, 3) : 0;
        @endphp
        <colgroup>
            <col style="width:1.5%;"><col style="width:5%;"><col style="width:6%;"><col style="width:6%;"><col style="width:6%;"><col style="width:3%;">
            @foreach ($layout as $group)
                @for ($i = 0; $i < count($group['sublabels']) * 3; $i++)
                    <col style="width:{{ $subColWidth }}%;">
                @endfor
            @endforeach
            <col style="width:4%;"><col style="width:4%;"><col style="width:5%;">
        </colgroup>
        <thead>
            <tr>
                <th rowspan="3">Sl</th><th rowspan="3">Category</th><th rowspan="3">Sub Category</th><th rowspan="3">Item Name</th><th rowspan="3">Specification</th><th rowspan="3">Unit</th>
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
                        <th>Unit</th><th>Rate</th><th>Total</th>
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
                    <td>{{ $pkg->sl_no ?? $loop->iteration }}</td>
                    <td>{{ $pkg->category->name }}</td>
                    <td>{{ $pkg->chartOfAccount->name ?? $pkg->item?->chartOfAccount?->name ?? '' }}</td>
                    <td>{{ $pkg->budgeted_head }}</td>
                    <td>{{ $pkg->specification }}</td>
                    <td>{{ $pkg->unit }}</td>
                    @foreach ($layout as $group)
                        @foreach ($pkg->alignedValuesFor($group['key'], $group['sublabels']) as $i => $v)
                            <td>{{ $v['no_of_unit'] ? number_format($v['no_of_unit'], 0) : '' }}</td>
                            <td>{{ $v['rate'] ? number_format($v['rate'], 2) : '' }}</td>
                            <td>{{ $v['total'] ? number_format($v['total'], 2) : '' }}</td>
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
                    <td colspan="6">Total</td>
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