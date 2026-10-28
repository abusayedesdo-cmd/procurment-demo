@extends('layouts.app')

@section('title', 'Procurement Annual Plan')

@section('content')
    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="planHeader">Loading...</div>


    <div class="card" id="packageFormCard">
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
                <label for="groupSelect">Procurement Group No
                    <a href="#" id="newGroupLink" onclick="toggleNewGroup(event)" style="display:none; margin-left:.4rem; font-weight:normal;">+ New group</a>
                </label>
                <select id="groupSelect" onchange="onGroupChanged()">
                    <option value="">-- Select Category first --</option>
                </select>
            </div>
            <div style="position:relative;">
                <label for="itemSelect">Item (select if exists)</label>
                <input type="text" id="itemSelect" placeholder="-- Select / Type New --" oninput="onItemSelected()" onfocus="renderItemSuggest()" onblur="hideItemSuggest(event)" autocomplete="off">
                <div id="itemSuggest" style="display:none; position:absolute; left:0; right:0; top:100%; z-index:50; max-height:240px; overflow-y:auto; background:#fff; border:1px solid #cbd5e1; border-radius:6px; box-shadow:0 6px 16px rgba(0,0,0,.12);"></div>
                <input type="hidden" id="budgetedHead" value="">
                <input type="hidden" id="itemId" value="">
            </div>
            <div>
                <label for="specification">Specification</label>
                <input type="text" id="specification">
            </div>
            <div style="position:relative;">
                <label for="unit">Unit</label>
                <input type="text" id="unit" placeholder="e.g. Pcs" oninput="renderUnitSuggest()" onfocus="renderUnitSuggest()" onblur="hideUnitSuggest()" autocomplete="off">
                <div id="unitSuggest" style="display:none; position:absolute; left:0; right:0; top:100%; z-index:50; max-height:240px; overflow-y:auto; background:#fff; border:1px solid #cbd5e1; border-radius:6px; box-shadow:0 6px 16px rgba(0,0,0,.12);"></div>
            </div>
            <div>
                <label for="yearRate">Unit Rate (same across all rows)</label>
                <input type="number" id="yearRate" value="0" oninput="onYearRateChange()">
            </div>
        </div>

        <div id="addToListWrap" style="display:none; margin:.25rem 0 .75rem;">
            <label id="addToListLabel" style="font-weight:normal; display:flex; gap:.4rem; align-items:center;">
                <input type="checkbox" id="addToList" checked style="width:auto;">
                Add this new item to the Items list
            </label>
            <div class="muted" id="addToListHint"></div>
        </div>

        <div id="newGroupBox" class="card" style="display:none; margin:.25rem 0 .75rem; background:#fafafa;">
            <div class="row">
                <div>
                    <label for="newGroupCode">Group Code</label>
                    <input type="text" id="newGroupCode" placeholder="e.g. COA-1299">
                </div>
                <div>
                    <label for="newGroupName">Group Name</label>
                    <input type="text" id="newGroupName" placeholder="e.g. Office Furniture">
                </div>
            </div>
            <div class="muted" id="newGroupHint"></div>
            <div style="margin-top:.5rem;">
                <button type="button" class="btn" onclick="saveNewGroup()">Save Group</button>
                <button type="button" class="btn secondary" onclick="toggleNewGroup()">Cancel</button>
                <span id="newGroupMsg" class="muted" style="margin-left:.5rem;"></span>
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

        <div class="muted" style="margin-top:.5rem;">Year-1 Total and Grand Total are calculated automatically by the system after submission, from whatever you enter for Current Year above.</div>

        <div class="row">
            <div style="flex:1;">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" rows="2"></textarea>
            </div>
        </div>

        <div style="margin-top:1rem; display:flex; gap:.5rem;">
            <button class="btn" id="savePackageBtn" onclick="savePackage()">Save Package</button>
            <button class="btn secondary" onclick="resetForm()">Cancel</button>
        </div>
    </div>
    <p class="muted" id="packageFormReadOnlyNote" style="display:none;">Packages are added by the Accountant. You're viewing this plan's packages below.</p>

    <div class="card" style="margin:1rem 0;overflow-x:auto;">
        <table id="matrixTable" style="min-width:1400px;">
            <thead id="matrixThead"></thead>
            <tbody id="matrixBody"></tbody>
        </table>
    </div>
    
