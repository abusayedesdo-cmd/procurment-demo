@extends('documents.layout')

@php
    use App\Services\CommitteeDocumentText as Txt;
@endphp

@section('title', 'Rezulation ' . $meeting->minutes_number)

@section('content')
    <table class="plain" style="margin-bottom:4px">
        <tr>
            <td style="vertical-align:top">
                <b style="font-size:13px">Eco-Social Development Organization (ESDO)</b><br>
                <i>House # 748, Baitul Aman Housing Society, Road # 8, Adabor, Dhaka-1207</i><br>
                <i>Gobindanagar (Collegepara), Thakurgaon-5100</i>
            </td>
        </tr>
    </table>
    <hr class="doc-header-rule">

    <p><b>Rezulation/Minutes Number:</b> <u>{{ optional($meeting->minutes_generated_at)->format('d.m.Y') }}</u>
        [<b>Rezulation/Minutes No: -{{ $meeting->minutes_number }}]</b></p>

    <table class="bordered">
        <tr>
            <td style="width:50%"><b>Meeting Location:</b> <u>{{ $meeting->location ?: 'N/A' }}</u></td>
            <td style="width:50%"><b>Meeting Date &amp; Time:</b> <u>{{ $meeting->meeting_date->format('d F, Y') }}{{ $meeting->meeting_time ? ', '.$meeting->meeting_time : '' }}</u></td>
        </tr>
    </table>

    <p>The meeting was led by <u>{{ $convener->name ?? '[Convener Name]' }}</u>, Convener of the
        Central Procurement Committee, {{ $committeeLocation }}. At the beginning, he welcomed all the
        members and thanked them for joining. After that, he started the meeting officially.</p>

    <h2>Attendence of Procurement Committee Meeting:</h2>
    <table class="bordered">
        <thead>
            <tr>
                <th style="width:10%">Sl. No.</th>
                <th style="width:30%">Name</th>
                <th style="width:25%">Designation</th>
                <th style="width:20%">Signature</th>
                <th style="width:15%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($roster as $i => $member)
                <tr style="height:28px">
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->roleLabel() === 'Convener' ? 'Convener' : ($member->designation ?: $member->roleLabel()) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="text-decoration:underline">Meeting Agenda:</h2>
    <ol class="terms">
        <li>Reading and approval of the minutes of the previous meeting.</li>
        @foreach ($cases as $case)
            <li>Regarding the {{ Txt::agendaLine($case) }}.</li>
        @endforeach
        <li>Miscellaneous.</li>
    </ol>

    <h2 style="text-decoration:underline">Decisions of Today's Meeting:</h2>
    <ol class="terms">
        <li><b>Reading and approval of the minutes of the previous meeting:</b>
            <p>The Member Secretary of the Central Procurement Committee, {{ $committeeLocation }},
                <i>{{ $memberSecretary->name ?? '[Member Secretary Name]' }}</i>, read out the
                rezulation/minutes of the previous meeting. After review, the convener approved the
                rezulation/minutes without any amendment, addition, or deletion.</p>
        </li>

        @foreach ($cases as $case)
            <li><b>Regarding the {{ Txt::agendaLine($case) }}:</b>
                <p>A requisition was submitted to the Central Procurement Committee, {{ $committeeLocation }}
                    for the {{ Txt::verb($case) }} <i>{{ Txt::subCategoryName($case) }}</i> for the
                    <i>{{ Txt::categoryName($case) }}</i> under the <i>{{ Txt::projectName($case) ?? 'N/A' }}</i> Project.</p>

                @if ($case->pivot->agenda_type === 'first')
                    <p>After discussion, all committee members agreed, and the Committee confirmed the
                        following {{ Txt::solicitationLabel($case) }} schedule:</p>
                    <ul class="terms">
                        <li><b>{{ Txt::solicitationLabel($case) }} Publication Date:</b> <u>{{ optional($case->pivot->publish_date)->format('d F, Y') ?? '[to be set after meeting]' }}</u></li>
                        <li><b>Published / Invite / Advertisement To:</b> <u>{{ $case->pivot->publish_channel ?: '[Email/Hand Distribution/BD Jobs/Local Newspaper]' }}</u></li>
                        <li>According to procurement policy, {{ strtolower(Txt::solicitationLabel($case)) }} will be opening
                            <u>{{ $case->pivot->notice_period_days ?? 7 }} days</u> from {{ Txt::solicitationLabel($case) }} published.</li>
                        @if ($case->pivot->special_note)
                            <li><b>Special Note:</b> {{ $case->pivot->special_note }}</li>
                        @endif
                        <li><b>{{ Txt::solicitationLabel($case) }} Submission Deadline:</b>
                            <u>{{ $case->pivot->publish_date ? \Illuminate\Support\Carbon::parse($case->pivot->publish_date)->addDays($case->pivot->notice_period_days ?? 7)->format('d F, Y') : '[auto-generated from policy]' }}</u></li>
                        <li><b>{{ Txt::solicitationLabel($case) }} Opening Time:</b>
                            <u>{{ $case->pivot->publish_date ? \Illuminate\Support\Carbon::parse($case->pivot->publish_date)->addDays($case->pivot->notice_period_days ?? 7)->format('d F, Y') : '[auto-generated from submission deadline]' }}</u></li>
                    </ul>
                    <p>The Committee has agreed that the procurement process will be carried out by
                        selecting the qualified bidder after comparing at least three eligible bids
                        after proper technical and financial evaluation as per the procurement policy
                        of ESDO. The Committee will conduct interviews with the initially selected
                        vendors as required to verify their qualifications, technical expertise and
                        relevant experience.</p>
                @else
                    <p>After reviewing the Comparative Statement of technical and financial evaluation,
                        the Committee approved the recommendation and authorised the Notification of
                        Award (NOA) to proceed for the selected vendor as per the procurement policy of
                        ESDO.</p>
                @endif
            </li>
        @endforeach

        <li><b>Miscellaneous:</b>
            <p>As there were no further issues for discussion, the Central Procurement Committee,
                {{ $committeeLocation }} thanked all members for their active participation and
                declared the meeting adjourned.</p>
        </li>
    </ol>

    <div class="sig-block">
        <p>Approved</p>
        <br><br>
        <p>({{ $convener->name ?? '' }})<br>
        Convener,<br>
        Central Procurement Committee, {{ $committeeLocation }}.</p>
    </div>
@endsection
