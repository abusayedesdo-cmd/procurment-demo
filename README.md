# PR download — matches the paper "Purchase Requisition" form

Adds a PDF download button on the PR detail page, laid out to match
`PR_for_food_(tiffin).pdf` exactly: header block, Sl.No/Item/Specification/
Unit/Qty/Unit Price/Total/A-C Code table, Delivery Locations/Date/Time,
In-word (auto-computed), Budgetary Check block, and the signature lines
(Requested by / Endorsed by / Finance Requested by / Recommend by /
Approved by).

Uses the same DomPDF pipeline already used for RFQ, Tender Schedule, and
Tender Opening documents — no new dependencies.

## What's filled in automatically vs left blank
- **Auto-filled**: PR number, date, requestor name/designation, all item
  lines, delivery location/date/time, Amount of PR, and the "In-word"
  Taka amount (spelled out, Crore/Lakh/Thousand).
- **Left blank** (dotted lines, same as the paper form): the rest of the
  Budgetary Check block (Total allocated Budget, Remaining Budget B/F,
  Remaining Budget C/F, Accountant name/signature) and all the
  Endorsed by / Finance Requested by / Recommend by / Approved by
  signature lines — these get filled in and signed by hand after
  printing, same as your current paper process.

## Deploy (cPanel / phpMyAdmin — no artisan migrate; no DB changes at all this time)

1. **Copy these files in** (overwrite):
   - `app/Http/Controllers/Api/DocumentDownloadController.php`
   - `resources/views/documents/purchase-requisition.blade.php` (new file)
   - `resources/views/purchase-requisitions/show.blade.php`

2. **Merge `routes_api.php`** into `routes/api.php` — one new line inside
   the `auth:sanctum` group, right after the attachment upload route:
   ```php
   Route::get('purchase-requisitions/{purchaseRequisition}/pdf', [DocumentDownloadController::class, 'purchaseRequisitionPdf']);
   ```

3. `php artisan optimize:clear`

No SQL to run this time — this only reads existing data.
