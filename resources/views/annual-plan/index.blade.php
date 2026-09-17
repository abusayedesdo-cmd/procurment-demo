@extends('layouts.app')

@section('title', 'Procurement Annual Plans')

@section('content')
    <div id="errorBox" class="error-box" style="display:none;"></div>

    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h3 style="margin:0;">Annual Plans</h3>
        <a class="btn" id="createPlanLink" href="{{ route('annual-plans.create') }}">Create New Plan</a>
    </div>

    <div id="plansList">Loading...</div>
@endsection

@section('scripts')
<script>
    const errorBox = document.getElementById('errorBox');

    async function loadPlans() {
        try {
            const { data } = await api.get('/procurement-annual-plans');
            document.getElementById('plansList').innerHTML = data.map(p => `
                <div class="card row" style="align-items:center;">
                    <div><strong>${p.title}</strong><br><span class="muted">${p.plan_type} · ${p.donor_name ?? ''} · ${p.district?.name ?? ''}${p.upazila ? ', ' + p.upazila.name : ''} · ${formatDateTime(p.fiscal_year_start)} → ${formatDateTime(p.fiscal_year_end)}</span></div>
                    <div><span class="badge ${p.status}">${p.status}</span></div>
                    <div><a class="btn secondary" href="/annual-plans/${p.id}">Open</a></div>
                </div>
            `).join('') || '<div class="card muted">No plans yet.</div>';
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    function formatDateTime(iso) {
    if (!iso) return '';
    return iso.replace('T', ' ').split('.')[0];
    }

    loadPlans();

    // Only Admin/Budget Checker can create plans — hide the button for
    // everyone else instead of sending them to a form they can't use.
    (function gateCreateLink() {
        const canManage = window.currentUserRole === 'admin' || window.currentUserRole === 'budget_checker';
        if (canManage) return;
        document.getElementById('createPlanLink').style.display = 'none';
    })();
</script>
@endsection