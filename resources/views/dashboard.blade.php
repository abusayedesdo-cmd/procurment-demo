<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>ESDO Procurement — Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: system-ui, sans-serif; background: #f5f6fa; margin: 0; padding: 2rem; color: #1f2430; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
        .card { background: #fff; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .card h3 { margin: 0 0 .5rem; font-size: .85rem; color: #6b7280; text-transform: uppercase; letter-spacing: .04em; }
        .card p { margin: 0; font-size: 1.75rem; font-weight: 700; }
        .card a { text-decoration: none; color: inherit; display: block; }
        .actions { margin-top: 2rem; display: flex; gap: .75rem; flex-wrap: wrap; }
        .btn { display: inline-block; background: #1f2937; color: #fff; border: none; border-radius: 6px; padding: .6rem 1.1rem; cursor: pointer; text-decoration: none; font-size: .9rem; }
        .btn.secondary { background: #e5e7eb; color: #1f2937; }
        .note { margin-top: 2rem; font-size: .9rem; color: #6b7280; }
        form { display: inline; }
        button.logout { background: none; border: none; color: #b91c1c; cursor: pointer; font-size: .9rem; }
    </style>
</head>
<body>
    @php $canSeeModules = in_array($user->roleName() ?? null, [\App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN]); @endphp

    <div class="header">
        <div>
            <h1>ESDO Procurement</h1>
            <p>স্বাগতম, {{ $user->name ?? '' }} <span style="color:#6b7280; font-size:.85rem;">({{ $user->roleLabel() ?? '' }})</span></p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout">লগ-আউট</button>
        </form>
    </div>

    <div class="card-grid">
        <div class="card"><a href="{{ route('purchase-requisitions.index') }}?status=draft"><h3>Draft PR</h3><p>{{ $draftPrs }}</p></a></div>
        <div class="card"><a href="{{ route('purchase-requisitions.index') }}"><h3>Pending Review/Check</h3><p>{{ $pendingPrs }}</p></a></div>
        <div class="card"><a href="{{ route('purchase-requisitions.index') }}?status=approved"><h3>Approved PR</h3><p>{{ $approvedPrs }}</p></a></div>

        @if ($canSeeModules)
            <div class="card"><a href="{{ route('modules.show', 'procurement-plans') }}"><h3>Active Procurement Plans</h3><p>{{ $activePlans }}</p></a></div>
            <div class="card"><a href="{{ route('modules.show', 'contract-awards') }}"><h3>Contracts Awarded</h3><p>{{ $contractsAwarded }}</p></a></div>
        @else
            <div class="card"><h3>Active Procurement Plans</h3><p>{{ $activePlans }}</p></div>
            <div class="card"><h3>Contracts Awarded</h3><p>{{ $contractsAwarded }}</p></div>
        @endif
    </div>

    <div class="actions">
        <a href="{{ route('purchase-requisitions.index') }}" class="btn">Purchase Requisitions দেখুন</a>
        @if ($user && $user->roleName() === \App\Models\User::REQUESTER)
            <a href="{{ route('purchase-requisitions.create') }}" class="btn">+ নতুন PR তৈরি করুন</a>
        @endif
        @if ($canSeeModules)
            <a href="{{ route('modules.index') }}" class="btn secondary">সব মডিউল দেখুন (Plan, RFQ, Meeting, Evaluation, Contract...)</a>
        @endif
    </div>

    <p class="note">
        Procurement Plan থেকে Contract Award/Work Order/Delivery Receipt পর্যন্ত পুরো process এখন Procurement Officer-এর "সব মডিউল" থেকে পরিচালিত হয়।
    </p>
</body>
</html>
