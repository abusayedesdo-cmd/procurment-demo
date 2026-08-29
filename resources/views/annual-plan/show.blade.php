@extends('layouts.app')

@section('title', 'Procurement Annual Plan')

@section('content')
    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="planHeader">Loading...</div>


    <div class="card">
        <h3 id="formTitle">Add New Package</h3>
        <input type="hidden" id="editingId" value="">

        <div class="row">
            <div>
                <label for="yearSlotSelect">Select Year</label>
                <select id="yearSlotSelect" onchange="onYearSlotChange()">
                    <option id="optPrev2" value="previous_2nd_year">Previous 2nd Year</option>
                    <option id="optPrev1" value="previous_1st_year" selected>Previous 1st Year</option>
                    <option id="optCurrent" value="current_year">Current Year (Year-1)</option>
                    <option id="optYear2" value="year_2_total">Year-2 Total (Upcoming)</option>
                    <option id="optYear3" value="year_3_total">Year-3 Total (Upcoming)</option>
                </select>
                <div id="planTypeNote" class="muted" style="display:none; margin-top:.25rem;">This is an Annual plan — defaults to Current Year, but you can pick another year if needed.</div>
            </div>
        </div>

        <div class="row">
            <div>
                <label for="category">Category</label>
                <select id="category" onchange="onCategoryChanged()"></select>
            </div>
            <div>
                <label for="groupSelect">Procurement Group No</label>
                <select id="groupSelect" onchange="onGroupChanged()">
                    <option value="">-- Select Category first --</option>
                </select>
            </div>
            <div>
                <label for="itemSelect">Item (select if exists)</label>
                <input type="text" id="itemSelect" list="itemDatalist" placeholder="-- Select / Type New --" oninput="onItemSelected()" autocomplete="off">
                <datalist id="itemDatalist"></datalist>
            </div>
            <div>
                <label for="budgetedHead">Item Name </label>
                <input type="text" id="budgetedHead" placeholder="e.g. Computer">
                <input type="hidden" id="itemId" value="">
            </div>
            <div>
                <label for="specification">Specification</label>
                <input type="text" id="specification">
            </div>
            <div>
                <label for="unit">Unit</label>
                <input type="text" id="unit" list="unitList" placeholder="e.g. Pcs">
                <datalist id="unitList"></datalist>
            </div>
            <div>
                <label for="yearRate">Unit Rate (same across all rows)</label>
                <input type="number" id="yearRate" value="0" oninput="onYearRateChange()">
            </div>
        </div>

        <div class="row">
            <div>
                <label for="yearModeSelect">Entry Mode</label>
                <select id="yearModeSelect" onchange="onYearModeChange()">
                    <option value="month" selected>Monthly (12 months)</option>
                    <option value="year">Yearly (single line)</option>
                    <option value="quarter">Quarterly (split into parts)</option>
                </select>
            </div>
        </div>

        <div class="card" style="margin:1rem 0; background:#fafafa;">
            <div id="quarterCountWrap" style="display:none; margin-top:.5rem;">
                <label for="quarterCountSelect">How many Quarters do you want to create</label>
                <select id="quarterCountSelect" onchange="onQuarterCountChange()" style="max-width:120px;">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3" selected>3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
            </div>
            <div id="yearUnitsContainer" style="margin-top:.75rem;"></div>
        </div>

        <div id="quarterSection">
        <div class="row">
            <div><label>Quarter-1 (July-October) — No. of Unit</label><input type="number" id="q1_unit" value="0"></div>
            <div><label>Quarter-1 — Rate</label><input type="number" id="q1_rate" value="0"></div>
            <div><label>Quarter-2 (November-February) — No. of Unit</label><input type="number" id="q2_unit" value="0"></div>
            <div><label>Quarter-2 — Rate</label><input type="number" id="q2_rate" value="0"></div>
        </div>
        <div class="row">
            <div><label>Quarter-3 (March-June) — No. of Unit</label><input type="number" id="q3_unit" value="0"></div>
            <div><label>Quarter-3 — Rate</label><input type="number" id="q3_rate" value="0"></div>
            <div class="muted" style="align-self:flex-end;">Year-1 Total and Grand Total are calculated automatically by the system after submission.</div>
        </div>
        </div>

        <div class="row">
            <div style="flex:1;">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" rows="2"></textarea>
            </div>
        </div>

        <div style="margin-top:1rem; display:flex; gap:.5rem;">
            <button class="btn" onclick="savePackage()">Save Package</button>
            <button class="btn secondary" onclick="resetForm()">Cancel</button>
        </div>
    </div>
    
    <div class="card" style="margin:1rem 0;overflow-x:auto;">
        <table id="matrixTable" style="min-width:1400px;">
            <thead id="matrixThead"></thead>
            <tbody id="matrixBody"></tbody>
        </table>
    </div>
    
