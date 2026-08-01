<?php $__env->startSection('title', $title ?? 'Module'); ?>

<?php $__env->startSection('content'); ?>
    <div id="resourceRoot">
        <p class="muted">লোড হচ্ছে...</p>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/module-configs.js')); ?>"></script>
<script src="<?php echo e(asset('js/resource-ui.js')); ?>"></script>
<script>
    window.currentUserId = <?php echo e(auth()->id()); ?>;

    const slug = <?php echo json_encode($slug, 15, 512) ?>;
    const config = MODULE_CONFIGS[slug];

    if (!config) {
        document.getElementById('resourceRoot').innerHTML =
            '<div class="error-box">অজানা module: ' + slug + '</div>';
    } else {
        initResourcePage(config);
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\New folder\procurment\resources\views/modules/show.blade.php ENDPATH**/ ?>