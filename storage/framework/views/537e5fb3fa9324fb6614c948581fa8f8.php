<?php $__env->startSection('title', 'নতুন Purchase Requisition'); ?>

<?php $__env->startSection('content'); ?>
    <h1>নতুন Purchase Requisition</h1>

    <div id="errorBox" class="error-box" style="display:none;"></div>

    <form id="prForm" class="card">
        <div class="row">
            <div>
                <label for="window_type">Window</label>
                <select id="window_type" required>
                    <option value="PR">Purchase Requisition (PR)</option>
                    <option value="BOQ">BOQ/TOR</option>
                    <option value="Design_Drawing">Design & Drawing</option>
                    <option value="Photo">Photo</option>
                </select>
            </div>
            <div>
                <label for="category_id">Procurement Category</label>
                <select id="category_id" required></select>
            </div>
            <div>
                <label for="project_name">Project/Program Name</label>
                <input type="text" id="project_name">
            </div>
            <div>
                <label for="budget_line_id">Budget Line</label>
                <select id="budget_line_id"></select>
            </div>
            <div>
                <label for="package_id">Annual Plan Package</label>
                <select id="package_id"></select>
            </div>
            <div>
                <label for="requisition_date">Requisition Date</label>
                <input type="date" id="requisition_date" required>
            </div>
            <div>
                <label for="estimated_delivery_date">Estimated Delivery Date</label>
                <input type="date" id="estimated_delivery_date">
            </div>
            <div>
                <label for="estimated_delivery_time">Estimated Delivery Time (Optional)</label>
                <input type="text" id="estimated_delivery_time" placeholder="e.g. 10:00 AM">
            </div>
            <div>
                <label for="delivery_location">Delivery Location (Mandatory)</label>
                <input type="text" id="delivery_location" required>
            </div>
            <div>
                <label for="requestor_name">Name of Requestor</label>
                <input type="text" id="requestor_name">
            </div>
            <div>
                <label for="requestor_designation">Designation</label>
                <input type="text" id="requestor_designation">
            </div>
            <div>
                <label for="attachment">Attachment (Photo, Drawing, BOQ, ToR, etc. — Optional)</label>
                <input type="file" id="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
            </div>
        </div>

        <label>Remarks</label>
        <textarea id="remarks" rows="2"></textarea>

        <h3 style="margin-top:1.5rem;">আইটেম সমূহ</h3>
        <div id="itemRows"></div>
        <button type="button" class="btn secondary" id="addRowBtn" style="margin-top:.5rem;">+ আইটেম যোগ করুন</button>

        <div style="margin-top:1rem; text-align:right; font-weight:700;">
            মোট আনুমানিক পরিমাণ: ৳ <span id="grandTotal">0.00</span>
        </div>

        <div style="margin-top:1.5rem; display:flex; gap:.5rem;">
            <button type="submit" class="btn">PR তৈরি করুন</button>
            <a href="<?php echo e(route('purchase-requisitions.index')); ?>" class="btn secondary">বাতিল</a>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    let items = [];
    let units = [];
    let chartOfAccounts = [];
    let rowCount = 0;

    const itemRows = document.getElementById('itemRows');
    const grandTotal = document.getElementById('grandTotal');
    const errorBox = document.getElementById('errorBox');

    function itemOptions() {
        return items.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
    }
    function unitOptions() {
        return units.map(u => `<option value="${u.id}">${u.name}${u.symbol ? ' ('+u.symbol+')' : ''}</option>`).join('');
    }
    function chartOfAccountOptions() {
        return chartOfAccounts.map(a => `<option value="${a.id}">${a.code ? a.code + ' — ' : ''}${a.name}</option>`).join('');
    }

    function addRow() {
        const id = rowCount++;
        const div = document.createElement('div');
        div.className = 'card';
        div.dataset.rowId = id;
        div.style.cssText = 'display:flex; flex-wrap:wrap; gap:.6rem; align-items:flex-end; margin-bottom:.75rem; padding:.75rem;';
        div.innerHTML = `
            <div style="min-width:160px;">
                <label style="font-size:.75rem;">Item</label>
                <select class="row-item" required style="width:100%;">
                    <option value="">-- বাছাই করুন --</option>
                    <option value="__new__">-- নতুন আইটেম যোগ করুন --</option>
                    ${itemOptions()}
                </select>
            </div>
            <div class="row-new-item-wrap" style="display:none; min-width:160px;">
                <label style="font-size:.75rem;">নতুন Item Name</label>
                <input type="text" class="row-new-item-name" style="width:100%;">
            </div>
            <div class="row-account-wrap" style="display:none; min-width:180px;">
                <label style="font-size:.75rem;">Chart of Account</label>
                <select class="row-account" style="width:100%;">
                    <option value="">-- বাছাই করুন --</option>
                    ${chartOfAccountOptions()}
                </select>
            </div>
            <div style="min-width:150px;">
                <label style="font-size:.75rem;">Specification</label>
                <input type="text" class="row-spec" style="width:100%;">
            </div>
            <div style="min-width:130px;">
                <label style="font-size:.75rem;">Unit</label>
                <select class="row-unit" required style="width:100%;">
                    <option value="">-- বাছাই করুন --</option>${unitOptions()}
                </select>
            </div>
            <div style="width:90px;">
                <label style="font-size:.75rem;">Quantity</label>
                <input type="number" class="row-qty" min="0.01" step="0.01" value="1" required style="width:100%;">
            </div>
            <div style="width:100px;">
                <label style="font-size:.75rem;">Rate (৳)</label>
                <input type="number" class="row-rate" min="0" step="0.01" value="0" required style="width:100%;">
            </div>
            <div style="width:110px;">
                <label style="font-size:.75rem;">Total</label>
                <input type="text" class="row-total" value="0.00" disabled style="width:100%;">
            </div>
            <div style="width:110px;">
                <label style="font-size:.75rem;">A/C Code</label>
                <input type="text" class="row-ac-code" style="width:100%;">
            </div>
            <div style="display:flex; flex-direction:column; align-items:center;">
                <label style="font-size:.75rem;">Fixed Asset</label>
                <input type="checkbox" class="row-fixed-asset" title="Fixed Asset">
            </div>
            <button type="button" class="btn danger row-remove" style="padding:.5rem .7rem;">✕</button>
        `;
        itemRows.appendChild(div);

        div.querySelector('.row-qty').addEventListener('input', () => recalcRow(div));
        div.querySelector('.row-rate').addEventListener('input', () => recalcRow(div));
        div.querySelector('.row-remove').addEventListener('click', () => { div.remove(); recalcGrandTotal(); });
        div.querySelector('.row-item').addEventListener('change', (e) => {
            const isNew = e.target.value === '__new__';
            div.querySelector('.row-new-item-wrap').style.display = isNew ? '' : 'none';
            div.querySelector('.row-account-wrap').style.display = isNew ? '' : 'none';
        });
    }

    function recalcRow(div) {
        const qty = parseFloat(div.querySelector('.row-qty').value) || 0;
        const rate = parseFloat(div.querySelector('.row-rate').value) || 0;
        div.querySelector('.row-total').value = (qty * rate).toFixed(2);
        recalcGrandTotal();
    }

    function recalcGrandTotal() {
        const total = [...itemRows.querySelectorAll('.row-total')]
            .reduce((sum, el) => sum + (parseFloat(el.value) || 0), 0);
        grandTotal.textContent = total.toFixed(2);
    }

    async function init() {
        try {
            const [categoriesRes, itemsRes, unitsRes, budgetLinesRes, packagesRes, meRes, coaRes] = await Promise.all([
                api.get('/procurement-categories?per_page=200'),
                api.get('/items?per_page=500'),
                api.get('/units?per_page=200'),
                api.get('/budget-lines'),
                api.get('/procurement-plan-packages'),
                api.get('/me'),
                api.get('/chart-of-accounts?per_page=200'),
            ]);

            document.getElementById('category_id').innerHTML =
                '<option value="">-- বাছাই করুন --</option>' +
                categoriesRes.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

            document.getElementById('budget_line_id').innerHTML =
                '<option value="">-- (পরে বাজেট চেকার ঠিক করবে) --</option>' +
                budgetLinesRes.data.map(l => `<option value="${l.id}">${l.code} — ${l.name}</option>`).join('');

            document.getElementById('package_id').innerHTML =
                '<option value="">-- কোনোটা নয় --</option>' +
                packagesRes.data.map(p => `<option value="${p.id}">${p.package_number ?? ''} — ${p.budgeted_head} (${p.plan_title})</option>`).join('');

            const me = meRes.data ?? meRes;
            if (me?.name) document.getElementById('requestor_name').value = me.name;
            if (me?.designation) document.getElementById('requestor_designation').value = me.designation;

            items = itemsRes.data;
            units = unitsRes.data;
            chartOfAccounts = coaRes.data;
            addRow();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    document.getElementById('addRowBtn').addEventListener('click', addRow);

    document.getElementById('prForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        errorBox.style.display = 'none';

        if (![...itemRows.children].length) {
            errorBox.textContent = 'অন্তত একটা আইটেম যোগ করুন।';
            errorBox.style.display = 'block';
            return;
        }

        let itemPayload;
        try {
            itemPayload = await Promise.all([...itemRows.children].map(async (div) => {
                let itemId = div.querySelector('.row-item').value;

                if (itemId === '__new__') {
                    const name = div.querySelector('.row-new-item-name').value.trim();
                    const chartOfAccountId = div.querySelector('.row-account').value;

                    if (!name || !chartOfAccountId) {
                        throw new Error('নতুন আইটেমের জন্য Item Name ও Chart of Account দিন।');
                    }

                    const { data: newItem } = await api.post('/items', {
                        chart_of_account_id: chartOfAccountId,
                        name,
                        specification: div.querySelector('.row-spec').value.trim() || null,
                    });
                    itemId = newItem.id;
                }

                return {
                    item_id: itemId,
                    specification: div.querySelector('.row-spec').value.trim() || null,
                    unit_id: div.querySelector('.row-unit').value,
                    quantity: parseFloat(div.querySelector('.row-qty').value),
                    rate_bdt: parseFloat(div.querySelector('.row-rate').value),
                    ac_code: div.querySelector('.row-ac-code').value.trim() || null,
                    is_fixed_asset: div.querySelector('.row-fixed-asset').checked,
                };
            }));
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            return;
        }

        try {
            const { data } = await api.post('/purchase-requisitions', {
                window_type: document.getElementById('window_type').value,
                category_id: document.getElementById('category_id').value,
                project_name: document.getElementById('project_name').value.trim() || null,
                budget_line_id: document.getElementById('budget_line_id').value || null,
                procurement_plan_package_id: document.getElementById('package_id').value || null,
                requisition_date: document.getElementById('requisition_date').value,
                estimated_delivery_date: document.getElementById('estimated_delivery_date').value || null,
                estimated_delivery_time: document.getElementById('estimated_delivery_time').value.trim() || null,
                delivery_location: document.getElementById('delivery_location').value.trim(),
                requestor_name: document.getElementById('requestor_name').value.trim() || null,
                requestor_designation: document.getElementById('requestor_designation').value.trim() || null,
                remarks: document.getElementById('remarks').value || null,
                items: itemPayload,
            });

            const attachmentFile = document.getElementById('attachment').files[0];
            if (attachmentFile) {
                const formData = new FormData();
                formData.append('attachment', attachmentFile);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                await fetch(`/api/purchase-requisitions/${data.id}/attachment`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
            }

            window.location.href = `/purchase-requisitions/${data.id}`;
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    });

    init();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/purchase-requisitions/create.blade.php ENDPATH**/ ?>