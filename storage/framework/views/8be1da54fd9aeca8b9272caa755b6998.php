

<?php $__env->startSection('title', 'Budget Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="summary" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1rem;"></div>

    <div id="inputArea"></div>

    <div id="categories" style="margin-top:1rem;">লোড হচ্ছে...</div>
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
                <h3>নতুন Budget Category যোগ করুন</h3>
                <div class="row">
                    <div>
                        <label for="catCode">Code (যেমন: A, B, C, E)</label>
                        <input type="text" id="catCode" maxlength="20">
                    </div>
                    <div>
                        <label for="catName">Name</label>
                        <input type="text" id="catName" placeholder="যেমন: Staff Costs">
                    </div>
                    <div>
                        <label for="catSort">Sort Order</label>
                        <input type="number" id="catSort" value="0">
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <button class="btn" onclick="submitCategory()">Category যোগ করুন</button>
                </div>
            </div>

            <div class="card" style="margin-bottom:1rem;">
                <h3>নতুন Budget Line যোগ করুন</h3>
                <div class="row">
                    <div>
                        <label for="lineCategory">Category</label>
                        <select id="lineCategory">
                            <option value="">-- বাছাই করুন --</option>
                            ${categoryOptions}
                        </select>
                    </div>
                    <div>
                        <label for="lineCode">Item Code</label>
                        <input type="text" id="lineCode" placeholder="যেমন: A.1.1">
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
                        <label for="lineApproved">Approved Budget (৳) — realigned total, ব্যালেন্স হিসাবের ভিত্তি</label>
                        <input type="number" id="lineApproved" step="0.01" value="0">
                    </div>
                    <div>
                        <label for="lineReportedExpense">Reported Actual Expense (৳) — donor-এর last fin. report থেকে</label>
                        <input type="number" id="lineReportedExpense" step="0.01" value="0">
                    </div>
                </div>
                <div style="margin-top:1rem;">
                    <button class="btn" onclick="submitLine()">Budget Line যোগ করুন</button>
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
            errorBox.textContent = 'Code এবং Name দিন।';
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
            errorBox.textContent = 'Category, Item Code, এবং Item Name দিন।';
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
            `).join('') || '<tr><td colspan="6" class="muted">কোনো budget line নেই।</td></tr>';

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
        }).join('') || '<div class="card muted">এখনো কোনো Budget Category নেই — উপরের ফর্ম দিয়ে একটা যোগ করুন।</div>';
    }

    load();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/budget/dashboard.blade.php ENDPATH**/ ?>