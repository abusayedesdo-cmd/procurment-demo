<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Submitted</title>
    <style>
        body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#F8FAFC; color:#0F172A; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:1.5rem; }
        .box { background:#fff; border:1px solid #E2E8F0; border-radius:12px; padding:2.5rem; max-width:480px; text-align:center; }
        .box h1 { font-size:1.3rem; margin-bottom:.75rem; color:#0F766E; }
        .box p { color:#64748B; font-size:.92rem; line-height:1.6; }
    </style>
</head>
<body>
    <div class="box">
        <h1>✓ Quotation Submitted</h1>
        <p>RFQ #{{ $rfq->rfq_number }} ({{ $rfq->subject }})-এর জন্য আপনার quotation সফলভাবে জমা হয়েছে। ধন্যবাদ।</p>
        <p>প্রয়োজনে ESDO প্রকিউরমেন্ট টিম আপনার সাথে যোগাযোগ করবে।</p>
    </div>
</body>
</html>