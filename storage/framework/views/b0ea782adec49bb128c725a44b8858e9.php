<?php $__env->startSection('title', 'সব মডিউল'); ?>

<?php $__env->startSection('content'); ?>
    <h1>সব মডিউল</h1>
    <p class="muted">Document অনুযায়ী section-ভিত্তিক গ্রুপ করা — Procurement Plan (B), Process/Action (C), Framework Agreement (D), Sole Sourcing (E)।</p>

    <div id="groupsRoot"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/module-configs.js')); ?>"></script>
<script>
    const root = document.getElementById('groupsRoot');
    root.innerHTML = MODULE_GROUPS.map(group => `
        <div class="card">
            <h3>${group.title}</h3>
            <div style="display:flex; flex-wrap:wrap; gap:.5rem;">
                ${group.slugs.map(slug => `
                    <a href="/modules/${slug}" class="btn secondary">${MODULE_CONFIGS[slug].title}</a>
                `).join('')}
            </div>
        </div>
    `).join('');
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/modules/index.blade.php ENDPATH**/ ?>