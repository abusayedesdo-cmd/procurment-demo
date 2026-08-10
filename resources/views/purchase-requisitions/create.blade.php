@extends('layouts.app')

@section('title', 'New Purchase Requisition')

@section('content')
    <h1>New Purchase Requisition</h1>

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
                <label for="receiver_name">Name of Receiver</label>
                <input type="text" id="receiver_name">
            </div>
            <div>
                <label for="receiver_contact">Contact</label>
                <input type="text" id="receiver_contact">
            </div>
            <div>
                <label for="attachment">Attachment (Photo, Drawing, BOQ, ToR, etc. — Optional)</label>
                <input type="file" id="attachment" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx">
            </div>
        </div>

        <label>Remarks</label>
        <textarea id="remarks" rows="2"></textarea>

        <h3 style="margin-top:1.5rem;">Items</h3>
        <div id="itemRows"></div>
        <button type="button" class="btn secondary" id="addRowBtn" style="margin-top:.5rem;">+ Add Item</button>

        <div style="margin-top:1rem; text-align:right; font-weight:700;">
            Total Estimated Amount: ৳ <span id="grandTotal">0.00</span>
        </div>

        <div style="margin-top:1.5rem; display:flex; gap:.5rem;">
            <button type="submit" class="btn" id="submitBtn">Create PR</button>
            <a href="{{ route('purchase-requisitions.index') }}" class="btn secondary">Cancel</a>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    let items = [];
    let units = [];
    let chartOfAccounts = [];
    let rowCount = 0;

    const itemRows = document.getElementById('itemRows');
    const grandTotal = document.getElementById('grandTotal');
    const errorBox = document.getElementById('errorBox');
    const submitBtn = document.getElementById('submitBtn');
    let submitting = false;

    function itemOptions() {
        return items.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
    }
    function unitOptions() {
        return units.map(u => `<option value="${u.id}">${u.name}${u.symbol ? ' ('+u.symbol+')' : ''}</option>`).join('');
    }
    function chartOfAccountOptions() {
        return chartOfAccounts.map(a => `<option value="${a.id}">${a.code ? a.code + ' — ' : ''}${a.name}</option>`).join('');
    }

    function selectedCategoryId() {
        return document.getElementById('category_id').value;
    }

    // Resolve an item's category via its chart of account — works whether
    // the item came from the initial /items load (chart_of_account eager
    // loaded) or was just created in-flow (only chart_of_account_id known).
    function itemCategoryId(item) {
        if (item.chart_of_account?.category_id != null) return String(item.chart_of_account.category_id);
        const coaId = item.chart_of_account_id ?? item.chart_of_account?.id;
        const coa = chartOfAccounts.find(a => String(a.id) === String(coaId));
        return coa ? String(coa.category_id) : null;
    }

    function itemOptionsForCategory(categoryId) {
        const pool = categoryId ? items.filter(i => itemCategoryId(i) === String(categoryId)) : items;
        return pool.map(i => `<option value="${i.id}">${i.name}</option>`).join('');
    }

    function chartOfAccountOptionsForCategory(categoryId) {
        const pool = categoryId ? chartOfAccounts.filter(a => String(a.category_id) === String(categoryId)) : chartOfAccounts;
        return pool.map(a => `<option value="${a.id}">${a.code ? a.code + ' — ' : ''}${a.name}</option>`).join('');
    }

    // Rebuild every row's Item / Chart of Account dropdowns to match the
    // currently selected Procurement Category, keeping each row's current
    // selection if it's still valid for the new category.
    function refreshRowDropdownsForCategory() {
        const categoryId = selectedCategoryId();

        itemRows.querySelectorAll(':scope > div').forEach(div => {
            const itemSelect = div.querySelector('.row-item');
            const prevItem = itemSelect.value;
            itemSelect.innerHTML = `
                <option value="">-- Select --</option>
                <option value="__new__">-- Add New Item --</option>
                ${itemOptionsForCategory(categoryId)}
            `;
            const stillValid = prevItem === '__new__' || [...itemSelect.options].some(o => o.value === prevItem && prevItem !== '');
            itemSelect.value = stillValid ? prevItem : '';

            const accountSelect = div.querySelector('.row-account');
            const prevAccount = accountSelect.value;
            accountSelect.innerHTML = `
                <option value="">-- Select --</option>
                ${chartOfAccountOptionsForCategory(categoryId)}
            `;
            const accountStillValid = [...accountSelect.options].some(o => o.value === prevAccount && prevAccount !== '');
            accountSelect.value = accountStillValid ? prevAccount : '';
        });
    }

    function addRow() {
        const id = rowCount++;
        const categoryId = selectedCategoryId();
        const div = document.createElement('div');
        div.className = 'card';
        div.dataset.rowId = id;
        div.style.cssText = 'display:flex; flex-wrap:wrap; gap:.6rem; align-items:flex-end; margin-bottom:.75rem; padding:.75rem;';
        div.innerHTML = `
            <div style="min-width:160px;">
                <label style="font-size:.75rem;">Item</label>
                <select class="row-item" required style="width:100%;">
                    <option value="">-- Select --</option>
                    <option value="__new__">-- Add New Item --</option>
                    ${itemOptionsForCategory(categoryId)}
                </select>
            </div>
            <div class="row-new-item-wrap" style="display:none; min-width:160px;">
                <label style="font-size:.75rem;">New Item Name</label>
                <input type="text" class="row-new-item-name" style="width:100%;">
            </div>
            <div class="row-account-wrap" style="display:none; min-width:180px;">
                <label style="font-size:.75rem;">Chart of Account</label>
                <select class="row-account" style="width:100%;">
                    <option value="">-- Select --</option>
                    ${chartOfAccountOptionsForCategory(categoryId)}
                </select>
            </div>
            <div style="min-width:150px;">
                <label style="font-size:.75rem;">Specification</label>
                <input type="text" class="row-spec" style="width:100%;">
            </div>
            <div style="min-width:130px;">
                <label style="font-size:.75rem;">Unit</label>
                <select class="row-unit" required style="width:100%;">
                    <option value="">-- Select --</option>${unitOptions()}
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
                '<option value="">-- Select --</option>' +
                categoriesRes.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            document.getElementById('category_id').addEventListener('change', refreshRowDropdownsForCategory);

            document.getElementById('budget_line_id').innerHTML =
                '<option value="">-- (Budget checker will assign later) --</option>' +
                budgetLinesRes.data.map(l => `<option value="${l.id}">${l.code} — ${l.name}</option>`).join('');

            document.getElementById('package_id').innerHTML =
                '<option value="">-- None --</option>' +
                packagesRes.data.map(p => `<option value="${p.id}">${p.package_number ?? ''} — ${p.budgeted_head} (${p.plan_title})</option>`).join('');

            const me = meRes.data ?? meRes;
            if (me?.name) document.getElementById('requestor_name').value = me.name;
            if (me?.designation) document.getElementById('requestor_designation').value = me.designation;

            // Project/Program Name follows the requester's own assigned
            // project — auto-filled and locked, same as any other scoped
            // record, so it can't drift from what project_id enforces
            // server-side. Admin/Procurement Officer have no project of
            // their own and may raise a PR into any project, so the field
            // stays editable for them.
            const projectNameInput = document.getElementById('project_name');
            const isProjectExempt = ['admin', 'procurement_officer'].includes(me?.role?.name);
            if (me?.project?.name) {
                projectNameInput.value = me.project.name;
            }
            if (!isProjectExempt) {
                projectNameInput.readOnly = true;
                projectNameInput.style.background = '#f1f5f9';
                projectNameInput.title = 'Set automatically from your assigned project';
            }

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

        if (submitting) return; // guards against a double/triple click firing this handler again mid-flight
        submitting = true;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Processing...';

        errorBox.style.display = 'none';

        if (![...itemRows.children].length) {
            errorBox.textContent = 'Please add at least one item.';
            errorBox.style.display = 'block';
            submitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create PR';
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
                        throw new Error('Please provide an Item Name and Chart of Account for the new item.');
                    }

                    // Reuse an existing item with the same name under the same account
                    // instead of always creating a new row — case-insensitive match.
                    const existing = items.find(i =>
                        String(i.chart_of_account_id ?? i.chart_of_account?.id) === String(chartOfAccountId) &&
                        i.name.trim().toLowerCase() === name.toLowerCase()
                    );

                    if (existing) {
                        itemId = existing.id;
                    } else {
                        const { data: newItem } = await api.post('/items', {
                            chart_of_account_id: chartOfAccountId,
                            name,
                            specification: div.querySelector('.row-spec').value.trim() || null,
                        });
                        itemId = newItem.id;
                        items.push(newItem); // so a second "__new__" row in this same PR with the same name reuses it too
                    }
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
            submitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create PR';
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
                receiver_name: document.getElementById('receiver_name').value.trim() || null,
                receiver_contact: document.getElementById('receiver_contact').value.trim() || null,
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
            submitting = false;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create PR';
        }
    });

    init();
</script>
@endsection