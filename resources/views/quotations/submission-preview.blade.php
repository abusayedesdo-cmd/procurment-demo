<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation Submission — {{ $quotation->vendor->name }}</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; color:#0F172A; max-width:900px; margin:2rem auto; padding:0 1rem; }
        h1 { font-size:1.3rem; margin-bottom:.25rem; }
        .meta { color:#64748B; font-size:.9rem; margin-bottom:1.5rem; }
        .card { background:#fff; border:1px solid #E2E8F0; border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; }
        .card h2 { font-size:.95rem; color:#0F766E; margin:0 0 .9rem; }
        .row { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap:1rem; margin-bottom:.75rem; }
        .row label { display:block; font-size:.78rem; font-weight:600; color:#64748B; margin-bottom:.2rem; }
        .row .val { font-size:.9rem; }
        table { width:100%; border-collapse:collapse; font-size:.85rem; }
        th, td { text-align:left; padding:.5rem .4rem; border-bottom:1px solid #E2E8F0; }
        .badge { display:inline-block; padding:.15rem .6rem; border-radius:999px; font-size:.78rem; font-weight:600; }
        .badge.yes { background:#DCFCE7; color:#166534; }
        .badge.no { background:#FEE2E2; color:#991B1B; }
        .doc-link { display:inline-block; margin:.2rem .6rem .2rem 0; padding:.35rem .7rem; background:#F1F5F9; border-radius:6px; text-decoration:none; color:#0F172A; font-size:.85rem; }
    </style>
</head>
<body>
    <h1>Quotation Submission</h1>
    <div class="meta">RFQ #{{ $quotation->rfq->rfq_number ?? '-' }} &middot; Vendor: {{ $quotation->vendor->name }} &middot; Source: {{ $quotation->submitted_via_portal ? 'Vendor Portal' : 'Staff Entry' }}</div>

    <div class="card">
        <h2>Vendor Information</h2>
        <div class="row">
            <div><label>Company Name</label><div class="val">{{ $quotation->vendor->name }}</div></div>
            <div><label>Email</label><div class="val">{{ $quotation->vendor->email }}</div></div>
            <div><label>Address</label><div class="val">{{ $quotation->vendor->address ?? '-' }}</div></div>
        </div>
        <div class="row">
            <div><label>Representative</label><div class="val">{{ $quotation->representative_name }}</div></div>
            <div><label>Contact</label><div class="val">{{ $quotation->representative_contact }}</div></div>
            <div><label>Trade License No.</label><div class="val">{{ $quotation->vendor->trade_license_no ?? '-' }}</div></div>
        </div>
    </div>

    <div class="card">
        <h2>Acknowledgement</h2>
        <span class="badge {{ $quotation->delivery_terms_accepted ? 'yes' : 'no' }}">Delivery Terms: {{ $quotation->delivery_terms_accepted ? 'Accepted' : 'Not Accepted' }}</span>
        <span class="badge {{ $quotation->terms_accepted ? 'yes' : 'no' }}">Terms &amp; Conditions: {{ $quotation->terms_accepted ? 'Accepted' : 'Not Accepted' }}</span>
    </div>

    <div class="card">
        <h2>Experience Information</h2>
        <div class="row" style="grid-template-columns:1fr;">
            <div><label>General Experience</label><div class="val">{{ $quotation->general_experience ?: '-' }}</div></div>
        </div>
        <div class="row" style="grid-template-columns:1fr;">
            <div><label>Relevant Experience</label><div class="val">{{ $quotation->relevant_experience ?: '-' }}</div></div>
        </div>
    </div>

    <div class="card">
        <h2>Rate Schedule</h2>
        <table>
            <thead><tr><th>Description</th><th>Unit Price</th><th>Amount</th></tr></thead>
            <tbody>
                @foreach ($quotation->items as $item)
                    <tr>
                        <td>{{ $item->rfqItem->description ?? '-' }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
                <tr><td colspan="2"><b>Total Quoted Amount</b></td><td><b>{{ number_format($quotation->quoted_amount, 2) }}</b></td></tr>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Uploaded Documents</h2>
        @forelse ($quotation->vendor->documents as $doc)
            <a class="doc-link" href="{{ Storage::url($doc->file_path) }}" target="_blank">
                {{ ucwords(str_replace('_', ' ', $doc->document_type)) }}
            </a>
        @empty
            <p style="color:#64748B; font-size:.85rem;">কোনো ডকুমেন্ট আপলোড করা নেই।</p>
        @endforelse
    </div>
</body>
</html>