@endsection

@section('scripts')
<div id="importModal" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(15,23,42,.55); align-items:flex-start; justify-content:center; overflow:auto; padding:2rem 1rem;">
  <div style="background:#fff; border-radius:10px; width:min(1200px,100%); padding:1.25rem 1.5rem; box-shadow:0 12px 40px rgba(0,0,0,.3);">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:.75rem;">
      <h2 style="margin:0;">Import packages from Excel</h2>
      <button type="button" class="btn secondary" onclick="closeImport()">Close</button>
    </div>
    <p class="muted" style="margin:.25rem 0 .75rem;">Upload the Annual Procurement Plan Excel, pick this plan's project, check the preview, then import. Nothing is saved until you press <b>Import</b>.</p>
    <div style="display:flex; gap:1rem; flex-wrap:wrap; align-items:flex-end;">
      <div><label>Excel file (.xlsx)</label><input type="file" id="importFileInput" accept=".xlsx,.xls" onchange="onImportFileChosen(this)"></div>
      <div id="importProjectWrap" style="display:none; min-width:340px; flex:1;"><label>Project in the file</label><select id="importProject" onchange="importGroupMap = {}; runImportPreview()"></select></div>
    </div>
    <div id="importNote" class="muted" style="margin-top:.5rem; font-size:.85rem;"></div>
    <div id="importMsg" style="margin-top:.5rem;"></div>
    <div id="importBody" style="margin-top:.75rem;"></div>
    <div style="display:flex; justify-content:flex-end; gap:.5rem; margin-top:1rem;">
      <button type="button" class="btn secondary" onclick="closeImport()">Cancel</button>
      <button type="button" class="btn" id="importConfirmBtn" onclick="importCommit()" disabled>Import</button>
    </div>
  </div>
</div>

