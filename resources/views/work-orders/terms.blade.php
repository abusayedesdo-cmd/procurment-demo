@extends('layouts.app')

@section('title', 'Work Order Terms')

@section('styles')
<style>
    .term-row { display: flex; gap: 8px; align-items: flex-start; margin-bottom: 8px; }
    .term-row textarea { flex: 1; width: 100%; }
    .term-num { min-width: 28px; padding-top: 8px; color: #6b7280; }
</style>
@endsection

@section('content')
    <h1>Work Order Terms &amp; Conditions</h1>
    <p style="color:#6b7280;">Work Order: <strong>{{ $workOrder->wo_number }}</strong> &middot; <span id="termsStatus">Loading…</span></p>

    <div id="errorBox" class="error-box" style="display:none;"></div>
    <div id="successBox" class="error-box" style="display:none; background:#ecfdf5; color:#065f46; border-color:#a7f3d0;"></div>

    <div class="card" style="max-width:900px;">
        <p style="font-size:0.85rem; color:#6b7280; margin-top:0;">
            These lines come from the RFQ's terms. Edit any line, remove it, or add a new one below.
            Saving makes this list the one printed on this Work Order (Preview / Word / PDF).
        </p>

        <div id="termsList"></div>
        <p id="emptyNote" style="display:none; color:#b91c1c;">No terms in the list. Add at least one, or use "Reset to RFQ terms".</p>

        <label for="newTerm" style="margin-top:12px;">Add a new term</label>
        <textarea id="newTerm" rows="2" placeholder="Type the new term here, then click Add"></textarea>
        <div class="row" style="margin-top:8px; gap:8px;">
            <button type="button" id="addTermBtn" class="btn secondary">Add term</button>
        </div>

        <hr style="margin:1.25rem 0; border:none; border-top:1px solid #e5e7eb;">

        <div class="row" style="gap:8px;">
            <button type="button" id="saveBtn" class="btn">Save terms</button>
            <button type="button" id="resetBtn" class="btn secondary">Reset to RFQ terms</button>
            <a href="/api/work-orders/{{ $workOrder->id }}/preview" target="_blank" rel="noopener" class="btn secondary">Preview Work Order</a>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    const woId = {{ $workOrder->id }};
    const listEl = document.getElementById('termsList');
    const emptyNote = document.getElementById('emptyNote');
    const statusEl = document.getElementById('termsStatus');
    const errorBox = document.getElementById('errorBox');
    const successBox = document.getElementById('successBox');
    const newTerm = document.getElementById('newTerm');

    function showError(msg) {
        successBox.style.display = 'none';
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
    }
    function showSuccess(msg) {
        errorBox.style.display = 'none';
        successBox.textContent = msg;
        successBox.style.display = 'block';
    }

    function renumber() {
        Array.from(listEl.children).forEach((row, i) => {
            row.querySelector('.term-num').textContent = (i + 1) + '.';
        });
        emptyNote.style.display = listEl.children.length ? 'none' : 'block';
    }

    function addRow(text) {
        const row = document.createElement('div');
        row.className = 'term-row';

        const num = document.createElement('span');
        num.className = 'term-num';

        const ta = document.createElement('textarea');
        ta.rows = 2;
        ta.value = text;

        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn secondary';
        del.textContent = 'Remove';
        del.addEventListener('click', () => { row.remove(); renumber(); });

        row.append(num, ta, del);
        listEl.appendChild(row);
        renumber();
    }

    function currentTerms() {
        return Array.from(listEl.querySelectorAll('textarea')).map(t => t.value.trim()).filter(Boolean);
    }

    function render(terms) {
        listEl.innerHTML = '';
        terms.forEach(addRow);
        renumber();
    }

    async function load() {
        try {
            const { data } = await api.get(`/work-orders/${woId}/terms`);
            render(data.terms);
            statusEl.textContent = data.is_custom
                ? 'Edited list (saved on this Work Order)'
                : 'Generated from the RFQ terms (not edited yet)';
        } catch (err) {
            showError(err.message);
        }
    }

    document.getElementById('addTermBtn').addEventListener('click', () => {
        const text = newTerm.value.trim();
        if (!text) return;
        addRow(text);
        newTerm.value = '';
    });

    document.getElementById('saveBtn').addEventListener('click', async () => {
        const terms = currentTerms();
        if (!terms.length) {
            showError('Add at least one term, or use "Reset to RFQ terms".');
            return;
        }
        try {
            const res = await api.put(`/work-orders/${woId}/terms`, { terms });
            showSuccess(res.message || 'Terms saved.');
            await load();
        } catch (err) {
            showError(err.message);
        }
    });

    document.getElementById('resetBtn').addEventListener('click', async () => {
        if (!window.confirm('Discard your edits and use the RFQ terms again?')) return;
        try {
            const res = await api.del(`/work-orders/${woId}/terms`);
            showSuccess(res.message || 'Reset to RFQ terms.');
            await load();
        } catch (err) {
            showError(err.message);
        }
    });

    load();
</script>
@endsection