<?php $__env->startSection('title', 'Budget Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div id="errorBox" class="error-box" style="display:none;"></div>

    <div id="checkQueue" style="margin-bottom:1.5rem;"></div>

    <div id="summary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem;"></div>

    <div id="inputArea"></div>

    <div id="categories" style="margin-top:1rem;">Loading...</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const currentUserRole = <?php echo json_encode(auth()->user()->roleName(), 15, 512) ?>;
    const canEdit = ['budget_checker', 'admin'].includes(currentUserRole);

    const errorBox = document.getElementById('errorBox');
    const summary = document.getElementById('summary');
    const inputArea = document.getElementById('inputArea');
    const categoriesEl = document.getElementById('categories');

    let categories = [];

    function money(n) {
        return Number(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
    }

    function barColor(pct) {
        if (pct >= 100) return '#b91c1c';
        if (pct >= 80) return '#d97706';
        return '#059669';
    }

    async function load() {
        try {
            const [dashRes, catRes] = await Promise.all([
                api.get('/budget-dashboard'),
                api.get('/budget-categories'),
            ]);
            categories = catRes.data;
            renderInputArea();
            render(dashRes.data, dashRes.grand_total);
            if (canEdit) loadCheckQueue();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    function renderInputArea() {
        if (!canEdit) {
            inputArea.innerHTML = '';
            return;
        }

        const categoryOptions = categories.map(c => `<option value="${c.id}">${c.code}. ${c.name}</option>`).join('');

        inputArea.innerHTML = `
            <div class="card" style="margin-bottom:1rem;">
                <h3>Add New Budget Category</h3>
                <div class="row">
                    <div>
                        <label for="catCode">Code (e.g. A, B, C, E)</label>
                        <input type="text" id="catCode" maxlength="20">
                    </div>
                    <div>
                        <label for="catName">Name</label>
                        <input type="text" id="catName" placeholder="e.g. Staff Costs">
                    </div>
                    <div>
                        <label for="catSort">Sort Order</label>
                        <input type="number" id="catSort" value="0">
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <button class="btn" onclick="submitCategory()">Add Category</button>
                </div>
            </div>

            <div class="card" style="margin-bottom:1rem;">
                <h3>Add New Budget Line</h3>
                <div class="row">
                    <div>
                        <label for="lineCategory">Category</label>
                        <select id="lineCategory">
                            <option value="">-- Select --</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    <div>
                        <label for="lineCode">Item Code</label>
                        <input type="text" id="lineCode" placeholder="e.g. A.1.1">
                    </div>
                    <div>
                        <label for="lineName">Item Name</label>
                        <input type="text" id="lineName">
                    </div>
                    <div>
                        <label for="lineUnit">Unit</label>
                        <input type="text" id="lineUnit">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label for="lineOriginal">Original Budget (৳)</label>
                        <input type="number" id="lineOriginal" step="0.01" value="0">
                    </div>
                    <div>
                        <label for="lineApproved">Approved Budget (৳) — realigned total, basis for balance calculation</label>
                        <input type="number" id="lineApproved" step="0.01" value="0">
                    </div>
                    <div>
                        <label for="lineReportedExpense">Reported Actual Expense (৳) — from donor's last financial report</label>
                        <input type="number" id="lineReportedExpense" step="0.01" value="0">
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <button class="btn" onclick="submitLine()">Add Budget Line</button>
                </div>
            </div>
        `;
    }

    async function submitCategory() {
        errorBox.style.display = 'none';
        const code = document.getElementById('catCode').value.trim();
        const name = document.getElementById('catName').value.trim();
        const sortOrder = document.getElementById('catSort').value || 0;

        if (!code || !name) {
            errorBox.textContent = 'Please enter Code and Name.';
            errorBox.style.display = 'block';
            return;
        }

        try {
            await api.post('/budget-categories', { code, name, sort_order: Number(sortOrder) });
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    async function submitLine() {
        errorBox.style.display = 'none';
        const categoryId = document.getElementById('lineCategory').value;
        const itemCode = document.getElementById('lineCode').value.trim();
        const itemName = document.getElementById('lineName').value.trim();

        if (!categoryId || !itemCode || !itemName) {
            errorBox.textContent = 'Please enter Category, Item Code, and Item Name.';
            errorBox.style.display = 'block';
            return;
        }

        try {
            await api.post('/budget-lines', {
                budget_category_id: categoryId,
                item_code: itemCode,
                item_name: itemName,
                unit: document.getElementById('lineUnit').value || null,
                original_budget: Number(document.getElementById('lineOriginal').value || 0),
                approved_budget: Number(document.getElementById('lineApproved').value || 0),
                reported_actual_expense: Number(document.getElementById('lineReportedExpense').value || 0),
            });
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    function render(cats, grand) {
        summary.innerHTML = `
            <div class="card"><h3 style="margin:0 0 .4rem;font-size:.8rem;color:#6b7280;text-transform:uppercase;">Total Approved Budget</h3><p style="margin:0;font-size:1.5rem;font-weight:700;">৳ ${money(grand.budget)}</p></div>
            <div class="card"><h3 style="margin:0 0 .4rem;font-size:.8rem;color:#6b7280;text-transform:uppercase;">Total Spent</h3><p style="margin:0;font-size:1.5rem;font-weight:700;">৳ ${money(grand.spent)}</p></div>
            <div class="card"><h3 style="margin:0 0 .4rem;font-size:.8rem;color:#6b7280;text-transform:uppercase;">Balance</h3><p style="margin:0;font-size:1.5rem;font-weight:700;">৳ ${money(grand.balance)}</p></div>
        `;

        categoriesEl.innerHTML = cats.map(cat => {
            const pct = cat.total_budget > 0 ? Math.min(100, Math.round(cat.total_spent / cat.total_budget * 100)) : 0;
            const lineRows = cat.lines.map(l => `
                <tr>
                    <td>${l.item_code}</td>
                    <td>${l.item_name}</td>
                    <td style="text-align:right;">${money(l.approved_budget)}</td>
                    <td style="text-align:right;">${money(l.spent)}</td>
                    <td style="text-align:right;">${money(l.balance)}</td>
                    <td style="text-align:right;">${l.percent_used}%</td>
                </tr>
            `).join('') || '<tr><td colspan="6" class="muted">No budget lines yet.</td></tr>';

            return `
                <div class="card" style="margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                        <h3 style="margin:0;">${cat.code}. ${cat.name}</h3>
                        <span class="muted">৳ ${money(cat.total_spent)} / ${money(cat.total_budget)} (${pct}%)</span>
                    </div>
                    <div style="background:#eef0f3;border-radius:999px;height:8px;overflow:hidden;margin-bottom:1rem;">
                        <div style="width:${pct}%;background:${barColor(pct)};height:100%;"></div>
                    </div>
                    <table>
                        <thead><tr><th>Code</th><th>Item</th><th style="text-align:right;">Budget</th><th style="text-align:right;">Spent</th><th style="text-align:right;">Balance</th><th style="text-align:right;">%</th></tr></thead>
                        <tbody>${lineRows}</tbody>
                    </table>
                </div>
            `;
        }).join('') || '<div class="card muted">No Budget Category yet — add one using the form above.</div>';
    }

    const checkQueueEl = document.getElementById('checkQueue');
    let sharedBudgetLines = null;
    let queuedPrs = [];
    let selectedCheckData = null;

    async function loadCheckQueue() {
        checkQueueEl.innerHTML = '<div class="card muted">Loading Budgetary Check queue...</div>';

        try {
            const [prsRes, linesRes] = await Promise.all([
                api.get('/purchase-requisitions?status=reviewed'),
                api.get('/budget-lines'),
            ]);
            queuedPrs = prsRes.data;
            sharedBudgetLines = linesRes.data;
        } catch (err) {
            checkQueueEl.innerHTML = '';
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            return;
        }

        renderQueuePanel();
    }

    function renderQueuePanel() {
        const prOptions = queuedPrs.map(pr => `
            <option value="${pr.id}">PR ${pr.pr_number} — ৳ ${money(pr.total_estimated_amount)}</option>
        `).join('');

        checkQueueEl.innerHTML = `
            <div class="card" style="margin-bottom:1rem;">
                <h3 style="margin:0 0 .75rem;">Budgetary Check — Purchase Requisitions Awaiting Check (${queuedPrs.length})</h3>
                ${!queuedPrs.length ? '<p class="muted">No PRs are currently awaiting a Budgetary Check.</p>' : `
                    <label for="prSelect">Select PR</label>
                    <select id="prSelect">
                        <option value="">-- Select a PR --</option>
                        ${prOptions}
                    </select>
                `}
            </div>
            <div id="checkForm"></div>
        `;

        if (queuedPrs.length) {
            document.getElementById('prSelect').addEventListener('change', (e) => {
                const prId = e.target.value;
                document.getElementById('checkForm').innerHTML = '';
                selectedCheckData = null;
                if (prId) selectPr(Number(prId));
            });
        }
    }

    async function selectPr(prId) {
        const formEl = document.getElementById('checkForm');
        formEl.innerHTML = '<div class="card muted">Loading PR...</div>';

        let checkRes;
        try {
            checkRes = await api.get(`/purchase-requisitions/${prId}/budget-check`);
        } catch (err) {
            formEl.innerHTML = '';
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            return;
        }

        selectedCheckData = checkRes.data;
        formEl.innerHTML = renderCheckForm(prId, selectedCheckData);
        wireCheckForm(prId, selectedCheckData);
    }

    function renderCheckForm(prId, checkData) {
        const pr = checkData.pr;
        const line = checkData.budget_line;

        const lineOptions = (sharedBudgetLines || []).map(l => `
            <option value="${l.id}" ${line && line.id === l.id ? 'selected' : ''}>${l.code} — ${l.name} (${l.category}) · balance ৳ ${money(l.balance)}</option>
        `).join('');

        return `
            <div class="card" style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.5rem;">
                    <h4 style="margin:0;">PR ${pr.pr_number} — ৳ ${money(pr.total_estimated_amount)}</h4>
                    <a href="/purchase-requisitions/${pr.id}" class="muted">View Full PR →</a>
                </div>

                <p class="bold" style="margin:.25rem 0 .75rem;">Budgetary Check: (by Accounts personnel)</p>

                <label for="budgetLineSelect">Budget Line (optional — for linking the eventual expense)</label>
                <select id="budgetLineSelect">
                    <option value="">-- None --</option>
                    ${lineOptions}
                </select>

                <div class="row" style="margin-top:.75rem;">
                    <div>
                        <label for="allocatedBudget">Total Allocated Budget (৳)</label>
                        <input type="number" id="allocatedBudget" step="0.01" value="${line ? line.total_allocated_budget : ''}">
                    </div>
                    <div>
                        <label for="remainingBf">Remaining Budget B/F (৳)</label>
                        <input type="number" id="remainingBf" step="0.01" value="${line ? line.remaining_budget_bf : ''}">
                    </div>
                </div>

                <table style="margin-top:.75rem;">
                    <tr><td class="muted">Amount of PR</td><td class="bold">৳ ${money(pr.total_estimated_amount)}</td></tr>
                    <tr><td class="muted">Remaining Budget C/F</td><td id="remainingCfCell" style="color:#065f46;"><strong>—</strong></td></tr>
                    <tr><td class="muted">Name of Accountant</td><td>${checkData.accountant_name}</td></tr>
                </table>
                <p class="muted" id="sufficiencyNote" style="margin-top:.5rem;"></p>

                <div class="form-field checkbox-field" style="margin-top:1rem;">
                    <label><input type="checkbox" id="codeVerified"> Budget code verified</label>
                </div>
                <div class="form-field checkbox-field">
                    <label><input type="checkbox" id="availabilityVerified"> Budget availability confirmed</label>
                </div>

                <label for="budgetRemarks" style="margin-top:.75rem;">Remarks</label>
                <textarea id="budgetRemarks" rows="2"></textarea>

                <label for="decision" style="margin-top:.75rem;">Decision</label>
                <select id="decision">
                    <option value="recommended">Recommend</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Reject</option>
                </select>

                <div id="routeToField" style="margin-top:.75rem;">
                    <label for="routeTo">Send to</label>
                    <select id="routeTo">
                        <option value="focal_person">Focal Person</option>
                        <option value="executive_director">Executive Director (ED)</option>
                    </select>
                </div>

                <div style="margin-top:1rem;">
                    <button class="btn" onclick="submitQueueCheck(${pr.id})">Submit</button>
                </div>
            </div>
        `;
    }

    function wireCheckForm(prId, checkData) {
        const pr = checkData.pr;
        const allocatedInput = document.getElementById('allocatedBudget');
        const bfInput = document.getElementById('remainingBf');
        const cfCell = document.getElementById('remainingCfCell');
        const sufficiencyNote = document.getElementById('sufficiencyNote');
        const decisionApproved = document.querySelector('#decision option[value="approved"]');
        const decisionSelect = document.getElementById('decision');
        const lineSelect = document.getElementById('budgetLineSelect');
        const routeToField = document.getElementById('routeToField');

        // Remaining Budget C/F = Remaining Budget B/F − Amount of PR.
        // Recomputed live any time the accountant edits Remaining B/F.
        function recalc() {
            const bf = Number(bfInput.value || 0);
            const cf = bf - Number(pr.total_estimated_amount);
            const sufficient = cf >= 0;

            cfCell.innerHTML = `<strong style="color:${sufficient ? '#065f46' : '#991b1b'}">৳ ${money(cf)}</strong>`;
            sufficiencyNote.innerHTML = sufficient
                ? '<b style="color:#065f46;">Sufficient balance</b>'
                : '<b style="color:#991b1b;">Insufficient balance — C/F would go negative</b>';

            decisionApproved.disabled = !sufficient;
            decisionApproved.textContent = sufficient ? 'Approved' : 'Approved (insufficient balance)';
            if (!sufficient && decisionSelect.value === 'approved') {
                decisionSelect.value = 'recommended';
            }
        }

        bfInput.addEventListener('input', recalc);

        // Picking a different Budget Line re-fills Allocated Budget / Remaining B/F
        // from that line's live figures — still editable afterwards.
        lineSelect.addEventListener('change', (e) => {
            const picked = sharedBudgetLines.find(l => String(l.id) === e.target.value);
            if (picked) {
                allocatedInput.value = picked.approved_budget;
                bfInput.value = picked.balance;
            }
            recalc();
        });

        // "Send to" only matters when the PR is actually being forwarded —
        // hide it for Reject.
        decisionSelect.addEventListener('change', (e) => {
            routeToField.style.display = e.target.value === 'rejected' ? 'none' : '';
        });

        recalc();
    }

    async function submitQueueCheck(prId) {
        errorBox.style.display = 'none';

        const lineSelect = document.getElementById('budgetLineSelect');
        const allocatedBudget = document.getElementById('allocatedBudget').value;
        const remainingBf = document.getElementById('remainingBf').value;
        const decision = document.getElementById('decision').value;

        if (allocatedBudget === '' || remainingBf === '') {
            errorBox.textContent = 'Please enter Allocated Budget and Remaining Budget B/F.';
            errorBox.style.display = 'block';
            return;
        }

        const payload = {
            budget_line_id: lineSelect.value || null,
            allocated_budget: Number(allocatedBudget),
            remaining_budget_bf: Number(remainingBf),
            is_budget_code_verified: document.getElementById('codeVerified').checked,
            is_budget_available: document.getElementById('availabilityVerified').checked,
            decision,
            remarks: document.getElementById('budgetRemarks').value || null,
        };
        if (decision !== 'rejected') {
            payload.route_to = document.getElementById('routeTo').value;
        }

        try {
            await api.post(`/purchase-requisitions/${prId}/budget-check`, payload);
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    load();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/budget/dashboard.blade.php ENDPATH**/ ?>