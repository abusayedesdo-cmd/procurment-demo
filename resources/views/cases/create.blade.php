@extends('layouts.app')
@section('title', 'Open Procurement Case')
@section('content')

<div><a href="{{ url()->previous() ?: route('cases.index') }}" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }" style="font-size:12.5px;font-weight:600;text-decoration:none">← Back to Step</a></div>

<div class="card card-pad" style="max-width:640px;margin:16px auto 0">
  <h2 style="font-size:16px;margin:0 0 4px">Open a New Procurement Case</h2>
  <p style="font-size:12.5px;color:var(--muted);margin:0 0 20px">
    Starts ESDO procurement process for an approved Purchase Requisition.
  </p>

  @if ($errors->any())
    <div style="background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-bottom:16px">
      @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
  @endif

  @if ($eligiblePrs->isEmpty())
    <div style="font-size:13px;color:var(--muted)">
      No approved Purchase Requisitions are available to open a case for — either none are approved yet, or every approved PR already has a case.
    </div>
  @else
    <form method="POST" action="{{ route('cases.store') }}" style="display:flex;flex-direction:column;gap:14px">
      @csrf

      <div>
        <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Purchase Requisition</label>
        <select name="purchase_requisition_id" id="prSelect" required style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
          <option value="">-- Select an approved PR --</option>
          @foreach ($eligiblePrs as $pr)
            <option value="{{ $pr->id }}"
                    data-project="{{ $pr->project_name }}"
                    data-amount="{{ $pr->total_estimated_amount }}"
                    data-category="{{ $pr->category?->name }}">
              {{ $pr->pr_number }} — {{ $pr->project_name }} (৳ {{ number_format($pr->total_estimated_amount, 2) }})
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Case Title</label>
        <input type="text" name="title" id="titleInput" required maxlength="255" value="{{ old('title') }}"
               style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
      </div>

      <div style="display:flex;gap:14px">
        <div style="flex:1">
          <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Category</label>
          <select name="category" id="categorySelect" required style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
            <option value="">-- Select --</option>
            <option value="Goods" @selected(old('category')==='Goods')>Goods</option>
            <option value="Works" @selected(old('category')==='Works')>Works</option>
            <option value="Services" @selected(old('category')==='Services')>Services</option>
          </select>
        </div>
        <div style="flex:1">
          <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Method</label>
          <select name="method" required style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
            <option value="">-- Select --</option>
            <option value="RFQ" @selected(old('method')==='RFQ')>RFQ (Goods)</option>
            <option value="RFP" @selected(old('method')==='RFP')>RFP (Services)</option>
            <option value="RFT" @selected(old('method')==='RFT')>RFT (Works)</option>
          </select>
        </div>
      </div>

      <label style="display:flex;align-items:center;gap:8px;font-size:13px">
        <input type="checkbox" name="is_otm" value="1" @checked(old('is_otm'))>
        Open Tender Method (OTM) — otherwise treated as Quotation
      </label>

      <div>
        <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Estimated Amount (৳)</label>
        <input type="number" step="0.01" min="0" name="amount" id="amountInput" required value="{{ old('amount') }}"
               style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
      </div>

      <button type="submit" class="btn btn-primary" style="align-self:flex-start;margin-top:6px">Open Case</button>
    </form>

    <script>
      // A PR's category is stored as a free-form name (e.g. "Goods", "Works",
      // "Service"). The case's category is a fixed enum (Goods / Works /
      // Services), so "Service" (singular, as seeded) needs mapping to
      // "Services" (plural). Anything already matching an option passes
      // through unchanged.
      const categoryMap = { 'Goods': 'Goods', 'Works': 'Works', 'Service': 'Services', 'Services': 'Services' };

      document.getElementById('prSelect').addEventListener('change', function () {
        const opt = this.selectedOptions[0];
        if (!opt || !opt.value) return;

        const titleEl = document.getElementById('titleInput');
        const amountEl = document.getElementById('amountInput');
        const categoryEl = document.getElementById('categorySelect');

        // Title and amount always mirror the selected PR.
        titleEl.value = opt.dataset.project || '';
        amountEl.value = opt.dataset.amount || '';

        const mapped = categoryMap[opt.dataset.category] || '';
        if (mapped && [...categoryEl.options].some(o => o.value === mapped)) {
          categoryEl.value = mapped;
        }
      });
    </script>
  @endif
</div>

@endsection