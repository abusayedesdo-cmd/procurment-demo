@extends('layouts.app')

@section('title', 'Procurement Annual Plans')

@section('content')
    <div id="errorBox" class="error-box" style="display:none;"></div>

    <div class="card" style="margin-bottom:1rem;">
        <h3>Create New Plan</h3>

        <div class="row">
            <div>
                <label for="w_project_name">Project Name</label>
                <input type="text" id="w_project_name" placeholder="e.g. FRR Noakhali Project">
            </div>
            <div>
                <label for="w_district">District</label>
                <select id="w_district">
                    <option value="">Select District</option>
                </select>
            </div>
            <div>
                <label for="w_upazila">Upazila</label>
                <select id="w_upazila">
                    <option value="">Select Upazila</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="w_project_location">Project Location (Office)</label>
                <input type="text" id="w_project_location">
            </div>
            <div>
                <label for="w_working_area">Project Working Area</label>
                <input type="text" id="w_working_area">
            </div>
        </div>

        <div class="row">
            <div>
                <label for="w_agreement_date">Date of Agreement/Awarded (Start Date)</label>
                <input type="date" id="w_agreement_date">
            </div>
            <div>
                <label for="w_fy_start">Fiscal Year Start (Previous 2nd Year begins)</label>
                <input type="date" id="w_fy_start" oninput="updateDurationPreview()">
            </div>
            <div>
                <label for="w_fy_end">Fiscal Year End</label>
                <input type="date" id="w_fy_end" oninput="updateDurationPreview()">
            </div>
        </div>
        <p class="muted" style="margin-top:.25rem;">Project Duration (auto-calculated): <strong id="durationPreview">-</strong></p>

        <div class="row">
            <div style="flex:1;">
                <label for="w_activity_summary">Activity Summary (within 3 sentences)</label>
                <textarea id="w_activity_summary" rows="3"></textarea>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="w_donor_name">Donor Name</label>
                <input type="text" id="w_donor_name" placeholder="e.g. UNICEF">
            </div>
            <div>
                <label for="w_funding_source">Funding Source</label>
                <input type="text" id="w_funding_source">
            </div>
            <div>
                <label for="w_plan_type">Plan Type</label>
                <select id="w_plan_type">
                    <option value="annual" selected>Annual</option>
                    <option value="project">Project</option>
                </select>
            </div>
        </div>

        <div style="margin-top:1rem;">
            <button class="btn" onclick="createPlan()">Create Plan</button>
        </div>
    </div>

    <div id="plansList">Loading...</div>
@endsection

@section('scripts')
<script>
    const errorBox = document.getElementById('errorBox');

    function calcDuration(startStr, endStr) {
        if (!startStr || !endStr) return '-';
        const start = new Date(startStr);
        const end = new Date(endStr);
        if (end <= start) return '-';

        let months = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
        if (end.getDate() < start.getDate()) months--;

        const years = Math.floor(months / 12);
        const remMonths = months % 12;
        const parts = [];
        if (years > 0) parts.push(`${years} year${years > 1 ? 's' : ''}`);
        if (remMonths > 0) parts.push(`${remMonths} month${remMonths > 1 ? 's' : ''}`);

        return parts.length ? parts.join(' ') : '0 months';
    }

    function updateDurationPreview() {
        const start = document.getElementById('w_fy_start').value;
        const end = document.getElementById('w_fy_end').value;
        document.getElementById('durationPreview').textContent = calcDuration(start, end);
    }

    function validatePlan(data) {
        if (!data.project_name) return 'Please provide Project Name.';
        if (!data.fiscal_year_start || !data.fiscal_year_end) return 'Please provide Fiscal Year Start and End.';
        if (data.fiscal_year_end <= data.fiscal_year_start) return 'Fiscal Year End must be after Start.';
        return null;
    }

    async function createPlan() {
        errorBox.style.display = 'none';

        const data = {
            project_name: document.getElementById('w_project_name').value.trim(),
            district_id: document.getElementById('w_district').value || null,
            upazila_id: document.getElementById('w_upazila').value || null,
            project_location: document.getElementById('w_project_location').value.trim(),
            working_area: document.getElementById('w_working_area').value.trim(),
            agreement_date: document.getElementById('w_agreement_date').value,
            fiscal_year_start: document.getElementById('w_fy_start').value,
            fiscal_year_end: document.getElementById('w_fy_end').value,
            activity_summary: document.getElementById('w_activity_summary').value.trim(),
            donor_name: document.getElementById('w_donor_name').value.trim(),
            funding_source: document.getElementById('w_funding_source').value.trim(),
            plan_type: document.getElementById('w_plan_type').value,
        };

        const err = validatePlan(data);
        if (err) {
            errorBox.textContent = err;
            errorBox.style.display = 'block';
            return;
        }

        try {
            const { data: created } = await api.post('/procurement-annual-plans', {
                plan_type: data.plan_type,
                title: data.project_name, // no separate Title field — reuses Project Name
                project_name: data.project_name || null,
                district_id: data.district_id || null,
                upazila_id: data.upazila_id || null,
                project_location: data.project_location || null,
                working_area: data.working_area || null,
                activity_summary: data.activity_summary || null,
                fiscal_year_start: data.fiscal_year_start,
                fiscal_year_end: data.fiscal_year_end,
                project_duration: calcDuration(data.fiscal_year_start, data.fiscal_year_end),
                agreement_date: data.agreement_date || null,
                donor_name: data.donor_name || null,
                funding_source: data.funding_source || null,
            });
            window.location.href = `/annual-plans/${created.id}`;
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    async function loadPlans() {
        try {
            const { data } = await api.get('/procurement-annual-plans');
            document.getElementById('plansList').innerHTML = data.map(p => `
                <div class="card row" style="align-items:center;">
                    <div><strong>${p.title}</strong><br><span class="muted">${p.plan_type} · ${p.donor_name ?? ''} · ${p.district?.name ?? ''}${p.upazila ? ', ' + p.upazila.name : ''} · ${p.fiscal_year_start} → ${p.fiscal_year_end}</span></div>
                    <div><span class="badge ${p.status}">${p.status}</span></div>
                    <div><a class="btn secondary" href="/annual-plans/${p.id}">Open</a></div>
                </div>
            `).join('') || '<div class="card muted">No plans yet.</div>';
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    let allUpazilas = [];

    function populateUpazilaOptions(districtId) {
        const select = document.getElementById('w_upazila');
        select.innerHTML = '<option value="">Select Upazila</option>';
        const filtered = districtId
            ? allUpazilas.filter(u => String(u.district_id) === String(districtId))
            : allUpazilas;
        filtered.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.name;
            select.appendChild(opt);
        });
    }

    async function loadDistricts() {
        try {
            const { data } = await api.get('/procurement-districts');
            const select = document.getElementById('w_district');
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                select.appendChild(opt);
            });
            select.addEventListener('change', () => populateUpazilaOptions(select.value));
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    async function loadUpazilas() {
        try {
            const { data } = await api.get('/procurement-upazilas');
            allUpazilas = data;
            populateUpazilaOptions(document.getElementById('w_district').value);
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    loadPlans();
    loadDistricts();
    loadUpazilas();
</script>
@endsection