@endsection

@section('scripts')
<script>
   const planId = {{ (int) $id }};
const errorBox = document.getElementById('errorBox');
let categories = [];
let items = [];
let units = [];
let chartOfAccounts = [];

const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const FISCAL_MONTH_NAMES = ['July','August','September','October','November','December','January','February','March','April','May','June'];
const YEAR_SLOT_KEYS = ['previous_2nd_year', 'previous_1st_year', 'current_year', 'year_2_total', 'year_3_total'];
const DEFAULT_YEAR_SLOT = 'previous_1st_year';

function money(n) {
    return Number(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
}

function freshYearSlot() {
    return { mode: 'month', rate: 0, units: new Array(12).fill(0) };
}

let yearSlotData = {
    previous_2nd_year: freshYearSlot(),
    previous_1st_year: freshYearSlot(),
    current_year: freshYearSlot(),
    year_2_total: freshYearSlot(),
    year_3_total: freshYearSlot(),
};
let currentYearSlot = DEFAULT_YEAR_SLOT;
let quarterCount = 3; // user-selectable 1-5 when Entry Mode = Quarterly
let currentPlanType = 'project'; // set from the loaded plan; 'annual' plans default to Current Year

function defaultYearSlot() {
    return currentPlanType === 'annual' ? 'current_year' : DEFAULT_YEAR_SLOT;
}

function applyPlanTypeToYearOptions(plan) {
    currentPlanType = plan.plan_type;
    const isAnnual = currentPlanType === 'annual';

    // Annual plans default to Current Year for convenience, but every option
    // stays enabled/visible — you can still switch to Previous/Upcoming years if needed.
    document.getElementById('planTypeNote').style.display = isAnnual ? 'block' : 'none';

    if (isAnnual && currentYearSlot !== 'current_year') {
        currentYearSlot = 'current_year';
        document.getElementById('yearSlotSelect').value = 'current_year';
        yearSlotData.current_year = freshYearSlot();
        renderYearPanel();
    }
}

function countForMode(mode) {
    if (mode === 'month') return 12;
    if (mode === 'quarter') return quarterCount;
    return 1;
}

function labelsForMode(mode, slotKey) {
    if (mode === 'month') return slotKey === 'current_year' ? FISCAL_MONTH_NAMES : MONTH_NAMES;
    if (mode === 'quarter') return Array.from({ length: quarterCount }, (_, i) => `Quarter-${i + 1}`);
    return ['Total'];
}

function setFieldValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value;
}

function renderYearPanel() {
    const slot = yearSlotData[currentYearSlot];
    document.getElementById('yearModeSelect').value = slot.mode;
    document.getElementById('yearRate').value = slot.rate;

    const showQuarterCount = slot.mode === 'quarter';
    document.getElementById('quarterCountWrap').style.display = showQuarterCount ? 'block' : 'none';
    if (showQuarterCount) document.getElementById('quarterCountSelect').value = quarterCount;

    const labels = labelsForMode(slot.mode, currentYearSlot);
    const container = document.getElementById('yearUnitsContainer');

    container.innerHTML = `
        <div class="row" style="flex-wrap:wrap;">
            ${labels.map((label, i) => `
                <div style="min-width:140px;">
                    <label>${label} — No. of Unit</label>
                    <input type="number" value="${slot.units[i] ?? 0}" oninput="onYearUnitChange(${i}, this.value)">
                </div>
            `).join('')}
        </div>
    `;
}

