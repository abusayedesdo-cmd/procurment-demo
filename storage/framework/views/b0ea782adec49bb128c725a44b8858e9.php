<?php $__env->startSection('title', 'All Modules'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;700&display=swap');

    :root {
        --ink: #0F172A;
        --paper: #FFFFFF;
        --surface: #F8FAFC;
        --line: #E2E8F0;
        --muted: #64748B;
        --accent: #0D9488;
        --accent-dark: #0F766E;
        --slate-bg: #F1F5F9;
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .shell {
        max-width: 1080px;
        margin: 0 auto;
        padding: 2.5rem 2rem 4rem;
    }

    .page-header {
        padding-bottom: 1.75rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--line);
    }

    .eyebrow {
        font-family: 'JetBrains Mono', monospace;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        margin: 0 0 .5rem;
    }

    .page-header h1 {
        margin: 0 0 .5rem;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--ink);
    }

    .page-header p {
        margin: 0 0 1.35rem;
        font-size: .9rem;
        color: var(--muted);
        max-width: 62ch;
        line-height: 1.55;
    }

    .section-filters {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
    }

    .section-capsule {
        display: inline-flex;
        align-items: center;
        background: var(--paper);
        color: var(--muted);
        border: 1px solid var(--line);
        border-radius: 999px;
        padding: .4rem .95rem;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        transition: border-color .15s ease, background .15s ease, color .15s ease;
        font-family: inherit;
    }
    .section-capsule:hover { border-color: var(--accent); color: var(--accent-dark); }
    .section-capsule.active {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }

    .group-list {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .group-panel {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 1.35rem 1.5rem;
    }
    .group-panel.hidden { display: none; }

    .group-panel h3 {
        margin: 0 0 1rem;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--accent-dark);
    }

    .module-list {
        display: flex;
        flex-direction: column;
    }

    .module-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem .2rem;
        border-bottom: 1px solid var(--line);
        text-decoration: none;
        color: var(--ink);
        font-size: .9rem;
        font-weight: 600;
        transition: background .15s ease, padding-left .15s ease, color .15s ease;
    }
    .module-list .module-row:last-child { border-bottom: none; }
    .module-row:hover {
        background: var(--surface);
        padding-left: .6rem;
        color: var(--accent-dark);
    }
    .module-row .chevron {
        color: var(--muted);
        font-weight: 400;
        flex-shrink: 0;
        transition: transform .15s ease, color .15s ease;
    }
    .module-row:hover .chevron {
        color: var(--accent);
        transform: translateX(3px);
    }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="shell">
        <div class="page-header">
            <p class="eyebrow">Procurement</p>
            <h1>All Modules</h1>
            <p>Grouped by document section. Select a section to filter, or view all.</p>
            <div id="sectionFilters" class="section-filters"></div>
        </div>

        <div id="groupsRoot" class="group-list"></div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script src="<?php echo e(asset('js/module-configs.js')); ?>"></script>
<script>
    const root = document.getElementById('groupsRoot');
    const filters = document.getElementById('sectionFilters');

    root.innerHTML = MODULE_GROUPS.map((group, i) => `
        <div class="group-panel" data-group-index="${i}">
            <h3>${group.title}</h3>
            <div class="module-list">
                ${group.slugs.map(slug => `
                    <a href="/modules/${slug}" class="module-row">
                        <span>${MODULE_CONFIGS[slug].title}</span>
                        <span class="chevron">→</span>
                    </a>
                `).join('')}
            </div>
        </div>
    `).join('');

    filters.innerHTML = `
        <button type="button" class="section-capsule active" data-filter="all">All</button>
        ${MODULE_GROUPS.map((group, i) => `
            <button type="button" class="section-capsule" data-filter="${i}">${group.title}</button>
        `).join('')}
    `;

    filters.addEventListener('click', (e) => {
        const btn = e.target.closest('.section-capsule');
        if (!btn) return;

        filters.querySelectorAll('.section-capsule').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const target = btn.dataset.filter;
        root.querySelectorAll('.group-panel').forEach(panel => {
            panel.classList.toggle('hidden', target !== 'all' && panel.dataset.groupIndex !== target);
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/modules/index.blade.php ENDPATH**/ ?>