<?php $__env->startSection('title', 'Procurement Annual Plans'); ?>

<?php $__env->startSection('content'); ?>
    <div id="errorBox" class="error-box" style="display:none;"></div>

    <div class="row" style="justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h3 style="margin:0;">Annual Plans</h3>
        <!-- Pushed buttons container to the far right -->
        <div style="display:flex; gap:8px; align-items:center; margin-left:auto;">
            <a class="btn secondary" href="<?php echo e(route('dashboard')); ?>">← Back to Dashboard</a>
            <a class="btn" id="createPlanLink" href="<?php echo e(route('annual-plans.create')); ?>">Create New Plan</a>
        </div>
    </div>

    <input
        type="text"
        id="planSearch"
        placeholder="Search by title, donor, or district..."
        style="width:100%; max-width:420px; padding:8px 12px; font-size:13px; border:1px solid #E2E8F0; border-radius:8px; margin-bottom:1rem;"
    >

    <div id="plansList">Loading...</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const errorBox = document.getElementById('errorBox');
    let allPlans = [];

    async function loadPlans() {
        try {
            const { data } = await api.get('/procurement-annual-plans');
            allPlans = data;
            renderPlans(allPlans);
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    function renderPlans(plans) {
        document.getElementById('plansList').innerHTML = plans.map(p => `
            <div class="card row" style="align-items:center;">
                <div><strong>${p.title}</strong><br><span class="muted">${p.plan_type} · ${p.donor_name ?? ''} · ${p.district?.name ?? ''}${p.upazila ? ', ' + p.upazila.name : ''} · ${formatDateTime(p.fiscal_year_start)} → ${formatDateTime(p.fiscal_year_end)}</span></div>
                <div><span class="badge ${p.status}">${p.status}</span></div>
                <div><a class="btn secondary" href="/annual-plans/${p.id}">Open</a></div>
            </div>
        `).join('') || '<div class="card muted">No plans found.</div>';
    }

    function formatDateTime(iso) {
        if (!iso) return '';
        return iso.replace('T', ' ').split('.')[0];
    }

    loadPlans();

    document.getElementById('planSearch').addEventListener('input', (e) => {
        const q = e.target.value.trim().toLowerCase();
        const filtered = !q ? allPlans : allPlans.filter(p => {
            const haystack = [p.title, p.donor_name, p.district?.name, p.upazila?.name, p.plan_type]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();
            return haystack.includes(q);
        });
        renderPlans(filtered);
    });

    // Only Admin/Budget Checker can create plans — hide the button for
    // everyone else instead of sending them to a form they can't use.
    (function gateCreateLink() {
        const canManage = window.currentUserRole === 'admin' || window.currentUserRole === 'budget_checker';
        if (canManage) return;
        document.getElementById('createPlanLink').style.display = 'none';
    })();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/annual-plan/index.blade.php ENDPATH**/ ?>