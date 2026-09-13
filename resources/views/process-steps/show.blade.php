@extends('layouts.app')

@section('title', $step['subject'])

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

    :root {
        --ink: #0F172A;
        --paper: #FFFFFF;
        --surface: #F8FAFC;
        --line: #E2E8F0;
        --muted: #64748B;
        --accent: #0D9488;
        --accent-dark: #0F766E;
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .shell { max-width: 1080px; margin: 0 auto; padding: 2.5rem 2rem 4rem; }

    .page-header { padding-bottom: 1.75rem; margin-bottom: 2rem; border-bottom: 1px solid var(--line); display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }

    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 0 0 .5rem;
    }

    .page-header h1 { margin: 0; font-size: 1.5rem; font-weight: 700; letter-spacing: -0.01em; color: var(--ink); }

    .back-link {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: .5rem .9rem;
        font-size: .82rem;
        font-weight: 600;
        color: var(--ink);
        text-decoration: none;
    }
    .back-link:hover { border-color: var(--accent); color: var(--accent-dark); }

    .group-panel { background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 1.35rem 1.5rem; }

    .module-list { display: flex; flex-direction: column; }

    .module-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem .2rem;
        border-bottom: 1px solid var(--line);
        text-decoration: none;
        color: var(--ink);
        font-size: .9rem;
        font-weight: 600;
        transition: background .15s ease, padding-left .15s ease, color .15s ease;
    }
    .module-list .module-row:last-child { border-bottom: none; }
    .module-row:hover { background: var(--surface); padding-left: .6rem; color: var(--accent-dark); }
    .module-row .chevron { color: var(--muted); font-weight: 400; flex-shrink: 0; transition: transform .15s ease, color .15s ease; }
    .module-row:hover .chevron { color: var(--accent); transform: translateX(3px); }

    .coming-soon { text-align: center; padding: 3rem 1rem; color: var(--muted); font-size: .9rem; }

    .case-hint { font-size: .82rem; color: var(--muted); margin-bottom: 1rem; }
    .case-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: .9rem; }
    .case-card {
        display: flex; flex-direction: column; gap: .55rem;
        background: var(--paper); border: 1px solid var(--line); border-radius: 10px;
        padding: 1rem 1.1rem; text-decoration: none; color: inherit;
        transition: border-color .15s ease, transform .15s ease;
    }
    .case-card:hover { border-color: var(--accent); transform: translateY(-1px); }
    .case-card .top-row { display: flex; align-items: center; gap: .4rem; font-size: .7rem; font-weight: 700; color: var(--muted); }
    .case-card .ref { margin-left: auto; }
    .case-card .title { font-size: .92rem; font-weight: 700; color: var(--ink); }
    .case-card .amount { font-size: .78rem; color: var(--muted); }
    .case-card .progress-track { height: 5px; background: var(--surface); border-radius: 999px; overflow: hidden; }
    .case-card .progress-fill { height: 100%; background: var(--accent); }
    .case-card .step-label { font-size: .72rem; font-weight: 700; color: var(--accent-dark); }
    .case-card .now { font-size: .78rem; font-weight: 600; color: var(--ink); }

        .action-menu { position: relative; display: inline-block; }
    .action-menu .action-btn {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 7px;
        padding: .4rem .75rem;
        font-size: .8rem;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer;
        font-family: inherit;
    }
    .action-menu .action-btn:hover { border-color: #CBD5E1; background: var(--surface); }
    .action-menu .action-dropdown {
        display: none;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .12);
        min-width: 240px;
        z-index: 20;
        overflow: hidden;
        text-align: left;
    }
    .action-menu.open .action-dropdown { display: block; }
    .action-dropdown a {
        display: block;
        padding: .6rem .85rem;
        font-size: .82rem;
        color: var(--ink);
        text-decoration: none;
        border-bottom: 1px solid var(--line);
    }
    .action-dropdown a:last-child { border-bottom: none; }
    .action-dropdown a:hover { background: var(--surface); color: var(--accent-dark); }
    .no-cases { text-align: center; padding: 2rem 1rem; color: var(--muted); font-size: .88rem; }
    .no-cases a { color: var(--accent-dark); font-weight: 600; }

    .missing-plan-notice {
        display: flex; flex-direction: column; gap: .6rem;
        background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE;
        border-radius: 10px; padding: 1rem 1.2rem; margin-bottom: 1.25rem; font-size: .88rem;
    }
    .missing-plan-notice a {
        align-self: flex-start; background: #1D4ED8; color: #fff; text-decoration: none;
        font-weight: 600; padding: .5rem .95rem; border-radius: 7px; font-size: .84rem;
    }

    .active-pr-banner {
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        background: var(--surface); border: 1px solid var(--line); border-radius: 10px;
        padding: .8rem 1.1rem; margin-bottom: 1.25rem; font-size: .86rem; color: var(--ink);
    }
    .active-pr-banner a { color: var(--muted); font-weight: 600; font-size: .8rem; text-decoration: none; }
    .active-pr-banner a:hover { color: var(--accent-dark); }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
        .page-header { flex-direction: column; }
    }
