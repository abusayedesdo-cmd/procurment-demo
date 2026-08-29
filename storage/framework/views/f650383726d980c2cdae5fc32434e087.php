<?php $__env->startSection('title', 'Open Procurement Case'); ?>
<?php $__env->startSection('content'); ?>

<div><a href="<?php echo e(url()->previous() ?: route('cases.index')); ?>" onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }" style="font-size:12.5px;font-weight:600;text-decoration:none">← Back to Step</a></div>

<div class="card card-pad" style="max-width:640px;margin:16px auto 0">
  <h2 style="font-size:16px;margin:0 0 4px">Open a New Procurement Case</h2>
  <p style="font-size:12.5px;color:var(--muted);margin:0 0 20px">
    Starts ESDO procurement process for an approved Purchase Requisition.
  </p>

  <?php if($errors->any()): ?>
    <div style="background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;padding:10px 14px;border-radius:8px;font-size:12.5px;margin-bottom:16px">
      <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <div><?php echo e($e); ?></div> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>

  <?php if($eligiblePrs->isEmpty()): ?>
    <div style="font-size:13px;color:var(--muted)">
      No approved Purchase Requisitions are available to open a case for — either none are approved yet, or every approved PR already has a case.
    </div>
  <?php else: ?>
    <form method="POST" action="<?php echo e(route('cases.store')); ?>" style="display:flex;flex-direction:column;gap:14px">
      <?php echo csrf_field(); ?>

      <div>
        <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Purchase Requisition</label>
        <select name="purchase_requisition_id" id="prSelect" required style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
          <option value="">-- Select an approved PR --</option>
          <?php $__currentLoopData = $eligiblePrs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($pr->id); ?>"
                    data-project="<?php echo e($pr->project_name); ?>"
                    data-amount="<?php echo e($pr->total_estimated_amount); ?>"
                    data-category="<?php echo e($pr->category?->name); ?>">
              <?php echo e($pr->pr_number); ?> — <?php echo e($pr->project_name); ?> (৳ <?php echo e(number_format($pr->total_estimated_amount, 2)); ?>)
            </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div>
        <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Case Title</label>
        <input type="text" name="title" id="titleInput" required maxlength="255" value="<?php echo e(old('title')); ?>"
               style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
      </div>

      <div style="display:flex;gap:14px">
        <div style="flex:1">
          <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Category</label>
          <select name="category" id="categorySelect" required style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
            <option value="">-- Select --</option>
            <option value="Goods" <?php if(old('category')==='Goods'): echo 'selected'; endif; ?>>Goods</option>
            <option value="Works" <?php if(old('category')==='Works'): echo 'selected'; endif; ?>>Works</option>
            <option value="Services" <?php if(old('category')==='Services'): echo 'selected'; endif; ?>>Services</option>
          </select>
        </div>
        <div style="flex:1">
          <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Method</label>
          <select name="method" required style="width:100%;padding:8px 10px;border:1px solid var(--line-soft);border-radius:6px;font-size:13px">
            <option value="">-- Select --</option>
            <option value="RFQ" <?php if(old('method')==='RFQ'): echo 'selected'; endif; ?>>RFQ (Goods)</option>
            <option value="RFP" <?php if(old('method')==='RFP'): echo 'selected'; endif; ?>>RFP (Services)</option>
            <option value="RFT" <?php if(old('method')==='RFT'): echo 'selected'; endif; ?>>RFT (Works)</option>
          </select>
        </div>
      </div>

      <label style="display:flex;align-items:center;gap:8px;font-size:13px">
        <input type="checkbox" name="is_otm" value="1" <?php if(old('is_otm')): echo 'checked'; endif; ?>>
        Open Tender Method (OTM) — otherwise treated as Quotation
      </label>

      <div>
        <label style="font-size:12.5px;font-weight:600;display:block;margin-bottom:4px">Estimated Amount (৳)</label>
        <input type="number" step="0.01" min="0" name="amount" id="amountInput" required value="<?php echo e(old('amount')); ?>"
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
  <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/cases/create.blade.php ENDPATH**/ ?>