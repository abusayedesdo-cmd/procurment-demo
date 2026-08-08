<?php $__env->startSection('title', 'PR বিস্তারিত'); ?>

<?php $__env->startSection('content'); ?>
    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="prDetail">লোড হচ্ছে...</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const prId = <?php echo e((int) $id); ?>;
    const currentUserRole = <?php echo json_encode(auth()->user()->roleName(), 15, 512) ?>;
    const errorBox = document.getElementById('errorBox');
    const detail = document.getElementById('prDetail');

    function badge(status) {
        return `<span class="badge ${status}">${status}</span>`;
    }

    function money(n) {
        return Number(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
    }

    function amountInWords(n) {
        const num = Math.round(Number(n || 0));
        if (num === 0) return 'Zero Taka Only';

        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function twoDigits(x) {
            if (x < 20) return ones[x];
            return tens[Math.floor(x / 10)] + (x % 10 ? ' ' + ones[x % 10] : '');
        }
        function threeDigits(x) {
            if (x < 100) return twoDigits(x);
            return ones[Math.floor(x / 100)] + ' Hundred' + (x % 100 ? ' ' + twoDigits(x % 100) : '');
        }

        // Bangladeshi grouping: Crore (1,00,00,000) / Lakh (1,00,000) / Thousand / Hundred
        let rem = num;
        const crore = Math.floor(rem / 10000000); rem %= 10000000;
        const lakh = Math.floor(rem / 100000); rem %= 100000;
        const thousand = Math.floor(rem / 1000); rem %= 1000;
        const hundred = rem;

        const parts = [];
        if (crore) parts.push(threeDigits(crore) + ' Crore');
        if (lakh) parts.push(threeDigits(lakh) + ' Lakh');
        if (thousand) parts.push(threeDigits(thousand) + ' Thousand');
        if (hundred) parts.push(threeDigits(hundred));

        return parts.join(' ') + ' Taka Only';
    }

    function isBudgetCheckStage(pr) {
        return pr.window_type === 'PR' && pr.status === 'reviewed'
            && ['budget_checker', 'admin'].includes(currentUserRole);
    }

    function isReviewStage(pr) {
        return pr.status === 'draft'
            && ['reviewer', 'admin'].includes(currentUserRole);
    }

    function isApproverStage(pr) {
        if (['approver', 'admin'].includes(currentUserRole) === false) return false;
        // PR window: Reviewer -> Budget Checker -> Approver (checked -> approved)
        // Other windows: Reviewer -> Approver directly (reviewed -> approved)
        return pr.window_type === 'PR' ? pr.status === 'checked' : pr.status === 'reviewed';
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
                <td>${li.specification ?? li.item?.specification ?? '-'}</td>
                <td>${li.unit?.name ?? ''}</td>
                <td>${Number(li.quantity).toLocaleString()}</td>
                <td>${Number(li.rate_bdt).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                <td>${Number(li.total_amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                <td>${li.ac_code ?? '-'}</td>
                <td>${li.is_fixed_asset ? 'হ্যাঁ' : 'না'}</td>
            </tr>
        `).join('');

        const classificationRows = (pr.items || []).map(li => `
            <tr>
                <td>${pr.category?.name ?? '-'}</td>
                <td>${li.item?.chart_of_account?.name ?? '-'}</td>
                <td>${li.is_fixed_asset ? 'হ্যাঁ' : 'না'}</td>
                <td>${li.item?.name ?? ''}</td>
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
                <div style="display:flex; gap:.5rem;">
                    <a href="/api/purchase-requisitions/${pr.id}/pdf" class="btn secondary" target="_blank">PDF ডাউনলোড করুন</a>
                    <a href="<?php echo e(route('purchase-requisitions.index')); ?>" class="btn secondary">← তালিকায় ফিরুন</a>
                </div>
            </div>

            <div class="card row">
                <div><span class="muted">Window</span><br>${pr.window_type}</div>
                <div><span class="muted">Category</span><br>${pr.category?.name ?? '-'}</div>
                <div><span class="muted">Project/Program Name</span><br>${pr.project_name ?? '-'}</div>
                <div><span class="muted">Budget Line</span><br>${pr.budget_line ? pr.budget_line.item_code + ' — ' + pr.budget_line.item_name : '<span class="muted">এখনো ঠিক করা হয়নি</span>'}</div>
                <div><span class="muted">Annual Plan Package</span><br>${pr.package ? pr.package.package_number + ' — ' + pr.package.budgeted_head : '<span class="muted">লিংক করা নেই</span>'}</div>
                <div><span class="muted">Requisition Date</span><br>${pr.requisition_date}</div>
                <div><span class="muted">Est. Delivery Date</span><br>${pr.estimated_delivery_date ?? '-'}</div>
                <div><span class="muted">Est. Delivery Time</span><br>${pr.estimated_delivery_time ?? '-'}</div>
                <div><span class="muted">Delivery Location</span><br>${pr.delivery_location ?? '-'}</div>
                <div><span class="muted">Total (৳)</span><br><strong>${money(pr.total_estimated_amount)}</strong></div>
                <div><span class="muted">Raised By</span><br>${pr.raised_by_user?.name ?? pr.raisedBy?.name ?? '-'}</div>
                <div><span class="muted">Name of Requestor</span><br>${pr.requestor_name ?? '-'}</div>
                <div><span class="muted">Designation</span><br>${pr.requestor_designation ?? '-'}</div>
                ${pr.attachment_url ? `<div><span class="muted">Attachment</span><br><a href="${pr.attachment_url}" target="_blank" rel="noopener">দেখুন / ডাউনলোড করুন</a></div>` : ''}
            </div>

            <div class="card">
                <p class="muted" style="margin:0;">In-word</p>
                <p style="margin:.25rem 0 0; font-weight:600;">৳ ${amountInWords(pr.total_estimated_amount)}</p>
            </div>

            <div class="card">
                <h3>আইটেম সমূহ</h3>
                <table>
                    <thead><tr><th>#</th><th>Item</th><th>Specification</th><th>Unit</th><th>Qty</th><th>Rate</th><th>Total</th><th>A/C Code</th><th>Fixed Asset</th></tr></thead>
                    <tbody>${itemsRows}</tbody>
                </table>
            </div>

            <div class="card">
                <h3>Item Category / Sub Category / Fixed Asset</h3>
                <table>
                    <thead><tr><th>Item Category</th><th>Sub Category</th><th>Fixed Asset</th><th>Item Name</th></tr></thead>
                    <tbody>${classificationRows}</tbody>
                </table>
            </div>

            <div class="card">
                <h3>Approval History</h3>
                <table>
                    <thead><tr><th>Date</th><th>User</th><th>Role</th><th>Action</th><th>Remarks</th></tr></thead>
                    <tbody>${approvalRows}</tbody>
                </table>
            </div>

            <div id="budget-check"></div>
        `;
    }

    async function renderActionArea(pr) {
        const area = document.getElementById('budget-check');

        if (pr.status === 'approved' || pr.status === 'rejected') {
            area.innerHTML = '';
            return;
        }

        if (isBudgetCheckStage(pr)) {
            await renderBudgetCheckCard(pr, area);
        } else if (isReviewStage(pr)) {
            renderGenericActionCard(area, 'Reviewer', 'Review Decision');
        } else if (isApproverStage(pr)) {
            renderGenericActionCard(area, 'Approver', 'Approval Decision');
        } else {
            // Not this user's turn to act — no action card, just show
            // where the PR currently sits in the chain.
            area.innerHTML = `<div class="card muted">This PR is at "${pr.status}" — waiting on the next role in the chain.</div>`;
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
                    <div id="livePreview" style="margin-top:.5rem;"></div>
                ` : `
                    <div class="row">
                        <div><span class="muted">Budget Code</span><br><strong>${line.code}</strong></div>
                        <div><span class="muted">Spent so far</span><br>৳ ${money(line.spent)}</div>
                    </div>
                    <table style="margin-top:.75rem;">
                        <tr><td class="muted">Total allocated Budget</td><td class="bold">৳ ${money(line.total_allocated_budget)}</td></tr>
                        <tr><td class="muted">Remaining Budget B/F</td><td>৳ ${money(line.remaining_budget_bf)}</td></tr>
                        <tr><td class="muted">Amount of PR</td><td>৳ ${money(line.amount_of_pr)}</td></tr>
                        <tr><td class="muted">Remaining Budget C/F</td><td style="color:${line.is_sufficient ? '#065f46' : '#991b1b'}"><strong>৳ ${money(line.remaining_budget_cf)}</strong></td></tr>
                        <tr><td class="muted">Name of Accountant</td><td>${check.data.accountant_name}</td></tr>
                    </table>
                    <p class="muted" style="margin-top:.5rem;">
                        ${line.is_sufficient ? '<b style="color:#065f46;">Sufficient balance</b>' : '<b style="color:#991b1b;">Insufficient balance — C/F would go negative</b>'}
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

                <label for="decision" style="margin-top:.75rem;">Decision</label>
                <select id="decision">
                    <option value="recommended">Recommend for Approval</option>
                    <option value="approved" ${line && !line.is_sufficient ? 'disabled' : ''}>Approved${line && !line.is_sufficient ? ' (insufficient balance)' : ''}</option>
                    <option value="rejected">Reject</option>
                </select>

                <div style="margin-top:1rem;">
                    <button class="btn" onclick="submitBudgetCheck()">Submit</button>
                </div>
            </div>
        `;

        if (!line) {
            document.getElementById('budgetLineSelect').addEventListener('change', (e) => {
                const id = e.target.value;
                const preview = document.getElementById('livePreview');
                const decisionReturned = document.querySelector('#decision option[value="approved"]');
                const picked = budgetLines.find(l => String(l.id) === id);
                if (!picked) {
                    preview.innerHTML = '';
                    decisionReturned.disabled = false;
                    decisionReturned.textContent = 'Approved';
                    return;
                }
                const cf = picked.balance - Number(pr.total_estimated_amount);
                const sufficient = cf >= 0;
                preview.innerHTML = `
                    <table>
                        <tr><td class="muted">Total allocated Budget</td><td class="bold">৳ ${money(picked.approved_budget)}</td></tr>
                        <tr><td class="muted">Remaining Budget B/F</td><td>৳ ${money(picked.balance)}</td></tr>
                        <tr><td class="muted">Amount of PR</td><td>৳ ${money(pr.total_estimated_amount)}</td></tr>
                        <tr><td class="muted">Remaining Budget C/F</td><td style="color:${sufficient ? '#065f46' : '#991b1b'}"><strong>৳ ${money(cf)}</strong></td></tr>
                    </table>
                `;
                decisionReturned.disabled = !sufficient;
                decisionReturned.textContent = sufficient ? 'Approved' : 'Approved (insufficient balance)';
                if (!sufficient && document.getElementById('decision').value === 'approved') {
                    document.getElementById('decision').value = 'recommended';
                }
            });
        }
    }

    function renderGenericActionCard(area, roleLabel, title) {
        area.innerHTML = `
            <div class="card">
                <h3>${title}</h3>
                <p class="muted">Acting as: <strong>${roleLabel}</strong></p>
                <label for="actionRemarks">Remarks</label>
                <textarea id="actionRemarks" rows="2"></textarea>
                <div style="margin-top:1rem; display:flex; gap:.5rem;">
                    <button class="btn" onclick="submitAction('approved', '${roleLabel}')">Approve</button>
                    <button class="btn secondary" onclick="submitAction('returned', '${roleLabel}')">Return</button>
                    <button class="btn danger" onclick="submitAction('rejected', '${roleLabel}')">Reject</button>
                </div>
            </div>
        `;
    }

    async function submitAction(action, roleAtAction) {
        errorBox.style.display = 'none';

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

    async function submitBudgetCheck() {
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
                decision: document.getElementById('decision').value,
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
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/purchase-requisitions/show.blade.php ENDPATH**/ ?>