</style>
@endsection

@section('content')
    <div class="shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Step {{ $step['step_no'] }}</p>
                <h1>{{ $step['subject'] }}</h1>
            </div>
            <div style="display:flex; align-items:center; gap:.6rem; flex-shrink:0;">
                <a href="{{ url()->previous() ?: route('dashboard') }}"
                   onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }"
                   class="back-link">&larr; Back</a>
            </div>
        </div>

        @isset($activePr)
            <div class="active-pr-banner">
                <span>Working on <b>PR-{{ $activePr->pr_number ?? $activePr->id }}</b> — every step below stays scoped to this PR.</span>
                <a href="{{ route('process-steps.show', $slug) }}?clear_pr=1">Change / clear &times;</a>
            </div>
        @endisset

        @isset($missingPlanForPr)
            <div class="missing-plan-notice">
                <div>
                    <b>PR-{{ $missingPlanForPr->pr_number ?? $missingPlanForPr->id }}</b>-এর জন্য এখনো কোনো Procurement Plan তৈরি হয়নি — Sub-Committee-তে ট্রান্সফার করার আগে প্রথমে একটা Procurement Plan লাগবে।
                </div>
                <a href="{{ route('modules.show', 'procurement-plans') }}?new=1&field_pr_id={{ $missingPlanForPr->id }}&context_label=PR-{{ $missingPlanForPr->pr_number ?? $missingPlanForPr->id }}">
                    Create Procurement Plan for PR-{{ $missingPlanForPr->pr_number ?? $missingPlanForPr->id }} &rarr;
                </a>
                <div style="font-size:.8rem; opacity:.85;">Plan তৈরি হয়ে গেলে Purchase Requisitions লিস্টে ফিরে গিয়ে আবার "Transfer to Sub-Committee (3rd Step)" চাপুন — তখন এটা এখানে auto-select হয়ে আসবে।</div>
            </div>
        @endisset

        @if (!empty($step['coming_soon']))
            <div class="group-panel">
                <div class="coming-soon">This step's module is coming soon.</div>
            </div>
        @elseif (!empty($step['is_pr_picker']))
            <p class="case-hint">একটা Approved PR বেছে নিন — এরপর Process Steps-এর প্রতিটা ধাপ শুধু এই PR নিয়েই কাজ করবে।</p>
            @if ($prReceiveList->isEmpty())
                <div class="group-panel">
                    <div class="no-cases">No approved PR is waiting to be picked up right now.</div>
                </div>
            @else
                <div class="group-panel">
                    <div class="module-list">
                        @foreach ($prReceiveList as $pr)
                            <div class="module-row" style="cursor:default;">
                                <span>{{ $pr->pr_number ?? ('PR-' . $pr->id) }} — ৳ {{ number_format($pr->total_estimated_amount ?? 0, 2) }}</span>
                                <div class="action-menu">
                                    <button type="button" class="action-btn">Action &#9662;</button>
                                <div class="action-dropdown">
                                    <a href="{{ route('process-steps.show', 'sub-committee') }}?pr_id={{ $pr->id }}">Transfer to Sub-Committee</a>
                                    <a href="{{ route('process-steps.show', 'meeting-notice') }}?pr_id={{ $pr->id }}">1st Meeting Notice</a>
                                 
                                    <a href="{{ route('process-steps.show', 'rfq') }}?pr_id={{ $pr->id }}">RFQ </a>
                                    <a href="{{ route('process-steps.show', 'rfp-rfi') }}?pr_id={{ $pr->id }}">RFP/RFI/Hiring Vendor/Consultant </a>
                                    <a href="{{ route('process-steps.show', 'tender-otm') }}?pr_id={{ $pr->id }}">Tender/OTM/Press Tender/STD </a>
                                </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @elseif (isset($cases))
            <p class="case-hint">Pick a case below to record this step directly on it.</p>
            @if ($cases->isEmpty())
                <div class="group-panel">
                   <div class="no-cases">No procurement cases yet. <a href="{{ route('cases.create', $activePr ? ['pr_id' => $activePr->id] : []) }}">Open a new case</a> to get started.</div>
                </div>
            @else
                <div class="case-grid">
                    @foreach ($cases as $case)
                        @php
                            // Meeting-step pages jump straight into that step's own
                            // form — the case is only listed here because it's
                            // actually ready for this specific action.
                            $firstMeeting = $case->relationLoaded('meetings') ? $case->meetings->first() : null;
                            $cardUrl = match ($slug) {
                                'meeting-notice' => route('meetings.notice.create', [$case, 'first']),
                                'meeting-attendance' => $firstMeeting ? route('meetings.attendance.create', $firstMeeting) : route('cases.show', $case),
                                'meeting-resolution' => $firstMeeting ? route('meetings.resolution.create', $firstMeeting) : route('cases.show', $case),
                                default => route('cases.show', $case),
                            };
                        @endphp
                        <a href="{{ $cardUrl }}" class="case-card">
                            <div class="top-row">
                                <span>{{ $case->method }}</span>
                                <span>&middot;</span>
                                <span>{{ $case->category }}</span>
                                <span class="ref">{{ $case->ref }}</span>
                            </div>
                            <div class="title">{{ $case->title }}</div>
                            <div class="amount">৳ {{ number_format($case->amount, 2) }}</div>
                        </a>
                    @endforeach
                </div>
            @endif
        @else
            <div class="group-panel">
                <div class="module-list">
                    @foreach ($step['modules'] as $m)
                        @php
                            $moduleUrl = isset($m['route']) ? route($m['route']) : route('modules.show', $m['slug']);
                            $prefillField = $prefillFieldBySlug[$m['slug'] ?? null] ?? null;
                            $prefillValue = match ($prefillField) {
                                'pr_id' => $activePr?->id,
                                'procurement_plan_id' => $planId,
                                'procurement_case_id' => $caseId,
                                'rfq_id' => $rfqId,
                                default => null,
                            };
                        if ($prefillField && $prefillValue) {
                            $moduleUrl .= '?new=1&field_' . $prefillField . '=' . $prefillValue;
                            if ($activePr) {
                                $moduleUrl .= '&context_label=' . urlencode($activePr->pr_number ?? ('PR-' . $activePr->id));
                            }
                            // Sub-Committee Transfer's From/To committee auto-fill.
                            if (($m['slug'] ?? null) === 'sub-committee-transfers') {
                                if ($fromCommitteeId) {
                                    $moduleUrl .= '&field_from_committee_id=' . $fromCommitteeId;
                                }
                                if ($toCommitteeId) {
                                    $moduleUrl .= '&field_to_committee_id=' . $toCommitteeId;
                                }
                            }
                        }
                        @endphp
                        <a class="module-row" href="{{ $moduleUrl }}">
                            <span>{{ $m['title'] }}</span>
                            <span class="chevron">&rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.action-btn');
        const menu = e.target.closest('.action-menu');
        if (btn && menu && btn === menu.querySelector('.action-btn')) {
            const wasOpen = menu.classList.contains('open');
            document.querySelectorAll('.action-menu.open').forEach(m => m.classList.remove('open'));
            if (!wasOpen) menu.classList.add('open');
            return;
        }
        if (!e.target.closest('.action-menu')) {
            document.querySelectorAll('.action-menu.open').forEach(m => m.classList.remove('open'));
        }
    });
</script>
@endsection