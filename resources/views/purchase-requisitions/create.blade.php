@extends('layouts.app')

@section('title', 'নতুন Purchase Requisition')

@section('content')
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
                </select>
            </div>
            <div>
                <label for="category_id">Procurement Category</label>
                <select id="category_id" required></select>
            </div>
            <div>
<<<<<<< HEAD
                <label for="budget_line_id">Budget Line</label>
                <select id="budget_line_id"></select>
            </div>
            <div>
                <label for="package_id">Annual Plan Package</label>
                <select id="package_id"></select>
            </div>
            <div>
=======
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc
                <label for="requisition_date">Requisition Date</label>
                <input type="date" id="requisition_date" required>
            </div>
            <div>
                <label for="estimated_delivery_date">Estimated Delivery Date</label>
                <input type="date" id="estimated_delivery_date">
            </div>
        </div>

        <label>Remarks</label>
        <textarea id="remarks" rows="2"></textarea>

        <h3 style="margin-top:1.5rem;">আইটেম সমূহ</h3>
        <div class="item-row muted" style="font-size:.8rem;">
            <div>Item</div><div>Unit</div><div>Quantity</div><div>Rate (৳)</div><div>Total</div><div></div>
        </div>
        <div id="itemRows"></div>
        <button type="button" class="btn secondary" id="addRowBtn" style="margin-top:.5rem;">+ আইটেম যোগ করুন</button>

        <div style="margin-top:1rem; text-align:right; font-weight:700;">
            মোট আনুমানিক পরিমাণ: ৳ <span id="grandTotal">0.00</span>
        </div>

        <div style="margin-top:1.5rem; display:flex; gap:.5rem;">
            <button type="submit" class="btn">PR তৈরি করুন</button>
            <a href="{{ route('purchase-requisitions.index') }}" class="btn secondary">বাতিল</a>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    let items = [];
    let units = [];
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

    function addRow() {
        const id = rowCount++;
        const div = document.createElement('div');
        div.className = 'item-row';
        div.dataset.rowId = id;
        div.innerHTML = `
            <select class="row-item" required><option value="">-- বাছাই করুন --</option>${itemOptions()}</select>
            <select class="row-unit" required><option value="">-- বাছাই করুন --</option>${unitOptions()}</select>
            <input type="number" class="row-qty" min="0.01" step="0.01" value="1" required>
            <input type="number" class="row-rate" min="0" step="0.01" value="0" required>
            <input type="text" class="row-total" value="0.00" disabled>
            <button type="button" class="btn danger row-remove" style="padding:.4rem .6rem;">✕</button>
        `;
        itemRows.appendChild(div);

        div.querySelector('.row-qty').addEventListener('input', () => recalcRow(div));
        div.querySelector('.row-rate').addEventListener('input', () => recalcRow(div));
        div.querySelector('.row-remove').addEventListener('click', () => { div.remove(); recalcGrandTotal(); });
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
<<<<<<< HEAD
            const [categoriesRes, itemsRes, unitsRes, budgetLinesRes, packagesRes] = await Promise.all([
            api.get('/procurement-categories?per_page=200'),
            api.get('/items?per_page=500'),
            api.get('/units?per_page=200'),
            api.get('/budget-lines'),
            api.get('/procurement-plan-packages'),
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

        items = itemsRes.data;
        units = unitsRes.data;
=======
            const [categoriesRes, itemsRes, unitsRes] = await Promise.all([
                api.get('/procurement-categories?per_page=200'),
                api.get('/items?per_page=500'),
                api.get('/units?per_page=200'),
            ]);

            document.getElementById('category_id').innerHTML =
                '<option value="">-- বাছাই করুন --</option>' +
                categoriesRes.data.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

            items = itemsRes.data;
            units = unitsRes.data;
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc
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

        const itemPayload = [...itemRows.children].map(div => ({
            item_id: div.querySelector('.row-item').value,
            unit_id: div.querySelector('.row-unit').value,
            quantity: parseFloat(div.querySelector('.row-qty').value),
            rate_bdt: parseFloat(div.querySelector('.row-rate').value),
        }));

        if (!itemPayload.length) {
            errorBox.textContent = 'অন্তত একটা আইটেম যোগ করুন।';
            errorBox.style.display = 'block';
            return;
        }

        try {
            const { data } = await api.post('/purchase-requisitions', {
                window_type: document.getElementById('window_type').value,
                category_id: document.getElementById('category_id').value,
<<<<<<< HEAD
                budget_line_id: document.getElementById('budget_line_id').value || null,
                procurement_plan_package_id: document.getElementById('package_id').value || null,
=======
>>>>>>> 17f553d94be223884a853c7e712b85e71d50acfc
                requisition_date: document.getElementById('requisition_date').value,
                estimated_delivery_date: document.getElementById('estimated_delivery_date').value || null,
                remarks: document.getElementById('remarks').value || null,
                items: itemPayload,
            });

            window.location.href = `/purchase-requisitions/${data.id}`;
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    });

    init();
</script>
@endsection
