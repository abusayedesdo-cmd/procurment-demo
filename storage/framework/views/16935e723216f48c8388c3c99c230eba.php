<?php $__env->startSection('title', 'Purchase Requisitions'); ?>

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
        --amber: #B45309;
        --amber-bg: #FFFBEB;
        --amber-line: #FDE68A;
        --green: #15803D;
        --green-bg: #F0FDF4;
        --green-line: #BBF7D0;
        --red: #991B1B;
        --red-bg: #FEF2F2;
        --red-line: #FECACA;
        --slate-bg: #F1F5F9;
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .shell {
        max-width: 1080px;
        margin: 0 auto;
        padding: 2.5rem 2rem 4rem;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        padding-bottom: 1.75rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--line);
        flex-wrap: wrap;
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
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--ink);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: var(--ink);
        color: #fff;
        border: 1px solid var(--ink);
        border-radius: 7px;
        padding: .6rem 1.1rem;
        cursor: pointer;
        text-decoration: none;
        font-size: .85rem;
        font-weight: 600;
        font-family: inherit;
        transition: background .15s ease, border-color .15s ease;
    }
    .btn:hover { background: var(--accent-dark); border-color: var(--accent-dark); }
    .btn.primary { background: var(--accent); border-color: var(--accent); }
    .btn.primary:hover { background: var(--accent-dark); border-color: var(--accent-dark); }

    .panel {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 1.35rem 1.5rem;
    }

    .toolbar {
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 1.35rem;
        flex-wrap: wrap;
    }

    .form-field { display: flex; flex-direction: column; }
    .form-field.filter-field { min-width: 220px; }
    label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        color: var(--muted);
        margin: 0 0 .4rem;
    }
    select {
        width: 100%;
        padding: .55rem .7rem;
        border: 1px solid var(--line);
        border-radius: 7px;
        font-size: .88rem;
        font-family: inherit;
        color: var(--ink);
        background: var(--paper);
    }
    select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(13,148,136,.12);
    }

    .error-box {
        background: var(--red-bg);
        color: var(--red);
        border: 1px solid var(--red-line);
        padding: .75rem 1rem;
        border-radius: 8px;
        margin-bottom: 1.25rem;
        font-size: .88rem;
    }

    .table-wrap { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .87rem; }
    thead th {
        text-align: left;
        padding: .6rem .7rem;
        color: var(--muted);
        font-weight: 600;
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        border-bottom: 1px solid var(--line);
        white-space: nowrap;
    }
    tbody td {
        text-align: left;
        padding: .8rem .7rem;
        border-bottom: 1px solid var(--line);
        color: var(--ink);
        vertical-align: middle;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover td { background: var(--surface); }
    td.num, th.num { text-align: right; font-family: 'JetBrains Mono', monospace; }
    td.pr-number { font-family: 'JetBrains Mono', monospace; font-weight: 600; }
    td.muted-cell { color: var(--muted); text-align: center; padding: 2rem .7rem; }
    td.view-link a { color: var(--accent-dark); font-weight: 600; text-decoration: none; font-size: .85rem; }
    td.view-link a:hover { text-decoration: underline; }

    .badge {
        display: inline-block;
        padding: .2rem .65rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        border: 1px solid transparent;
    }
    .badge.draft { background: var(--slate-bg); color: #475569; border-color: var(--line); }
    .badge.reviewed, .badge.checked { background: var(--amber-bg); color: var(--amber); border-color: var(--amber-line); }
    .badge.approved { background: var(--green-bg); color: var(--green); border-color: var(--green-line); }
    .badge.rejected { background: var(--red-bg); color: var(--red); border-color: var(--red-line); }

    /* ---- Status filter cards ---- */
    .status-filters {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
    }
    .status-card {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 9px;
        padding: .55rem 1rem;
        font-size: .85rem;
        font-weight: 600;
        color: var(--ink);
        cursor: pointer;
        font-family: inherit;
        transition: border-color .15s ease, background .15s ease, transform .1s ease;
    }
    .status-card:hover { border-color: #CBD5E1; background: var(--surface); }
    .status-card .dot {
        width: 8px; height: 8px;
        border-radius: 999px;
        background: #94A3B8;
        flex-shrink: 0;
    }
    .status-card[data-tone="draft"] .dot { background: #64748B; }
    .status-card[data-tone="pending"] .dot,
    .status-card[data-tone="reviewed"] .dot,
    .status-card[data-tone="checked"] .dot { background: var(--amber); }
    .status-card[data-tone="approved"] .dot { background: var(--green); }
    .status-card[data-tone="rejected"] .dot { background: var(--red); }

    .status-card.active {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }
    .status-card.active .dot { background: #fff; }

    .status-card .count {
        font-family: 'JetBrains Mono', monospace;
        font-size: .76rem;
        font-weight: 700;
        color: var(--muted);
        background: var(--surface);
        border-radius: 999px;
        padding: .05rem .45rem;
        min-width: 1.4em;
        text-align: center;
    }
    .status-card.active .count { color: #fff; background: rgba(255,255,255,.15); }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
        .page-header { flex-direction: column; align-items: flex-start; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="shell">
        <div class="page-header">
            <div>
                <p class="eyebrow">Procurement</p>
                <h1>Purchase Requisitions</h1>
            </div>
            <?php if(auth()->user()->isPrCreator()): ?>
                <a href="<?php echo e(route('purchase-requisitions.create')); ?>" class="btn primary">+ New PR</a>
            <?php endif; ?>
        </div>

        <div class="panel">
            <div class="toolbar">
                <div class="status-filters" id="statusFilters"></div>
            </div>

            <div id="errorBox" class="error-box" style="display:none;"></div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>PR Number</th>
                            <th>Window</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th class="num">Total (৳)</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="prTableBody">
                        <tr><td colspan="7" class="muted-cell">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const tbody = document.getElementById('prTableBody');
    const errorBox = document.getElementById('errorBox');
    const statusFilters = document.getElementById('statusFilters');

    const STATUS_OPTIONS = [
        { value: '', label: 'All', tone: 'all' },
        { value: 'draft', label: 'Draft', tone: 'draft' },
        { value: 'reviewed,checked', label: 'Pending (Review/Check)', tone: 'pending' },
        { value: 'reviewed', label: 'Reviewed', tone: 'reviewed' },
        { value: 'checked', label: 'Checked', tone: 'checked' },
        { value: 'approved', label: 'Approved', tone: 'approved' },
        { value: 'rejected', label: 'Rejected', tone: 'rejected' },
    ];

    // Pre-select status from ?status= query param (used by dashboard cards)
    const urlParams = new URLSearchParams(window.location.search);
    let currentStatus = urlParams.get('status') ?? '';
    if (!STATUS_OPTIONS.some(o => o.value === currentStatus)) currentStatus = '';

    let allPrs = []; // full, unfiltered list — used both for counts and client-side filtering

    function computeCounts(prs) {
        const counts = {};
        STATUS_OPTIONS.forEach(o => {
            if (o.value === '') {
                counts[o.value] = prs.length;
            } else {
                const statuses = o.value.split(',');
                counts[o.value] = prs.filter(pr => statuses.includes(pr.status)).length;
            }
        });
        return counts;
    }

    function renderFilters(counts) {
        statusFilters.innerHTML = STATUS_OPTIONS.map(o => `
            <button type="button" class="status-card ${o.value === currentStatus ? 'active' : ''}" data-tone="${o.tone}" data-value="${o.value}">
                <span class="dot"></span>${o.label}
                <span class="count">${counts[o.value] ?? 0}</span>
            </button>
        `).join('');
    }

    function badge(status) {
        return `<span class="badge ${status}">${status}</span>`;
    }

    function renderTable(prs) {
        if (!prs.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="muted-cell">No purchase requisitions found.</td></tr>';
            return;
        }

        tbody.innerHTML = prs.map(pr => `
            <tr>
                <td class="pr-number">${pr.pr_number}</td>
                <td>${pr.window_type}</td>
                <td>${pr.category?.name ?? '-'}</td>
                <td>${pr.requisition_date}</td>
                <td class="num">${Number(pr.total_estimated_amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                <td>${badge(pr.status)}</td>
                <td class="view-link"><a href="/purchase-requisitions/${pr.id}">View</a></td>
            </tr>
        `).join('');
    }

    function applyFilter() {
        const filtered = currentStatus
            ? allPrs.filter(pr => currentStatus.split(',').includes(pr.status))
            : allPrs;
        renderTable(filtered);
    }

    statusFilters.addEventListener('click', (e) => {
        const card = e.target.closest('.status-card');
        if (!card) return;

        currentStatus = card.dataset.value;
        statusFilters.querySelectorAll('.status-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        applyFilter();
    });

    async function loadPrs() {
        tbody.innerHTML = '<tr><td colspan="7" class="muted-cell">Loading…</td></tr>';
        errorBox.style.display = 'none';
        renderFilters({}); // show filter bar immediately with 0s while loading

        try {
            // Fetch the full unfiltered list once — counts and table filtering are done client-side
            const { data } = await api.get('/purchase-requisitions');
            allPrs = data;

            renderFilters(computeCounts(allPrs));
            applyFilter();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            tbody.innerHTML = '';
        }
    }

    loadPrs();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/purchase-requisitions/index.blade.php ENDPATH**/ ?>