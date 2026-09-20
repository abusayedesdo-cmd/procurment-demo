<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation Submission — <?php echo e($rfq->rfq_number); ?></title>
    <style>
        :root { --ink:#0F172A; --muted:#64748B; --line:#E2E8F0; --accent:#0D9488; --accent-dark:#0F766E; --bg:#F8FAFC; }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, Segoe UI, Roboto, Arial, sans-serif; background: var(--bg); color: var(--ink); margin: 0; padding: 2rem 1rem; }
        .shell { max-width: 820px; margin: 0 auto; }
        .header { margin-bottom: 1.5rem; }
        .header h1 { font-size: 1.4rem; margin: 0 0 .25rem; }
        .header .meta { color: var(--muted); font-size: .9rem; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 1.5rem; margin-bottom: 1.25rem; }
        .card h2 { font-size: 1rem; margin: 0 0 1rem; color: var(--accent-dark); }
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        label { display: block; font-size: .85rem; font-weight: 600; margin-bottom: .35rem; }
        input[type="text"], input[type="email"], input[type="number"], input[type="file"], textarea {
            width: 100%; box-sizing: border-box; padding: .55rem .7rem; font-size: .9rem;
            border: 1px solid var(--line); border-radius: 7px; font-family: inherit;
        }
        textarea { resize: vertical; }
        table { width: 100%; border-collapse: collapse; font-size: .85rem; }
        th, td { text-align: left; padding: .5rem .4rem; border-bottom: 1px solid var(--line); }
        th { color: var(--muted); font-weight: 600; font-size: .78rem; text-transform: uppercase; }
        .checkbox-row { display: flex; align-items: flex-start; gap: .5rem; font-size: .85rem; margin-bottom: .75rem; }
        .checkbox-row input { margin-top: .2rem; }
        .radio-row { display: block; font-size: .9rem; font-weight: 400; margin-bottom: .5rem; cursor: pointer; }
        .radio-row input { margin-right: .5rem; }
        .btn { background: var(--accent); color: #fff; border: none; border-radius: 7px; padding: .7rem 1.4rem; font-size: .95rem; font-weight: 600; cursor: pointer; }
        .btn:hover { background: var(--accent-dark); }
        .error-box { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; border-radius: 8px; padding: .8rem 1rem; margin-bottom: 1.25rem; font-size: .88rem; }
        .error-box ul { margin: 0; padding-left: 1.2rem; }
        .closed-notice { background: #FFF7ED; color: #9A3412; border: 1px solid #FED7AA; border-radius: 8px; padding: 1rem 1.2rem; font-size: .95rem; }
        .total-row td { font-weight: 700; }
        .hint { color: var(--muted); font-size: .78rem; margin-top: .3rem; }
    </style>
</head>
<body>
<div class="shell">
    <div class="header">
        <h1>Quotation Submission</h1>
        <div class="meta">
            RFQ #<?php echo e($rfq->rfq_number); ?> — <?php echo e($rfq->subject); ?> (<?php echo e($rfq->type); ?>)<br>
            Closing Date: <?php echo e(optional($rfq->closing_date)->format('Y-m-d')); ?>

        </div>
    </div>

    <?php if($pr): ?>
        <div class="card">
            <h2>Purchase Requisition Reference</h2>
            <div class="row">
                <div>
                    <label>PR Number</label>
                    <p style="margin:0; font-weight:600;"><?php echo e($pr->pr_number); ?></p>
                </div>
                <div>
                    <label>Category</label>
                    <p style="margin:0; font-weight:600;"><?php echo e($pr->category->name ?? '-'); ?></p>
                </div>
                <div>
                    <label>Window / Nature</label>
                    <p style="margin:0; font-weight:600;"><?php echo e($pr->window_type); ?></p>
                </div>
                <div>
                    <label>Project</label>
                    <p style="margin:0; font-weight:600;"><?php echo e($pr->project_name ?? '-'); ?></p>
                </div>
                <div>
                    <label>Requisition Date</label>
                    <p style="margin:0; font-weight:600;"><?php echo e(optional($pr->requisition_date)->format('Y-m-d')); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if($closed): ?>
        <div class="closed-notice">This RFQ's closing date has passed — new quotations can no longer be submitted.</div>
    <?php else: ?>
        <?php if($errors->any()): ?>
            <div class="error-box">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('vendor-portal.store', $rfq->public_token)); ?>" enctype="multipart/form-data" id="quoteForm">
            <?php echo csrf_field(); ?>

            <div class="card">
                <h2>Vendor / Company Information</h2>
                <div class="row">
                    <div>
                        <label>Vendor / Company Name *</label>
                        <input type="text" name="vendor_name" value="<?php echo e(old('vendor_name')); ?>" required>
                    </div>
                    <div>
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo e(old('email')); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Address</label>
                        <input type="text" name="vendor_address" value="<?php echo e(old('vendor_address')); ?>">
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Representative Name *</label>
                        <input type="text" name="representative_name" value="<?php echo e(old('representative_name')); ?>" required>
                    </div>
                    <div>
                        <label>Cell Number *</label>
                        <input type="text" name="representative_contact" value="<?php echo e(old('representative_contact')); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Trade License No.</label>
                        <input type="text" name="trade_license_no" value="<?php echo e(old('trade_license_no')); ?>">
                    </div>
                    <div>
                        <label>VAT Reg. No. (BIN)</label>
                        <input type="text" name="vat_reg_no" value="<?php echo e(old('vat_reg_no')); ?>">
                    </div>
                    <div>
                        <label>Tax ID (TIN)</label>
                        <input type="text" name="tax_id" value="<?php echo e(old('tax_id')); ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>ESDO Vendor Enlistment</h2>
                <label class="radio-row">
                    <input type="radio" name="enlistment_status" value="enlisted" required onchange="toggleEnlistmentFields()">
                    I am already an enlisted vendor with ESDO
                </label>
                <label class="radio-row">
                    <input type="radio" name="enlistment_status" value="applied" required onchange="toggleEnlistmentFields()">
                    I am applying for new enlistment
                </label>
                <div id="enlistmentFields" style="display:none; margin-top:1rem;">
                    <div class="row">
                        <div>
                            <label>Owner Name</label>
                            <input type="text" name="owner_name" value="<?php echo e(old('owner_name')); ?>">
                        </div>
                        <div>
                            <label>Group / Subcategory</label>
                            <input type="text" name="group_subcategory" value="<?php echo e(old('group_subcategory')); ?>" placeholder="e.g. Group-A1">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>Bank Account Name</label>
                            <input type="text" name="bank_account_name" value="<?php echo e(old('bank_account_name')); ?>">
                        </div>
                        <div>
                            <label>Bank Name</label>
                            <input type="text" name="bank_name" value="<?php echo e(old('bank_name')); ?>">
                        </div>
                        <div>
                            <label>Bank Account Number</label>
                            <input type="text" name="bank_account_number" value="<?php echo e(old('bank_account_number')); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div>
                            <label>Bank Address</label>
                            <input type="text" name="bank_address" value="<?php echo e(old('bank_address')); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Legal Documents (Upload)</h2>
                <div class="row">
                    <div>
                        <label>Trade License *</label>
                        <input type="file" name="trade_license_file" required>
                    </div>
                    <div>
                        <label>TIN Certificate *</label>
                        <input type="file" name="tin_file" required>
                    </div>
                    <div>
                        <label>BIN / VAT Certificate *</label>
                        <input type="file" name="bin_file" required>
                    </div>
                </div>
                <div class="row">
                    <div>
                        <label>Experience Certificate (optional)</label>
                        <input type="file" name="experience_file">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Other Supporting Documents (optional)</h2>
                <div class="row">
                    <div>
                        <label>Tax Clearance Certificate (PSR)</label>
                        <input type="file" name="psr_file">
                    </div>
                    <div>
                        <label>Bank Solvency Certificate</label>
                        <input type="file" name="bank_solvency_file">
                    </div>
                    <div>
                        <label>Certificate of Incorporation</label>
                        <input type="file" name="incorporation_file">
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Experience Information</h2>
                <div class="row" style="grid-template-columns: 1fr;">
                    <div>
                        <label>General Experience</label>
                        <textarea name="general_experience" rows="3"><?php echo e(old('general_experience')); ?></textarea>
                    </div>
                </div>
                <div class="row" style="grid-template-columns: 1fr;">
                    <div>
                        <label>Relevant Experience</label>
                        <textarea name="relevant_experience" rows="3"><?php echo e(old('relevant_experience')); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>Rate Schedule</h2>
                <?php if($rfq->items->isEmpty()): ?>
                    <p style="color:#B91C1C; font-size:.88rem;">No items have been added to this RFQ yet. Please contact the ESDO procurement team — once items are added, you can return to this same link to submit your quotation.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Description</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Unit Price *</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $rfq->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->serial_no ?? $index + 1); ?></td>
                                <td><?php echo e($item->description); ?></td>
                                <td><?php echo e($item->unit->name ?? '-'); ?></td>
                                <td><?php echo e(rtrim(rtrim(number_format($item->quantity, 2), '0'), '.')); ?></td>
                                <td>
                                    <input type="hidden" name="items[<?php echo e($index); ?>][rfq_item_id]" value="<?php echo e($item->id); ?>">
                                    <input type="number" step="0.01" min="0" class="unit-price"
                                           data-qty="<?php echo e($item->quantity); ?>" data-row="<?php echo e($index); ?>"
                                           name="items[<?php echo e($index); ?>][unit_price]" required>
                                </td>
                                <td class="amount-cell" id="amount_<?php echo e($index); ?>">0.00</td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr class="total-row">
                            <td colspan="5">Total</td>
                            <td id="grandTotal">0.00</td>
                        </tr>
                    </tbody>
                </table>
                <?php endif; ?>
                <p class="hint">Enter the Unit Price on each line — the Amount and Total are calculated automatically.</p>
            </div>

            <div class="card">
                <h2>Terms &amp; Conditions</h2>
                <?php if($rfq->terms_conditions): ?>
                    <p style="white-space:pre-line; font-size:.88rem;"><?php echo e($rfq->terms_conditions); ?></p>
                    <hr style="border:none; border-top:1px solid var(--line); margin:1rem 0;">
                <?php endif; ?>
                <p style="white-space:pre-line; font-size:.85rem; color:#475569;">1. Quoted prices must be inclusive of applicable VAT and Tax.
2. Quantities beyond what is ordered may not be supplied without ESDO's written approval.
3. Goods/services must be delivered on time and to the specified quality standard.
4. The vendor bears liability for any loss or damage until the goods are received by ESDO.
5. If the vendor fails to meet the terms of the contract, ESDO may require correction, replacement, or a refund of the price.
6. Any dispute arising from this tender will first be addressed through discussion between the parties.</p>
            </div>

            <div class="card">
                <h2>Acknowledgement</h2>
                <label class="checkbox-row">
                    <input type="checkbox" name="delivery_terms_accepted" value="1" required>
                    <span>I acknowledge the Delivery Location and Delivery Schedule Time for this RFQ and agree to comply with them.</span>
                </label>
                <label class="checkbox-row">
                    <input type="checkbox" name="terms_accepted" value="1" required>
                    <span>I have read and agree to the Terms &amp; Conditions of this RFQ.</span>
                </label>
            </div>

           <button type="submit" class="btn" <?php if($rfq->items->isEmpty()): ?> disabled style="opacity:.5; cursor:not-allowed;" <?php endif; ?>>Submit Quotation</button>
        </form>
    <?php endif; ?>
</div>

<script>
    document.querySelectorAll('.unit-price').forEach(function (input) {
        input.addEventListener('input', function () {
            const qty = parseFloat(input.dataset.qty) || 0;
            const price = parseFloat(input.value) || 0;
            const amount = qty * price;
            document.getElementById('amount_' + input.dataset.row).textContent = amount.toFixed(2);

            let total = 0;
            document.querySelectorAll('.unit-price').forEach(function (i) {
                const q = parseFloat(i.dataset.qty) || 0;
                const p = parseFloat(i.value) || 0;
                total += q * p;
            });
            document.getElementById('grandTotal').textContent = total.toFixed(2);
        });
    });

    function toggleEnlistmentFields() {
        const applied = document.querySelector('input[name="enlistment_status"]:checked')?.value === 'applied';
        document.getElementById('enlistmentFields').style.display = applied ? 'block' : 'none';
    }
</script>
</body>
</html><?php /**PATH D:\New Poject\Project_procrument\resources\views/vendor-portal/show.blade.php ENDPATH**/ ?>