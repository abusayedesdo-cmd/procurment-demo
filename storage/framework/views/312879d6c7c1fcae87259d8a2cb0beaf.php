<?php $__env->startSection('title', $meeting->typeLabel() . ' Attendance'); ?>
<?php $__env->startSection('content'); ?>

<div><a href="<?php echo e(url()->previous() ?: route('cases.show', $meeting->procurementCase)); ?>"
        onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }"
        style="font-size:12.5px;font-weight:600;text-decoration:none">← Back</a></div>

<form method="POST" action="<?php echo e(route('meetings.attendance.store', $meeting)); ?>" class="card card-pad" style="display:flex;flex-direction:column;gap:16px">
  <?php echo csrf_field(); ?>
  <div>
    <div style="font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--muted)">Step 2 of 3 — Attendance</div>
    <b style="font-size:16px"><?php echo e($meeting->typeLabel()); ?> — <?php echo e($meeting->location); ?>, <?php echo e($meeting->meeting_date->format('d M Y')); ?><?php if($meeting->meeting_time): ?>, <?php echo e($meeting->meeting_time); ?><?php endif; ?></b>
    <div style="font-size:12.5px;color:var(--muted);margin-top:2px">Case: <?php echo e($meeting->procurementCase->ref); ?> — <?php echo e($meeting->procurementCase->title); ?></div>
    <div style="font-size:12px;color:var(--muted);margin-top:6px">The roster below is pre-filled — remove anyone who didn't attend, or add someone extra.</div>
  </div>

  <?php if($errors->any()): ?>
    <div style="background:#FBF1EF;border:1px solid #E5C0BB;color:var(--bad);border-radius:8px;padding:10px 14px;font-size:13px"><?php echo e($errors->first()); ?></div>
  <?php endif; ?>

  <div>
    <b style="font-size:13.5px">Attendees</b>
    <div style="border:1px solid var(--line);border-radius:10px;overflow-x:auto;margin-top:10px">
      <table class="data" id="attendees" style="min-width:520px">
        <thead><tr><th>Name</th><th>Designation</th><th></th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <button type="button" class="btn btn-outline" style="margin-top:10px" onclick="addAttendee()">+ Add attendee</button>
  </div>

  <div style="display:flex;gap:10px;justify-content:flex-end">
    <a href="<?php echo e(url()->previous() ?: route('cases.show', $meeting->procurementCase)); ?>"
      onclick="if (window.history.length > 1) { event.preventDefault(); window.history.back(); }"
      class="btn btn-outline">Cancel</a>
    <button class="btn btn-primary">Save attendance</button>
  </div>
</form>

<?php
  // Built here instead of inline inside @json(...) because Blade's @json
  // directive splits its argument on EVERY comma (to support the optional
  // $options/$depth args), including commas hidden inside string values
  // like "roleLabel() . ', Central Procurement Committee'". That silently
  // truncates the expression and leaves an unclosed array in the compiled
  // PHP, which breaks the parser. Passing a plain variable avoids it.
  $rosterJs = $roster->map(fn ($m) => [
      'id' => $m->id,
      'name' => $m->name,
      'designation' => $m->roleLabel() . ', Central Procurement Committee',
  ]);
?>

<script>
let roster = <?php echo json_encode($rosterJs, 15, 512) ?>;

let ai = 0;
function addAttendee(name = '', designation = '', committeeMemberId = '') {
  const i = ai++;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="hidden" name="attendees[${i}][committee_member_id]" value="${committeeMemberId}"><input name="attendees[${i}][name]" value="${name}" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><input name="attendees[${i}][designation]" value="${designation}" required style="width:100%;border:1px solid #D9DAE8;border-radius:6px;padding:7px 8px;font-size:13px"></td>
    <td><button type="button" onclick="this.closest('tr').remove()" style="border:none;background:none;color:var(--bad);font-size:15px;cursor:pointer">×</button></td>`;
  document.querySelector('#attendees tbody').appendChild(tr);
}

roster.forEach(m => addAttendee(m.name, m.designation, m.id));
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/meetings/create-attendance.blade.php ENDPATH**/ ?>