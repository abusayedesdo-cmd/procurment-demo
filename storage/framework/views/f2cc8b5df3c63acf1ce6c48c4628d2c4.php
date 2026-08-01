<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'ESDO Procurement'); ?></title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f5f6fa; margin: 0; color: #1f2430; }
        .topbar { background: #1f2937; color: #fff; display: flex; align-items: center; justify-content: space-between; padding: .75rem 1.5rem; }
        .topbar a { color: #fff; text-decoration: none; }
        .topbar .brand { font-weight: 700; }
        .nav { display: flex; gap: 1rem; align-items: center; }
        .nav a { font-size: .9rem; opacity: .85; }
        .nav a:hover { opacity: 1; }
        .container { padding: 1.5rem; max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); margin-bottom: 1rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: .6rem .5rem; border-bottom: 1px solid #eef0f3; font-size: .9rem; }
        th { color: #6b7280; font-weight: 600; font-size: .8rem; text-transform: uppercase; }
        .btn { display: inline-block; background: #1f2937; color: #fff; border: none; border-radius: 6px; padding: .5rem 1rem; cursor: pointer; text-decoration: none; font-size: .9rem; }
        .topbar .btn.secondary {
            background: #374151;
            color: #ffffff;
            border: 1px solid #4b5563;
            padding: .3rem .74rem;
        }
        .topbar .btn.secondary:hover {
            background: #4b5563;
        }
        .btn.danger { background: #b91c1c; }
        label { display: block; font-size: .85rem; color: #6b7280; margin: 0 0 .3rem; }
        input, select, textarea { width: 100%; padding: .5rem .6rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: .9rem; font-family: inherit; }

        /* Old simple row grid — still used by the PR create form */
        .row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .75rem; align-items: start; }
        .item-row { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto; gap: .5rem; align-items: end; margin-bottom: .5rem; }

        /* New generic module form grid — every field is its own block,
           hidden/currentUser fields are excluded entirely, textarea and
           checkbox fields always take the full row width. */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem 1.25rem; align-items: start; }
        .form-field { display: flex; flex-direction: column; }
        .form-field.full-width { grid-column: 1 / -1; }
        .form-field.checkbox-field label { display: flex; align-items: center; gap: .5rem; font-size: .9rem; color: #1f2430; }
        .form-field.checkbox-field input[type="checkbox"] { width: auto; }

        .badge { display: inline-block; padding: .15rem .55rem; border-radius: 999px; font-size: .75rem; font-weight: 600; }
        .badge.draft { background: #e5e7eb; color: #374151; }
        .badge.reviewed, .badge.checked { background: #fef3c7; color: #92400e; }
        .badge.approved { background: #d1fae5; color: #065f46; }
        .badge.rejected { background: #fee2e2; color: #991b1b; }
        .error-box { background: #fee2e2; color: #991b1b; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: .9rem; }
        .muted { color: #6b7280; font-size: .85rem; }
    </style>
    <?php echo $__env->yieldContent('styles'); ?>
</head>
<body>
    <div class="topbar">
        <a href="<?php echo e(route('dashboard')); ?>" class="brand">ESDO Procurement</a>
        <div class="nav">
            <a href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
            <a href="<?php echo e(route('purchase-requisitions.index')); ?>">Purchase Requisitions</a>
            <?php if(in_array(auth()->user()->roleName(), [\App\Models\User::BUDGET_CHECKER, \App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN])): ?>
                <a href="<?php echo e(route('budget-dashboard')); ?>">Budget Dashboard</a>
                 <a href="<?php echo e(route('annual-plans.index')); ?>">Annual Plan</a>
            <?php endif; ?>
            <?php if(in_array(auth()->user()->roleName(), [\App\Models\User::PROCUREMENT_OFFICER, \App\Models\User::ADMIN])): ?>
                <a href="<?php echo e(route('modules.index')); ?>">All Modules</a>
            <?php endif; ?>
            <span class="muted"><?php echo e(auth()->user()->name ?? ''); ?></span>
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="btn secondary">Sign out</button>
            </form>
        </div>
    </div>

    <div class="container">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <script src="<?php echo e(asset('js/api.js')); ?>"></script>
    <?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\New Poject\Project_procrument\resources\views/layouts/app.blade.php ENDPATH**/ ?>