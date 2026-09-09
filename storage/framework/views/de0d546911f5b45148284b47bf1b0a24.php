<?php $__env->startSection('title', $meeting->typeLabel() . ' Resolution'); ?>
<?php $__env->startSection('content'); ?>

<div><a href="<?php echo e(route('cases.show', $meeting->procurementCase)); ?>" style="font-size:12.5px;font-weight:600;text-decoration:none">← <?php echo e($meeting->procurementCase->ref); ?></a></div>

<form method="POST" action="<?php echo e(route('meetings.resolution.store', $meeting)); ?>" class="card card-pad" style="display:flex;flex-direction:column;gap:16px">
  <?php echo csrf_field(); ?>
  <div>
    <div style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)">Step 3 of 3 — Resolution</div>
    <b style="font-size:16px"><?php echo e($meeting->typeLabel()); ?> — <?php echo e($meeting->location); ?>, <?php echo e($meeting->meeting_date->format('d M Y')); ?></b>
    <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Case: <?php echo e($meeting->procurementCase->ref); ?> — <?php echo e($meeting->procurementCase->title); ?></div>
    <div style="font-size:12px;color:var(--muted);margin-top:6px">Record what the committee decided. This assigns the Rezulation No. and finalizes the minutes.</div>
  </div>

  <?php if($errors->any()): ?>
    <div style="background:#FBF1EF;border:1px solid #E5C0BB;color:var(--bad);border-radius:8px;padding:10px 14px;font-size:13px"><?php echo e($errors->first()); ?></div>
  <?php endif; ?>

  <div class="field"><label>Decisions of Today's Meeting</label>
    <textarea name="decisions" rows="4" required placeholder="Summarize what the committee decided"><?php echo e(old('decisions')); ?></textarea>
  </div>

  <?php if($meeting->meeting_type === 'first'): ?>
    <div style="border-top:1px solid var(--line-soft);padding-top:14px">
      <b style="font-size:13.5px">Tender Schedule Decided</b>
      <div class="form-grid" style="margin-top:10px">
        <div class="field"><label>Publish / Advertisement Date</label><input type="date" name="publish_date" value="<?php echo e(old('publish_date')); ?>"></div>
        <div class="field"><label>Submission Closing Date</label><input type="date" name="closing_date" value="<?php echo e(old('closing_date')); ?>"></div>
        <div class="field"><label>Opening Date</label><input type="date" name="opening_date" value="<?php echo e(old('opening_date')); ?>"></div>
      </div>
      <div class="field" style="margin-top:10px"><label>Reason, if this differs from the standard policy schedule</label>
        <input name="schedule_override_reason" value="<?php echo e(old('schedule_override_reason')); ?>" placeholder="e.g. emergency response — shortened notice period approved by ED">
      </div>
    </div>
  <?php else: ?>
    <div style="border-top:1px solid var(--line-soft);padding-top:14px">
      <b style="font-size:13.5px">Award Decision</b>
      <div style="font-size:12px;color:var(--muted);margin-top:2px">One row per vendor / lot — a case can be split across multiple vendors, as in the real Cyclone Shelter minutes.</div>
      <div style="border:1px solid var(--line);border-radius:10px;overflow-x:auto;margin-top:10px">
        <table class="data" id="awards" style="min-width:640px">
          <thead><tr><th>Vendor</th><th>Scope / Lot</th><th class="num">Awarded Amount</th><th></th></tr></thead>
          <tbody></tbody>
        </table>
      </div>
      <button type="button" class="btn btn-outline" style="margin-top:10px" onclick="addAward()">+ Add award line</button>
    </div>
  <?php endif; ?>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="<?php echo e(route('cases.show', $meeting->procurementCase)); ?>" class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary">Finalize resolution</button>
  </div>
</form>

<script>
let vendors = <?php echo json_encode($vendors->map(fn ($v) => ['id' => $v->id, 'name' => $v->name]), 512) ?>;

let wi = 0;
function addAward() {
  const i = wi++;
  const opts = vendors.map(v => `<option value="${v.id}">${v.name}</option>`).join('');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>
      <select name="awards[${i}][vendor_id]" onchange="this.nextElementSibling.value = this.options[this.selectedIndex].text !== 'Other / not in system' ? this.options[this.selectedIndex].text : ''" style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px;margin-bottom:4px">
        <option value="">Other / not in system</option>${opts}
      </select>
      <input name="awards[${i}][vendor_name]" placeholder="Vendor name" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px">
    </td>
    <td><input name="awards[${i}][scope_note]" placeholder="e.g. Najirhat A Malek GPS & Cyclone Shelter" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input type="number" name="awards[${i}][amount]" value="0" min="0" step="0.01" style="width:130px;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><button type="button" onclick="this.closest('tr').remove()" style="border:none;background:none;color:var(--bad);font-size:15px;cursor:pointer">×</button></td>`;
  document.querySelector('#awards tbody').appendChild(tr);
}

<?php if($meeting->meeting_type === 'second'): ?>
addAward();
<?php endif; ?>
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/meetings/create-resolution.blade.php ENDPATH**/ ?>