function onYearSlotChange() {
    currentYearSlot = document.getElementById('yearSlotSelect').value;
    if (currentYearSlot === 'current_year') {
        yearSlotData.current_year = freshYearSlot();
    }
    renderYearPanel();
}

function onYearModeChange() {
    const mode = document.getElementById('yearModeSelect').value;
    const slot = yearSlotData[currentYearSlot];
    if (mode === 'quarter') quarterCount = 3; // reset to default whenever Quarterly is freshly selected
    const count = countForMode(mode);
    const newUnits = new Array(count).fill(0);
    for (let i = 0; i < Math.min(count, slot.units.length); i++) newUnits[i] = slot.units[i];
    slot.mode = mode;
    slot.units = newUnits;
    renderYearPanel();
}

function onQuarterCountChange() {
    quarterCount = parseInt(document.getElementById('quarterCountSelect').value, 10) || 3;
    const slot = yearSlotData[currentYearSlot];
    const newUnits = new Array(quarterCount).fill(0);
    for (let i = 0; i < Math.min(quarterCount, slot.units.length); i++) newUnits[i] = slot.units[i];
    slot.units = newUnits;
    renderYearPanel();
}

function onYearRateChange() {
    yearSlotData[currentYearSlot].rate = document.getElementById('yearRate').value;
}

function onYearUnitChange(index, value) {
    yearSlotData[currentYearSlot].units[index] = value;
}

function buildYearSlotPayload(key) {
    const slot = yearSlotData[key];

    if (slot.mode === 'year') {
        return { no_of_unit: slot.units[0] ?? 0, rate: slot.rate };
    }

    return {
        granularity: slot.mode,
        entries: slot.units.map(u => ({ no_of_unit: u, rate: slot.rate })),
    };
}

function loadYearSlotFromPeriod(key, period) {
    if (!period) {
        yearSlotData[key] = freshYearSlot();
        return;
    }

    if (period.breakdown_granularity) {
        const entries = period.entries || [];
        yearSlotData[key] = {
            mode: period.breakdown_granularity,
            rate: entries[0]?.rate ?? 0,
            units: entries.map(e => e.no_of_unit),
        };
    } else {
        yearSlotData[key] = {
            mode: 'year',
            rate: period.rate ?? 0,
            units: [period.no_of_unit ?? 0],
        };
    }
}

function onItemSelected() {
    const typed = document.getElementById('itemSelect').value.trim();
    const list = window.__currentGroupItems || [];
    const match = list.find(i => i.name.toLowerCase() === typed.toLowerCase());

    if (match) {
        document.getElementById('itemId').value = match.id;
        document.getElementById('budgetedHead').value = match.name;
    } else {
        // typed a new name not in the list -> treat as "new item"
        document.getElementById('itemId').value = '';
        document.getElementById('budgetedHead').value = typed;
    }
}

async function loadCategories() {
    const [catRes, itemRes, unitRes, coaRes] = await Promise.all([
        api.get('/procurement-categories?per_page=200'),
        api.get('/items?per_page=2000'),
        api.get('/units?per_page=500'),
        api.get('/chart-of-accounts?per_page=500'),
    ]);
    categories = catRes.data;
    items = itemRes.data;
    units = unitRes.data;
    chartOfAccounts = coaRes.data;

    document.getElementById('category').innerHTML = categories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
    document.getElementById('unitList').innerHTML =
        units.map(u => `<option value="${u.name}"></option>`).join('');

    onCategoryChanged();
}

function onCategoryChanged() {
    const categoryId = document.getElementById('category').value;
    const groups = chartOfAccounts
        .filter(g => String(g.category_id) === String(categoryId))
        .filter(g => g.code !== 'C51');

    document.getElementById('groupSelect').innerHTML =
        '<option value="">-- Select Group --</option>' +
        groups.map(g => `<option value="${g.id}">${g.code} — ${g.name}</option>`).join('');

    onGroupChanged();
}

function onGroupChanged() {
    const groupId = document.getElementById('groupSelect').value;
    const filteredItems = groupId ? items.filter(i => String(i.chart_of_account_id) === String(groupId)) : [];

    document.getElementById('itemDatalist').innerHTML =
        filteredItems.map(i => `<option value="${i.name}" data-id="${i.id}"></option>`).join('');
    document.getElementById('itemSelect').value = '';
    document.getElementById('itemId').value = '';

    window.__currentGroupItems = filteredItems; // keep for lookup in onItemSelected
}

