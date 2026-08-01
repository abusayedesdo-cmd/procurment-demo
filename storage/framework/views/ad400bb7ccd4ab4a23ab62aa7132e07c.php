<?php $__env->startSection('title', 'Purchase Requisitions'); ?>

<?php $__env->startSection('content'); ?>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
        <h1 style="margin:0;">Purchase Requisitions</h1>
        <?php if(auth()->user()->roleName() === \App\Models\User::REQUESTER): ?>
            <a href="<?php echo e(route('purchase-requisitions.create')); ?>" class="btn">+ নতুন PR</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="row" style="margin-bottom:1rem;">
            <div>
                <label for="statusFilter">Status</label>
                <select id="statusFilter">
                    <option value="">সবগুলো</option>
                    <option value="draft">Draft</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="checked">Checked</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <div id="errorBox" class="error-box" style="display:none;"></div>

        <table>
            <thead>
                <tr>
                    <th>PR নাম্বার</th>
                    <th>Window</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Total (৳)</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="prTableBody">
                <tr><td colspan="7" class="muted">লোড হচ্ছে...</td></tr>
            </tbody>
        </table>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const tbody = document.getElementById('prTableBody');
    const errorBox = document.getElementById('errorBox');
    const statusFilter = document.getElementById('statusFilter');

    // Pre-select status from ?status= query param (used by dashboard cards)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status')) statusFilter.value = urlParams.get('status');

    function badge(status) {
        return `<span class="badge ${status}">${status}</span>`;
    }

    async function loadPrs() {
        tbody.innerHTML = '<tr><td colspan="7" class="muted">লোড হচ্ছে...</td></tr>';
        errorBox.style.display = 'none';

        try {
            const qs = statusFilter.value ? `?status=${statusFilter.value}` : '';
            const { data } = await api.get(`/purchase-requisitions${qs}`);

            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="7" class="muted">কোনো PR পাওয়া যায়নি।</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(pr => `
                <tr>
                    <td>${pr.pr_number}</td>
                    <td>${pr.window_type}</td>
                    <td>${pr.category?.name ?? '-'}</td>
                    <td>${pr.requisition_date}</td>
                    <td>${Number(pr.total_estimated_amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                    <td>${badge(pr.status)}</td>
                    <td><a href="/purchase-requisitions/${pr.id}">দেখুন</a></td>
                </tr>
            `).join('');
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            tbody.innerHTML = '';
        }
    }

    statusFilter.addEventListener('change', loadPrs);
    loadPrs();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\New folder\procurment\resources\views/purchase-requisitions/index.blade.php ENDPATH**/ ?>