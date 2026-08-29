@extends('layouts.app')
@section('title', 'New Purchase Requisition')
@section('content')

<form method="POST" action="{{ route('prs.store') }}" class="card card-pad" style="display:flex;flex-direction:column;gap:16px">
  @csrf
  <div style="display:flex;align-items:baseline;gap:12px;flex-wrap:wrap">
    <b style="font-size:16px">Purchase Requisition</b>
    <span style="font-size:12.5px;color:var(--muted)">PR Sl. No: <b style="color:var(--ink)">{{ $nextPrNo }}</b> · Date: {{ now()->format('d M Y') }}</span>
  </div>

  @if ($errors->any())
    <div style="background:#FBF1EF;border:1px solid #E5C0BB;color:var(--bad);border-radius:8px;padding:10px 14px;font-size:13px">{{ $errors->first() }}</div>
  @endif

  <div class="form-grid">
    <div class="field"><label>Name of Program / Project / Unit</label><input name="project" value="{{ old('project') }}" placeholder="e.g. WASH Project – Tangail" required></div>
    <div class="field"><label>Name of Requestor</label><input name="requestor" value="{{ old('requestor', auth()->user()->name) }}" placeholder="Full name" required></div>
    <div class="field"><label>Designation</label><input name="designation" value="{{ old('designation') }}" placeholder="e.g. Project Coordinator"></div>
    <div class="field"><label>Procurement Category</label>
      <select name="category">
        <option value="Goods">A. Goods — RFQ</option>
        <option value="Works">B. Works — RFT / Tender + BOQ</option>
        <option value="Services">C. Services — RFP + TOR</option>
      </select>
    </div>
    <div class="field"><label>Estimated Date of Delivery</label><input type="date" name="delivery_date" value="{{ old('delivery_date') }}"></div>
    <div class="field"><label>Total Allocated Budget (BDT)</label><input type="number" step="0.01" name="allocated_budget" value="{{ old('allocated_budget', 1500000) }}"></div>
  </div>

  <div style="border:1px solid var(--line);border-radius:10px;overflow-x:auto">
    <table class="data" id="items" style="min-width:720px">
      <thead><tr><th>Sl.</th><th>Particulars</th><th>Unit</th><th>Qty</th><th>Unit Price</th><th class="num">Est. Amount</th><th>A/C Code</th><th></th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
  <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    <button type="button" class="btn btn-outline" onclick="addRow()">+ Add line</button>
    <div style="flex:1"></div>
    <span style="font-size:13px;color:var(--muted)">Total estimated amount</span>
    <b id="grand" style="font-size:20px;font-variant-numeric:tabular-nums">Tk 0.00</b>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="{{ route('prs.index') }}" class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary">Submit for review</button>
  </div>
</form>

<script>
let n = 0;
function addRow() {
  const i = n++;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td class="sl">${i + 1}</td>
    <td><input name="items[${i}][name]" placeholder="Item / work / service description" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input name="items[${i}][unit]" placeholder="Pcs" style="width:70px;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input type="number" name="items[${i}][qty]" value="1" min="0" step="0.01" oninput="calc()" style="width:70px;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input type="number" name="items[${i}][rate]" value="0" min="0" step="0.01" oninput="calc()" style="width:100px;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td class="num rowtotal" style="font-weight:600">0.00</td>
    <td><input name="items[${i}][ac_code]" placeholder="Code" style="width:80px;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><button type="button" onclick="this.closest('tr').remove(); calc(); renumber()" style="border:none;background:none;color:var(--bad);font-size:15px;cursor:pointer">×</button></td>`;
  document.querySelector('#items tbody').appendChild(tr);
}
function renumber() {
  document.querySelectorAll('#items .sl').forEach((el, i) => el.textContent = i + 1);
}
function calc() {
  let sum = 0;
  document.querySelectorAll('#items tbody tr').forEach(tr => {
    const qty = parseFloat(tr.querySelector('[name$="[qty]"]').value) || 0;
    const rate = parseFloat(tr.querySelector('[name$="[rate]"]').value) || 0;
    const t = qty * rate;
    tr.querySelector('.rowtotal').textContent = t.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    sum += t;
  });
  document.getElementById('grand').textContent = '৳ ' + sum.toLocaleString('en-IN', { minimumFractionDigits: 2 });
}
addRow();
</script>
@endsection