async function load() {
    try {
        const [{ data: plan }] = await Promise.all([
            api.get(`/procurement-annual-plans/${planId}`),
            categories.length ? Promise.resolve(null) : loadCategories(),
        ]);
        renderHeader(plan);
        renderMatrix(plan);
        updateYearSlotLabels(plan);
        applyPlanTypeToYearOptions(plan);
    } catch (err) {
        errorBox.textContent = err.message;
        errorBox.style.display = 'block';
    }
}

function updateYearSlotLabels(plan) {
    if (!plan.fiscal_year_start) return;
    const y = new Date(plan.fiscal_year_start).getFullYear() + 2;

    document.getElementById('optPrev2').textContent = `FY ${y}-${y + 1}`;
    document.getElementById('optPrev1').textContent = `FY ${y - 1}-${y}`;
    document.getElementById('optCurrent').textContent = `FY ${y -2}-${y - 1} (Current Year)`;
    document.getElementById('optYear2').textContent = `FY ${y + 1}-${y + 2} (Upcoming)`;
    document.getElementById('optYear3').textContent = `FY ${y + 2}-${y + 3} (Upcoming)`;
}

function renderHeader(plan) {
    document.getElementById('planHeader').innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
            <div>
                <h1 style="margin:0;">${plan.title}</h1>
                <span class="muted">${plan.plan_type} · ${plan.donor_name ?? ''}</span>
            </div>
            <div style="display:flex; gap:.5rem;">
                <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/pdf/preview" target="_blank">Preview</a>
                <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/pdf" target="_blank">Download PDF</a>
                <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/excel" target="_blank">Download Excel</a>
            </div>
        </div>
        <div class="card row" style="margin-top:1rem;">
            <div><span class="muted">Project Name/Title</span><br>${plan.project_name ?? plan.title}</div>
            <div><span class="muted">Project Location (Office)</span><br>${plan.project_location ?? '-'}</div>
            <div><span class="muted">Project Working Area</span><br>${plan.working_area ?? '-'}</div>
            <div><span class="muted">Project Duration</span><br>${plan.project_duration ?? (plan.fiscal_year_start + ' → ' + plan.fiscal_year_end)}</div>
            <div><span class="muted">Date of Agreement/Awarded</span><br>${plan.agreement_date ?? '-'}</div>
            <div><span class="muted">Donor Name</span><br>${plan.donor_name ?? '-'}</div>
        </div>
        ${plan.activity_summary ? `<div class="card" style="margin-top:1rem;"><span class="muted">Activity Summary</span><p style="margin:.4rem 0 0;">${plan.activity_summary}</p></div>` : ''}
    `;
}

const MATRIX_GROUP_LABELS = [
    'Previous 2nd Year', 'Previous 1st Year', 'Quarter-1', 'Quarter-2', 'Quarter-3',
    'Total of Year-1', 'Total of Year-2', 'Total of Year-3', 'Grand Total',
];

function computeVisiblePeriods(packages) {
    if (!packages.length) return new Array(9).fill(true);
    const visible = new Array(9).fill(false);
    packages.forEach(pkg => {
        (pkg.periods || []).forEach((p, i) => {
            if (Number(p.no_of_unit || 0) !== 0 || Number(p.rate || 0) !== 0 || Number(p.total || 0) !== 0) {
                visible[i] = true;
            }
        });
    });
    return visible;
}

function renderMatrixHead(visible) {
    const groupHeaders = MATRIX_GROUP_LABELS
        .map((label, i) => visible[i] ? `<th colspan="3">${label}</th>` : '')
        .join('');
    const subHeaders = visible.filter(Boolean)
        .map(() => `<th>No. of Unit</th><th>Rate</th><th>Total</th>`)
        .join('');

    document.getElementById('matrixThead').innerHTML = `
        <tr>
            <th rowspan="2">Sl.No</th>
            <th rowspan="2">Category</th>
            <th rowspan="2">Sub Category</th>
            <th rowspan="2">Item Name</th>
            <th rowspan="2">Specification</th>
            <th rowspan="2">Unit</th>
            ${groupHeaders}
            <th rowspan="2">Already Procured</th>
            <th rowspan="2">Remaining Balance</th>
            <th rowspan="2">Remarks</th>
            <th rowspan="2">Action</th>
        </tr>
        <tr>${subHeaders}</tr>
    `;
}

function renderMatrix(plan) {
    const packages = plan.packages || [];
    const visible = computeVisiblePeriods(packages);
    const visibleCount = visible.filter(Boolean).length;

    renderMatrixHead(visible);

    document.getElementById('matrixBody').innerHTML = packages.map((pkg, idx) => {
        const cellsHtml = pkg.periods.map((p, i) => visible[i] ? `
            <td style="text-align:right;">${p.no_of_unit ? Number(p.no_of_unit).toLocaleString() : ''}</td>
            <td style="text-align:right;">${p.rate ? money(p.rate) : ''}</td>
            <td style="text-align:right;"><strong>${p.total ? money(p.total) : ''}</strong></td>
        ` : '').join('');

        return `
            <tr>
                <td>${pkg.sl_no ?? (idx + 1)}</td>
                <td>${pkg.category.name}</td>
                <td>${chartOfAccounts.find(g => g.id === (pkg.chart_of_account_id ?? pkg.item?.chart_of_account_id))?.name ?? ''}</td>
                <td>${pkg.budgeted_head}</td>
                <td>${pkg.specification ?? ''}</td>
                <td>${pkg.unit ?? ''}</td>
                ${cellsHtml}
                <td style="text-align:right;">${money(pkg.already_procured)}</td>
                <td style="text-align:right; ${pkg.remaining_balance < 0 ? 'color:#991b1b;font-weight:700;' : ''}">${money(pkg.remaining_balance)}</td>
                <td>${pkg.remarks ?? ''}</td>
                <td><button class="btn secondary" onclick="editPackage(${pkg.id})">Edit</button></td>
            </tr>
        `;
    }).join('') || `<tr><td colspan="${5 + visibleCount * 3 + 4}" class="muted">No packages yet — add one using the form below.</td></tr>`;

    if (packages.length) {
        const periodTotals = new Array(9).fill(0);
        let alreadyProcuredSum = 0, remainingBalanceSum = 0;

        packages.forEach(pkg => {
            pkg.periods.forEach((p, idx) => { periodTotals[idx] += Number(p.total || 0); });
            alreadyProcuredSum += Number(pkg.already_procured || 0);
            remainingBalanceSum += Number(pkg.remaining_balance || 0);
        });

        document.getElementById('matrixBody').innerHTML += `
            <tr style="font-weight:700; background:#f3f4f6;">
                <td colspan="6">Total</td>
                ${periodTotals.map((t, i) => visible[i] ? `<td></td><td></td><td style="text-align:right;">${money(t)}</td>` : '').join('')}
                <td style="text-align:right;">${money(alreadyProcuredSum)}</td>
                <td style="text-align:right;">${money(remainingBalanceSum)}</td>
                <td></td><td></td>
            </tr>
        `;
    }

    window.__packages = packages;
}

function editPackage(id) {
    const pkg = window.__packages.find(p => p.id === id);
    if (!pkg) return;

    document.getElementById('editingId').value = pkg.id;
    document.getElementById('formTitle').textContent = `Edit Package — ${pkg.package_number}`;
    document.getElementById('category').value = pkg.category.id;
    onCategoryChanged();

    // Older packages saved before the Group field existed have no chart_of_account_id
    // of their own — fall back to the linked item's group so editing still shows the right one.
    const groupId = pkg.chart_of_account_id ?? pkg.item?.chart_of_account_id ?? '';
    document.getElementById('groupSelect').value = groupId;
    onGroupChanged();

    document.getElementById('itemSelect').value = pkg.item?.name ?? '';
    document.getElementById('itemId').value = pkg.item_id ?? '';
    document.getElementById('budgetedHead').value = pkg.budgeted_head;
    document.getElementById('specification').value = pkg.specification ?? '';
    document.getElementById('unit').value = pkg.unit ?? '';
    document.getElementById('remarks').value = pkg.remarks ?? '';

    const find = (label) => pkg.periods.find(p => p.period_label === label);
    const byType = (type, year) => pkg.periods.find(p => p.period_type === type && p.year_number === year);

    const q1 = find('Quarter-1 (July-October)') || {}, q2 = find('Quarter-2 (November-February)') || {}, q3 = find('Quarter-3 (March-June)') || {};
    document.getElementById('q1_unit').value = q1.no_of_unit ?? 0; document.getElementById('q1_rate').value = q1.rate ?? 0;
    document.getElementById('q2_unit').value = q2.no_of_unit ?? 0; document.getElementById('q2_rate').value = q2.rate ?? 0;
    document.getElementById('q3_unit').value = q3.no_of_unit ?? 0; document.getElementById('q3_rate').value = q3.rate ?? 0;

    const previousYears = pkg.periods.filter(p => p.period_type === 'previous_year');
    loadYearSlotFromPeriod('previous_2nd_year', previousYears[0]);
    loadYearSlotFromPeriod('previous_1st_year', previousYears[1]);
    loadYearSlotFromPeriod('current_year', pkg.periods.find(p => p.period_type === 'current_year'));
    loadYearSlotFromPeriod('year_2_total', byType('year_total', 2));
    loadYearSlotFromPeriod('year_3_total', byType('year_total', 3));

    quarterCount = 3;

    currentYearSlot = defaultYearSlot();
    document.getElementById('yearSlotSelect').value = currentYearSlot;
    renderYearPanel();

    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('editingId').value = '';
    document.getElementById('formTitle').textContent = 'Add New Package';
    ['budgetedHead', 'specification', 'unit', 'remarks', 'itemSelect', 'itemId'].forEach(id => document.getElementById(id).value = '');
    ['q1_unit', 'q1_rate', 'q2_unit', 'q2_rate', 'q3_unit', 'q3_rate'].forEach(id => document.getElementById(id).value = 0);

    YEAR_SLOT_KEYS.forEach(key => { yearSlotData[key] = freshYearSlot(); });
    currentYearSlot = defaultYearSlot();
    quarterCount = 3;
    document.getElementById('yearSlotSelect').value = currentYearSlot;
    renderYearPanel();
    onCategoryChanged();
}

async function savePackage() {
    errorBox.style.display = 'none';
    const categoryId = document.getElementById('category').value;
    const budgetedHead = document.getElementById('budgetedHead').value.trim();

    if (!categoryId || !budgetedHead) {
        errorBox.textContent = 'Please provide Category and Item Name.';
        errorBox.style.display = 'block';
        return;
    }

    const payload = {
        procurement_category_id: categoryId,
        chart_of_account_id: document.getElementById('groupSelect').value || null,
        budgeted_head: budgetedHead,
        item_id: document.getElementById('itemId').value || null,
        specification: document.getElementById('specification').value || null,
        unit: document.getElementById('unit').value || null,
        remarks: document.getElementById('remarks').value || null,
        periods: {
            previous_2nd_year: buildYearSlotPayload('previous_2nd_year'),
            previous_1st_year: buildYearSlotPayload('previous_1st_year'),
            current_year: buildYearSlotPayload('current_year'),
            quarter_1: { no_of_unit: document.getElementById('q1_unit').value, rate: document.getElementById('q1_rate').value },
            quarter_2: { no_of_unit: document.getElementById('q2_unit').value, rate: document.getElementById('q2_rate').value },
            quarter_3: { no_of_unit: document.getElementById('q3_unit').value, rate: document.getElementById('q3_rate').value },
            year_2_total: buildYearSlotPayload('year_2_total'),
            year_3_total: buildYearSlotPayload('year_3_total'),
        },
    };

    const editingId = document.getElementById('editingId').value;

    try {
        if (editingId) {
            await api.put(`/procurement-plan-packages/${editingId}`, payload);
        } else {
            await api.post(`/procurement-annual-plans/${planId}/packages`, payload);
        }
        resetForm();
        load();
    } catch (err) {
        errorBox.textContent = err.message;
        errorBox.style.display = 'block';
    }
}

renderYearPanel();
load();
</script>
@endsection