<script>
    const CAN_MANAGE_PACKAGES = window.currentUserRole === 'admin' || window.currentUserRole === 'budget_checker';
    const CAN_ADD_GROUP = window.currentUserRole === 'admin';   // new Procurement Groups: Admin only

    // Same name regardless of case / extra spaces ("Laptop " == "laptop").
    function normName(s) { return String(s ?? '').trim().replace(/\s+/g, ' ').toLowerCase(); }
    // "2026-07-01T00:00:00.000000Z" -> "01 Jul 2026" (date only, no time, no timezone shift).
    function formatDateOnly(value) {
        if (!value) return '-';
        const m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (!m) return String(value);
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return `${m[3]} ${months[parseInt(m[2], 10) - 1]} ${m[1]}`;
    }

    function escHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }
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

    function renderItemSuggest() {
        const box = document.getElementById('itemSuggest');
        if (!box || window.__itemAddMode) return;
        const typed = document.getElementById('itemSelect').value.trim().toLowerCase();
        const list = (window.__currentGroupItems || [])
            .filter(i => typed === '' || String(i.name).toLowerCase().includes(typed));

        if (list.length === 0) {
            box.innerHTML = `<div style="padding:.5rem .75rem; color:#64748b; font-size:.85rem;">${
                typed ? 'No match. This will be saved as a new item.' : 'No existing items here. Type a new item name.'}</div>`;
        } else {
            box.innerHTML = list.map(i =>
                `<div data-id="${i.id}" onmousedown="pickItem(${i.id})" style="padding:.45rem .75rem; cursor:pointer; border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">${escHtml(i.name)}</div>`
            ).join('');
        }
        // Footer action: add a new item right from the dropdown.
        const rawTyped = document.getElementById('itemSelect').value.trim();
        const exact = (window.__currentGroupItems || []).some(i => normName(i.name) === normName(rawTyped));
        if (CAN_MANAGE_PACKAGES && !exact) {
            const label = rawTyped ? `+ Add "${escHtml(rawTyped)}" as a new item` : '+ Add a new item (type the name first)';
            box.innerHTML += `<div onmousedown="addNewItemFromDropdown(event)" style="padding:.5rem .75rem; cursor:pointer; font-weight:600; color:#0f766e; background:#f0fdfa; position:sticky; bottom:0;">${label}</div>`;
        }
        box.style.display = 'block';
    }

    // Click on "+ Add ... new item" in the dropdown.
    function addNewItemFromDropdown(e) {
        if (e) e.preventDefault();               // keep focus so the input does not blur first
        const name = document.getElementById('itemSelect').value.trim();
        if (name) { createItem(name); return; }
        showNewItemPanel();                      // nothing typed yet -> ask for the name right here
    }

    // Small panel inside the dropdown: type the new item's name and save it.
    function showNewItemPanel(message) {
        const box = document.getElementById('itemSuggest');
        window.__itemAddMode = true;
        box.innerHTML = `
            <div style="padding:.6rem .75rem;">
                <div style="font-weight:600; margin-bottom:.35rem;">New item name</div>
                <input type="text" id="newItemName" placeholder="e.g. Training materials" autocomplete="off"
                       style="width:100%; box-sizing:border-box;"
                       onkeydown="if(event.key==='Enter'){event.preventDefault();saveNewItemPanel();} else if(event.key==='Escape'){cancelNewItemPanel();}"
                       onblur="hideItemSuggest(event)">
                <div id="newItemMsg" style="color:#b91c1c; font-size:.85rem; margin-top:.3rem;">${message ? escHtml(message) : ''}</div>
                <div style="display:flex; gap:.4rem; margin-top:.5rem;">
                    <button type="button" onmousedown="event.preventDefault(); saveNewItemPanel()">Save item</button>
                    <button type="button" onmousedown="event.preventDefault(); cancelNewItemPanel()">Cancel</button>
                </div>
            </div>`;
        box.style.display = 'block';
        document.getElementById('newItemName').focus();
    }

    function saveNewItemPanel() {
        const name = document.getElementById('newItemName').value.trim();
        if (!name) { document.getElementById('newItemMsg').textContent = 'Please type the item name.'; return; }
        createItem(name);
    }

    function cancelNewItemPanel() {
        window.__itemAddMode = false;
        hideItemSuggest();
        document.getElementById('itemSelect').focus();
    }

    // Saves a new item under the selected Group and puts it in the Item field.
    async function createItem(name) {
        const input = document.getElementById('itemSelect');
        const groupId = document.getElementById('groupSelect').value;
        const inPanel = !!window.__itemAddMode;
        const fail = msg => {
            if (inPanel) { const m = document.getElementById('newItemMsg'); if (m) m.textContent = msg; return; }
            showNewItemPanel(msg);               // show the message inside the dropdown, not far up the page
        };

        if (!groupId) { fail('Select a Procurement Group first, then add the new item.'); return; }

        try {
            const { data: created } = await api.post('/items', {
                chart_of_account_id: groupId,
                name,
                specification: document.getElementById('specification').value.trim() || null,
            });
            items.push(created);
            window.__itemAddMode = false;
            onGroupChanged();                    // rebuild the list so the new item appears
            input.value = created.name;
            onItemSelected();
            hideItemSuggest();
        } catch (err) {
            fail(err.message);
        }
    }

    // ---- Unit dropdown (with "+ Add new unit") ----
    function renderUnitSuggest() {
        const box = document.getElementById('unitSuggest');
        if (!box) return;
        const raw = document.getElementById('unit').value.trim();
        const typed = raw.toLowerCase();
        const list = units.filter(u => typed === '' || String(u.name).toLowerCase().includes(typed));

        let html = list.map(u =>
            `<div onmousedown="pickUnit(${u.id})" style="padding:.45rem .75rem; cursor:pointer; border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background=''">${escHtml(u.name)}</div>`
        ).join('');
        if (!list.length) {
            html = '<div style="padding:.5rem .75rem; color:#64748b; font-size:.85rem;">No units found.</div>';
        }
        const exact = units.some(u => normName(u.name) === normName(raw));
        if (raw && !exact) {
            html += `<div onmousedown="addNewUnit(event)" style="padding:.5rem .75rem; cursor:pointer; font-weight:600; color:#0f766e; background:#f0fdfa; position:sticky; bottom:0;">+ Add "${escHtml(raw)}" as a new unit</div>`;
        }
        box.innerHTML = html;
        box.style.display = 'block';
    }

    function hideUnitSuggest() {
        const box = document.getElementById('unitSuggest');
        if (box) box.style.display = 'none';
    }

    function pickUnit(id) {
        const u = units.find(x => String(x.id) === String(id));
        if (u) document.getElementById('unit').value = u.name;
        hideUnitSuggest();
    }

    async function addNewUnit(e) {
        if (e) e.preventDefault();
        const input = document.getElementById('unit');
        const name = input.value.trim();
        const errorBox = document.getElementById('errorBox');
        errorBox.style.display = 'none';
        if (!name) return;
        try {
            const { data: created } = await api.post('/units', { name });
            units.push(created);
            input.value = created.name;
            hideUnitSuggest();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            hideUnitSuggest();
        }
    }

    function hideItemSuggest(ev) {
        const box = document.getElementById('itemSuggest');
        if (!box) return;
        // Focus moving between the Item field and the "new item" panel must not close it.
        const to = ev && ev.relatedTarget;
        if (to && (box.contains(to) || to.id === 'itemSelect')) return;
        window.__itemAddMode = false;
        box.style.display = 'none';
    }

    function pickItem(id) {
        const item = (window.__currentGroupItems || []).find(i => String(i.id) === String(id));
        if (!item) return;
        document.getElementById('itemSelect').value = item.name;
        onItemSelected();
        hideItemSuggest();
    }

    function onItemSelected() {
        const typed = document.getElementById('itemSelect').value.trim();
        const list = window.__currentGroupItems || [];
        const match = list.find(i => normName(i.name) === normName(typed));

        if (match) {
            // Item picked while only the Category is selected -> fill the matching Group automatically.
            const groupEl = document.getElementById('groupSelect');
            const matchGroupId = match.chart_of_account_id ?? match.chart_of_account?.id;
            if (!groupEl.value && matchGroupId) {
                groupEl.value = String(matchGroupId);
                onGroupChanged();
                document.getElementById('itemSelect').value = match.name;
            }
            document.getElementById('itemId').value = match.id;
            document.getElementById('budgetedHead').value = match.name;
        } else {
            // typed a new name not in the list -> treat as "new item"
            document.getElementById('itemId').value = '';
            document.getElementById('budgetedHead').value = typed;
        }
        if (document.activeElement === document.getElementById('itemSelect')) renderItemSuggest();
        refreshAddToList();
    }

    // Shows the "Add this new item to the Items list" box only when the typed item is new.
    function refreshAddToList() {
        const wrap = document.getElementById('addToListWrap');
        if (!wrap) return;

        const name = document.getElementById('budgetedHead').value.trim();
        const groupId = document.getElementById('groupSelect').value;
        const hasItem = !!document.getElementById('itemId').value;
        const label = document.getElementById('addToListLabel');
        const hint = document.getElementById('addToListHint');
        const box = document.getElementById('addToList');

        if (!CAN_MANAGE_PACKAGES || name === '' || hasItem) { wrap.style.display = 'none'; return; }

        const existing = groupId && items.find(i =>
            String(i.chart_of_account_id ?? i.chart_of_account?.id) === String(groupId) && normName(i.name) === normName(name));

        wrap.style.display = 'block';
        if (existing) {
            label.style.display = 'none';
            hint.textContent = 'This item already exists in the list. It will be linked automatically, no duplicate will be created.';
            return;
        }

        label.style.display = 'flex';
        box.disabled = !groupId;
        hint.textContent = groupId
            ? 'Next time it will appear in the "Item" dropdown for this group.'
            : 'Select a Procurement Group first to add this item to the Items list.';
    }

    function toggleNewGroup(e) {
        if (e) e.preventDefault();
        const box = document.getElementById('newGroupBox');
        const open = box.style.display === 'none';
        box.style.display = open ? 'block' : 'none';
        if (open) {
            const cat = categories.find(c => String(c.id) === String(document.getElementById('category').value));
            document.getElementById('newGroupHint').textContent = `It will be added under the category: ${cat ? cat.name : '(none selected)'}.`;
            document.getElementById('newGroupMsg').textContent = '';
            document.getElementById('newGroupCode').focus();
        }
    }

    async function saveNewGroup() {
        const msg = document.getElementById('newGroupMsg');
        const categoryId = document.getElementById('category').value;
        const code = document.getElementById('newGroupCode').value.trim();
        const name = document.getElementById('newGroupName').value.trim();
        msg.style.color = '';

        if (!categoryId || !code || !name) {
            msg.textContent = 'Category, Group Code and Group Name are all required.';
            msg.style.color = '#b91c1c';
            return;
        }

        const keepHead = document.getElementById('budgetedHead').value;
        const dup = chartOfAccounts.find(g => String(g.category_id) === String(categoryId) && normName(g.code) === normName(code));
        let groupId;

        if (dup) {
            groupId = dup.id;
            msg.textContent = 'That group code already exists, so it was selected for you.';
        } else {
            try {
                const { data: created } = await api.post('/chart-of-accounts', { category_id: categoryId, code, name });
                chartOfAccounts.push(created);
                groupId = created.id;
                msg.textContent = `Group ${created.code} added.`;
            } catch (err) {
                msg.textContent = err.message;
                msg.style.color = '#b91c1c';
                return;
            }
        }

        onCategoryChanged();                       // rebuild the Group list (includes the new one)
        document.getElementById('groupSelect').value = groupId;
        onGroupChanged();
        if (keepHead) {                            // keep an item name the user had already typed
            document.getElementById('itemSelect').value = keepHead;
            onItemSelected();
        }

        document.getElementById('newGroupCode').value = '';
        document.getElementById('newGroupName').value = '';
        document.getElementById('newGroupBox').style.display = 'none';
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

        onCategoryChanged();
    }

    function onCategoryChanged() {
        const categoryId = document.getElementById('category').value;
        const groups = chartOfAccounts
            .filter(g => String(g.category_id) === String(categoryId))
            .filter(g => g.code !== 'C51');

        document.getElementById('groupSelect').innerHTML =
            '<option value="">-- Select Group --</option>' +
            groups.map(g => `<option value="${g.id}" title="${escHtml(g.name)}">${escHtml(g.code)}</option>`).join('');

        onGroupChanged();
    }

    function onGroupChanged() {
        const groupId = document.getElementById('groupSelect').value;
        let filteredItems = [];
        if (groupId) {
            filteredItems = items.filter(i => String(i.chart_of_account_id) === String(groupId));
        } else {
            // Only the Category is selected: offer every item under that category's groups.
            const categoryId = document.getElementById('category').value;
            const groupIds = chartOfAccounts
                .filter(g => String(g.category_id) === String(categoryId))
                .map(g => String(g.id));
            filteredItems = items.filter(i => groupIds.includes(String(i.chart_of_account_id ?? i.chart_of_account?.id)));
        }

        document.getElementById('itemSelect').value = '';
        document.getElementById('itemId').value = '';

        window.__currentGroupItems = filteredItems; // keep for lookup in onItemSelected
        refreshAddToList();
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
                    ${CAN_MANAGE_PACKAGES ? '<button type="button" class="btn secondary" onclick="openImport()">Import Excel</button>' : ''}
                    <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/pdf/preview" target="_blank">Preview</a>
                    <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/word" target="_blank">Download Word</a>
                    <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/pdf" target="_blank">Download PDF</a>
                    <a class="btn secondary" href="/api/procurement-annual-plans/${planId}/excel" target="_blank">Download Excel</a>
                </div>
            </div>
            <div class="card row" style="margin-top:1rem;">
                <div><span class="muted">Project Name/Title</span><br>${plan.project_name ?? plan.title}</div>
                <div><span class="muted">Project Location (Office)</span><br>${plan.project_location ?? '-'}</div>
                <div><span class="muted">Project Working Area</span><br>${plan.working_area ?? '-'}</div>
                <div><span class="muted">Project Duration</span><br>${plan.project_duration ?? (plan.fiscal_year_start + ' → ' + plan.fiscal_year_end)}</div>
                <div><span class="muted">Date of Agreement/Awarded</span><br>${formatDateOnly(plan.agreement_date)}</div>
                <div><span class="muted">Donor Name</span><br>${plan.donor_name ?? '-'}</div>
            </div>
            ${plan.activity_summary ? `<div class="card" style="margin-top:1rem;"><span class="muted">Activity Summary</span><p style="margin:.4rem 0 0;">${plan.activity_summary}</p></div>` : ''}
        `;
    }

    // Positions here must match ProcurementPlanPackage::SLOT_ORDER exactly —
    // this array is indexed positionally against pkg.periods (which the API
    // returns ordered by slot_order), not by label. Quarter-1/2/3 stay in the
    // list (so later slots keep their correct position) but will never show:
    // nothing writes to them any more, so computeVisiblePeriods() always finds
    // them empty.
    const MATRIX_GROUP_LABELS = [
        'Previous 2nd Year', 'Previous 1st Year', 'Current Year', 'Quarter-1', 'Quarter-2', 'Quarter-3',
        'Total of Year-1', 'Total of Year-2', 'Total of Year-3', 'Grand Total',
    ];

    function computeVisiblePeriods(packages) {
        if (!packages.length) return new Array(10).fill(true);
        const visible = new Array(10).fill(false);
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
                    <td>${CAN_MANAGE_PACKAGES
                        ? `<button class="btn secondary" onclick="editPackage(${pkg.id})">Edit</button>`
                        : `<a class="btn secondary" href="/api/procurement-annual-plans/${planId}/pdf/preview" target="_blank">View</a>`}</td>
                </tr>
            `;
        }).join('') || `<tr><td colspan="${5 + visibleCount * 3 + 4}" class="muted">No packages yet — add one using the form below.</td></tr>`;

        if (packages.length) {
            const periodTotals = new Array(10).fill(0);
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

    // Procurement Officer-দের জন্য — একই ফর্মে ডেটা পপুলেট করে দেখায়, কিন্তু
    // সব ইনপুট disabled থাকে (কিছুই বদলানো/সেভ করা যাবে না) আর একটা Close
    // বাটন দিয়ে বন্ধ করা যায়।
    function viewPackage(id) {
        const card = document.getElementById('packageFormCard');
        card.style.display = '';
        editPackage(id);
        card.querySelectorAll('input, select, textarea, button').forEach(el => el.disabled = true);
        card.style.opacity = '0.75';
        card.style.pointerEvents = 'none';

        let closeBtn = document.getElementById('closePackageViewBtn');
        if (!closeBtn) {
            closeBtn = document.createElement('button');
            closeBtn.type = 'button';
            closeBtn.id = 'closePackageViewBtn';
            closeBtn.className = 'btn secondary';
            closeBtn.textContent = '✕ Close';
            closeBtn.style.cssText = 'margin-top:.6rem; pointer-events:auto; opacity:1;';
            closeBtn.onclick = () => { card.style.display = 'none'; };
            card.appendChild(closeBtn);
        } else {
            closeBtn.style.display = '';
        }
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

        document.getElementById('itemSelect').value = pkg.item?.name ?? pkg.budgeted_head ?? '';
        document.getElementById('itemId').value = pkg.item_id ?? '';
        document.getElementById('budgetedHead').value = pkg.budgeted_head;
        refreshAddToList();
        document.getElementById('specification').value = pkg.specification ?? '';
        document.getElementById('unit').value = pkg.unit ?? '';
        document.getElementById('remarks').value = pkg.remarks ?? '';

        const byType = (type, year) => pkg.periods.find(p => p.period_type === type && p.year_number === year);

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
            errorBox.textContent = 'Please select a Category and enter the Item.';
            errorBox.style.display = 'block';
            return;
        }

        // Resolve the item: reuse an identical existing item in this group, or (if ticked)
        // add the new one to the Items list so it shows up in the dropdown next time.
        let headName = budgetedHead;
        let itemId = document.getElementById('itemId').value || null;
        const groupId = document.getElementById('groupSelect').value || null;

        if (!itemId && groupId) {
            const existing = items.find(i =>
                String(i.chart_of_account_id ?? i.chart_of_account?.id) === String(groupId) && normName(i.name) === normName(budgetedHead));

            if (existing) {
                itemId = existing.id;
                headName = existing.name;
            } else if (CAN_MANAGE_PACKAGES && document.getElementById('addToList').checked) {
                try {
                    const { data: created } = await api.post('/items', {
                        chart_of_account_id: groupId,
                        name: budgetedHead,
                        specification: document.getElementById('specification').value.trim() || null,
                    });
                    items.push(created);
                    itemId = created.id;
                    headName = created.name;
                } catch (err) {
                    errorBox.textContent = err.message;
                    errorBox.style.display = 'block';
                    return;
                }
            }
        }

        const payload = {
            procurement_category_id: categoryId,
            chart_of_account_id: groupId,
            budgeted_head: headName,
            item_id: itemId,
            specification: document.getElementById('specification').value || null,
            unit: document.getElementById('unit').value || null,
            remarks: document.getElementById('remarks').value || null,
            periods: {
                previous_2nd_year: buildYearSlotPayload('previous_2nd_year'),
                previous_1st_year: buildYearSlotPayload('previous_1st_year'),
                current_year: buildYearSlotPayload('current_year'),
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


    if (CAN_ADD_GROUP) document.getElementById('newGroupLink').style.display = 'inline';


    // ---------------- Import Excel ----------------
    let importFile = null;
    let importGroupMap = {};
    let importData = null;

    function openImport() {
        importFile = null; importGroupMap = {}; importData = null;
        document.getElementById('importFileInput').value = '';
        document.getElementById('importProjectWrap').style.display = 'none';
        document.getElementById('importBody').innerHTML = '';
        document.getElementById('importNote').textContent = '';
        document.getElementById('importConfirmBtn').disabled = true;
        document.getElementById('importConfirmBtn').textContent = 'Import';
        setImportMsg('');
        document.getElementById('importModal').style.display = 'flex';
    }

    function closeImport() {
        document.getElementById('importModal').style.display = 'none';
    }

    function setImportMsg(text, isError) {
        const el = document.getElementById('importMsg');
        el.textContent = text || '';
        el.style.color = isError ? '#b91c1c' : '#475569';
    }

    async function importRequest(path, extra = {}) {
        const fd = new FormData();
        fd.append('file', importFile);
        Object.entries(extra).forEach(([k, v]) => fd.append(k, v));

        const res = await fetch(`/api/procurement-annual-plans/${planId}${path}`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: fd,
        });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            throw new Error(json.message || (json.errors ? Object.values(json.errors).flat().join(', ') : `Request failed (${res.status})`));
        }
        return json;
    }

    async function onImportFileChosen(input) {
        importFile = input.files[0] || null;
        importGroupMap = {};
        document.getElementById('importBody').innerHTML = '';
        document.getElementById('importConfirmBtn').disabled = true;
        if (!importFile) return;

        setImportMsg('Reading the file...');
        try {
            const data = await importRequest('/import/preview');
            importData = data;
            const sel = document.getElementById('importProject');
            sel.innerHTML = '<option value="">— choose a project —</option>' + data.projects.map(p =>
                `<option value="${escHtml(p.key)}">${escHtml(p.name)} — ${p.rows} rows</option>`).join('');
            if (data.suggested) sel.value = data.suggested;
            document.getElementById('importProjectWrap').style.display = 'block';

            const notes = [];
            if (data.skipped_no_project) notes.push(`${data.skipped_no_project} rows with no project name are skipped.`);
            if (data.existing_packages) notes.push(`This plan already has ${data.existing_packages} packages; imported ones are added after them.`);
            document.getElementById('importNote').textContent = notes.join(' ');

            if (sel.value) await runImportPreview();
            else setImportMsg('Choose the project to import.');
        } catch (e) {
            setImportMsg(e.message, true);
        }
    }

    async function runImportPreview() {
        const project = document.getElementById('importProject').value;
        document.getElementById('importConfirmBtn').disabled = true;
        if (!project || !importFile) { document.getElementById('importBody').innerHTML = ''; return; }

        setImportMsg('Preparing the preview...');
        try {
            const data = await importRequest('/import/preview', { project, group_map: JSON.stringify(importGroupMap) });
            importData = { ...importData, ...data };
            renderImportPreview(data);
            setImportMsg('');
        } catch (e) {
            setImportMsg(e.message, true);
        }
    }

    function onImportGroupChosen(key, value) {
        if (value === '__keep') delete importGroupMap[key];
        else importGroupMap[key] = value === 'none' ? '' : value;
        runImportPreview();
    }

    function renderImportPreview(data) {
        const money = n => Number(n || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
        const sm = data.summary;
        let html = `<div style="margin-bottom:.5rem;"><b>${sm.rows}</b> packages ready · total <b>${money(sm.grand_total)}</b>`
            + ` · <span style="color:#b45309;">${sm.with_warnings} with notes</span>`
            + (sm.skipped_no_item ? ` · ${sm.skipped_no_item} skipped (no item name)` : '') + `</div>`;

        if (data.unmatched_groups.length) {
            const opts = cat => data.groups
                .filter(g => String(g.category_id) === String(cat))
                .map(g => `<option value="${g.id}">${escHtml(g.code)} — ${escHtml(g.name)}</option>`).join('');
            html += `<div style="border:1px solid #fcd34d; background:#fffbeb; border-radius:8px; padding:.6rem .8rem; margin:.5rem 0;">
                <b>Choose a Procurement Group</b> for these Sub-Category texts (or import them without a group):
                <table style="width:100%; margin-top:.4rem;"><tr><th style="text-align:left;">Text in the file</th><th>Rows</th><th>Category</th><th style="text-align:left;">Procurement Group</th></tr>
                ${data.unmatched_groups.map(u => `<tr>
                    <td>${u.text ? escHtml(u.text) : '<i>(blank)</i>'}</td><td style="text-align:center;">${u.count}</td><td>${escHtml(u.category)}</td>
                    <td><select onchange="onImportGroupChosen('${u.key}', this.value)">
                        <option value="__keep">— choose —</option><option value="none">No group</option>${opts(u.category_id)}</select></td></tr>`).join('')}
                </table></div>`;
        }

        const months = ['Jul','Aug','Sep','Oct','Nov','Dec','Jan','Feb','Mar','Apr','May','Jun'];
        html += `<div style="overflow-x:auto; max-height:420px; overflow-y:auto;"><table style="font-size:.82rem; width:100%;">
            <tr><th>#</th><th style="text-align:left;">Item</th><th>Category</th><th style="text-align:left;">Group</th><th>Unit</th><th>Rate</th>
            ${months.map(m => `<th>${m}</th>`).join('')}<th>Total</th><th style="text-align:left;">Notes</th></tr>
            ${data.rows.map(r => `<tr style="${r.warnings.length ? 'background:#fffbeb;' : ''}">
                <td>${r.sl_no}</td><td>${escHtml(r.item)}</td><td>${escHtml(r.category)}</td>
                <td>${r.group ? escHtml(r.group) : '<span style="color:#b91c1c;">—</span>'}</td><td>${escHtml(r.unit || '')}</td><td style="text-align:right;">${money(r.rate)}</td>
                ${r.units.map(u => `<td style="text-align:right;">${u ? money(u * r.rate) : ''}</td>`).join('')}
                <td style="text-align:right;"><b>${money(r.import_total)}</b></td>
                <td style="color:#b45309;">${r.warnings.map(escHtml).join('; ')}</td></tr>`).join('')}
            </table></div>`;

        document.getElementById('importBody').innerHTML = html;
        const btn = document.getElementById('importConfirmBtn');
        btn.disabled = sm.rows === 0;
        btn.textContent = `Import ${sm.rows} packages`;
    }

    async function importCommit() {
        const project = document.getElementById('importProject').value;
        const count = importData?.summary?.rows ?? 0;
        if (!project || !count) return;
        if (!confirm(`Import ${count} packages into this plan?`)) return;

        const btn = document.getElementById('importConfirmBtn');
        btn.disabled = true;
        setImportMsg('Importing...');
        try {
            const res = await importRequest('/import/commit', { project, group_map: JSON.stringify(importGroupMap) });
            closeImport();
            alert(`Imported ${res.imported} packages.`);
            load();
        } catch (e) {
            btn.disabled = false;
            setImportMsg(e.message, true);
        }
    }

    (function gatePackageForm() {
        if (CAN_MANAGE_PACKAGES) return;
        document.getElementById('packageFormCard').style.display = 'none';
        document.getElementById('packageFormReadOnlyNote').style.display = 'block';
    })();
</script>
@endsection