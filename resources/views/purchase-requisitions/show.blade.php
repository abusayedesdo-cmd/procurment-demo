@extends('layouts.app')

@section('title', 'PR বিস্তারিত')

@section('content')
    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="prDetail">লোড হচ্ছে...</div>
@endsection

@section('scripts')
<script>
    const prId = {{ (int) $id }};
    const currentUserRole = @json(auth()->user()->roleName());
    const errorBox = document.getElementById('errorBox');
    const detail = document.getElementById('prDetail');

    function badge(status) {
        return `<span class="badge ${status}">${status}</span>`;
    }

    function money(n) {
        return Number(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
    }

    function isBudgetCheckStage(pr) {
        return pr.window_type === 'PR' && pr.status === 'reviewed'
            && ['budget_checker', 'admin'].includes(currentUserRole);
    }

    async function load() {
        try {
            const { data: pr } = await api.get(`/purchase-requisitions/${prId}`);
            render(pr);
            renderActionArea(pr);
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    function render(pr) {
        const itemsRows = (pr.items || []).map(li => `
            <tr>
                <td>${li.serial_no}</td>
                <td>${li.item?.name ?? ''}</td>
                <td>${li.unit?.name ?? ''}</td>
                <td>${Number(li.quantity).toLocaleString()}</td>
                <td>${Number(li.rate_bdt).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                <td>${Number(li.total_amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
            </tr>
        `).join('');

        const approvalRows = (pr.approvals || []).map(a => `
            <tr>
                <td>${a.acted_at}</td>
                <td>${a.user?.name ?? ''}</td>
                <td>${a.role_at_action}</td>
                <td>${badge(a.action === 'approved' ? 'approved' : (a.action === 'rejected' ? 'rejected' : 'draft'))} ${a.action}</td>
                <td>${a.remarks ?? ''}</td>
            </tr>
        `).join('') || '<tr><td colspan="5" class="muted">কোনো action নেই এখনও।</td></tr>';

        detail.innerHTML = `
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h1 style="margin:0;">${pr.pr_number} ${badge(pr.status)}</h1>
                <a href="{{ route('purchase-requisitions.index') }}" class="btn secondary">← তালিকায় ফিরুন</a>
            </div>

            <div class="card row">
                <div><span class="muted">Window</span><br>${pr.window_type}</div>
                <div><span class="muted">Category</span><br>${pr.category?.name ?? '-'}</div>
                <div><span class="muted">Budget Line</span><br>${pr.budget_line ? pr.budget_line.item_code + ' — ' + pr.budget_line.item_name : '<span class="muted">এখনো ঠিক করা হয়নি</span>'}</div>
                <div><span class="muted">Annual Plan Package</span><br>${pr.package ? pr.package.package_number + ' — ' + pr.package.budgeted_head : '<span class="muted">লিংক করা নেই</span>'}</div>
                <div><span class="muted">Requisition Date</span><br>${pr.requisition_date}</div>
                <div><span class="muted">Est. Delivery</span><br>${pr.estimated_delivery_date ?? '-'}</div>
                <div><span class="muted">Total (৳)</span><br><strong>${money(pr.total_estimated_amount)}</strong></div>
                <div><span class="muted">Raised By</span><br>${pr.raised_by_user?.name ?? pr.raisedBy?.name ?? '-'}</div>
            </div>

            <div class="card">
                <h3>আইটেম সমূহ</h3>
                <table>
                    <thead><tr><th>#</th><th>Item</th><th>Unit</th><th>Qty</th><th>Rate</th><th>Total</th></tr></thead>
                    <tbody>${itemsRows}</tbody>
                </table>
            </div>

            <div class="card">
                <h3>Approval History</h3>
                <table>
                    <thead><tr><th>Date</th><th>User</th><th>Role</th><th>Action</th><th>Remarks</th></tr></thead>
                    <tbody>${approvalRows}</tbody>
                </table>
            </div>

            <div id="actionArea"></div>
        `;
    }

    async function renderActionArea(pr) {
        const area = document.getElementById('actionArea');

        if (pr.status === 'approved' || pr.status === 'rejected') {
            area.innerHTML = '';
            return;
        }

        if (isBudgetCheckStage(pr)) {
            await renderBudgetCheckCard(pr, area);
        } else {
            renderGenericActionCard(area);
        }
    }

    async function renderBudgetCheckCard(pr, area) {
        area.innerHTML = `<div class="card">Budget তথ্য লোড হচ্ছে...</div>`;

        let budgetLines = [];
        let check;
        try {
            const results = await Promise.all([
                api.get(`/purchase-requisitions/${prId}/budget-check`),
                pr.budget_line ? Promise.resolve(null) : api.get('/budget-lines'),
            ]);
            check = results[0];
            if (results[1]) budgetLines = results[1].data;
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            return;
        }

        const line = check.data.budget_line;

        const lineOptions = budgetLines.map(l => `
            <option value="${l.id}">${l.code} — ${l.name} (${l.category}) · balance ৳ ${money(l.balance)}</option>
        `).join('');

        area.innerHTML = `
            <div class="card">
                <h3>Budget Verification (Module 1.4)</h3>
                ${!line ? `
                    <label for="budgetLineSelect">Budget Line বাছাই করুন</label>
                    <select id="budgetLineSelect">
                        <option value="">-- বাছাই করুন --</option>
                        ${lineOptions}
                    </select>
                    <div id="liveBalance" class="muted" style="margin-top:.5rem;"></div>
                ` : `
                    <div class="row">
                        <div><span class="muted">Budget Code</span><br><strong>${line.code}</strong></div>
                        <div><span class="muted">Approved Budget</span><br>৳ ${money(line.approved_budget)}</div>
                        <div><span class="muted">Spent so far</span><br>৳ ${money(line.spent)}</div>
                        <div><span class="muted">Available Balance</span><br><strong style="color:${line.is_sufficient ? '#065f46' : '#991b1b'}">৳ ${money(line.available_budget_amount)}</strong></div>
                    </div>
                    <p class="muted" style="margin-top:.5rem;">
                        PR amount ৳ ${money(pr.total_estimated_amount)} —
                        ${line.is_sufficient ? '<b style="color:#065f46;">Sufficient balance</b>' : '<b style="color:#991b1b;">Insufficient balance</b>'}
                    </p>
                `}

                <div class="form-field checkbox-field" style="margin-top:1rem;">
                    <label><input type="checkbox" id="codeVerified"> Budget code verified</label>
                </div>
                <div class="form-field checkbox-field">
                    <label><input type="checkbox" id="availabilityVerified"> Budget availability confirmed</label>
                </div>

                <label for="budgetRemarks" style="margin-top:.75rem;">মন্তব্য</label>
                <textarea id="budgetRemarks" rows="2"></textarea>

                <div style="margin-top:1rem; display:flex; gap:.5rem;">
                    <button class="btn" onclick="submitBudgetCheck('recommended')">Recommend for Approval</button>
                    <button class="btn secondary" onclick="submitBudgetCheck('returned')">Return for Correction</button>
                </div>
            </div>
        `;

        if (!line) {
            document.getElementById('budgetLineSelect').addEventListener('change', (e) => {
                const id = e.target.value;
                const liveBalance = document.getElementById('liveBalance');
                if (!id) { liveBalance.textContent = ''; return; }
                const picked = budgetLines.find(l => String(l.id) === id);
                liveBalance.textContent = picked ? `Balance: ৳ ${money(picked.balance)}` : '';
            });
        }
    }

    function renderGenericActionCard(area) {
        area.innerHTML = `
            <div class="card">
                <h3>Action নিন</h3>
                <div class="row">
                    <div>
                        <label for="roleAtAction">আপনার ভূমিকা (এই action-এ)</label>
                        <input type="text" id="roleAtAction" placeholder="যেমন: Reviewer, Approver">
                    </div>
                </div>
                <label for="actionRemarks">মন্তব্য</label>
                <textarea id="actionRemarks" rows="2"></textarea>
                <div style="margin-top:1rem; display:flex; gap:.5rem;">
                    <button class="btn" onclick="submitAction('approved')">Approve</button>
                    <button class="btn secondary" onclick="submitAction('returned')">Return</button>
                    <button class="btn danger" onclick="submitAction('rejected')">Reject</button>
                </div>
            </div>
        `;
    }

    async function submitAction(action) {
        errorBox.style.display = 'none';
        const roleAtAction = document.getElementById('roleAtAction').value;
        if (!roleAtAction) {
            errorBox.textContent = 'আপনার ভূমিকা লিখুন (Reviewer/Approver)।';
            errorBox.style.display = 'block';
            return;
        }

        try {
            await api.post(`/purchase-requisitions/${prId}/approvals`, {
                action,
                role_at_action: roleAtAction,
                remarks: document.getElementById('actionRemarks').value || null,
            });
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    async function submitBudgetCheck(decision) {
        errorBox.style.display = 'none';
        const select = document.getElementById('budgetLineSelect');
        const budgetLineId = select ? (select.value || null) : null;

        if (select && !budgetLineId) {
            errorBox.textContent = 'একটা Budget Line বাছাই করুন।';
            errorBox.style.display = 'block';
            return;
        }

        try {
            await api.post(`/purchase-requisitions/${prId}/budget-check`, {
                budget_line_id: budgetLineId,
                is_budget_code_verified: document.getElementById('codeVerified').checked,
                is_budget_available: document.getElementById('availabilityVerified').checked,
                decision,
                remarks: document.getElementById('budgetRemarks').value || null,
            });
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    load();
</script>
@endsection
