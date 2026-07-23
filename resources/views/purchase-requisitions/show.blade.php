@extends('layouts.app')

@section('title', 'PR বিস্তারিত')

@section('content')
    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="prDetail">লোড হচ্ছে...</div>
@endsection

@section('scripts')
<script>
    const prId = {{ (int) $id }};
    const errorBox = document.getElementById('errorBox');
    const detail = document.getElementById('prDetail');

    function badge(status) {
        return `<span class="badge ${status}">${status}</span>`;
    }

    async function load() {
        try {
            const { data: pr } = await api.get(`/purchase-requisitions/${prId}`);
            render(pr);
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
                <div><span class="muted">Requisition Date</span><br>${pr.requisition_date}</div>
                <div><span class="muted">Est. Delivery</span><br>${pr.estimated_delivery_date ?? '-'}</div>
                <div><span class="muted">Total (৳)</span><br><strong>${Number(pr.total_estimated_amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</strong></div>
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

            ${pr.status !== 'approved' && pr.status !== 'rejected' ? `
            <div class="card">
                <h3>Action নিন</h3>
                <div class="row">
                    <div>
                        <label for="roleAtAction">আপনার ভূমিকা (এই action-এ)</label>
                        <input type="text" id="roleAtAction" placeholder="যেমন: Reviewer, Budget Checker, Approver">
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
            ` : ''}
        `;
    }

    async function submitAction(action) {
        errorBox.style.display = 'none';
        const roleAtAction = document.getElementById('roleAtAction').value;
        if (!roleAtAction) {
            errorBox.textContent = 'আপনার ভূমিকা লিখুন (Reviewer/Budget Checker/Approver)।';
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

    load();
</script>
@endsection
