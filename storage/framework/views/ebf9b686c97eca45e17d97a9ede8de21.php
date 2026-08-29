

<?php $__env->startSection('title', 'PR Details'); ?>

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

        /* document / paper form palette — mirrors the DomPDF output */
        --doc-border: #333333;
        --doc-head-bg: #F2F2F2;
        --doc-text: #111111;
        --doc-muted: #444444;
    }

    body { font-family: 'Inter', system-ui, sans-serif; }

    .shell {
        max-width: 1000px;
        margin: 0 auto;
        padding: 2rem 2rem 4rem;
    }

    /* ---- Page toolbar ---- */
    .pr-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 1.5rem;
        padding-bottom: 1.25rem;
        margin-bottom: 1.5rem;
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

    .pr-title {
        margin: 0;
        display: flex;
        align-items: center;
        gap: .85rem;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: var(--ink);
    }

    .pr-title .pr-number {
        font-family: 'JetBrains Mono', monospace;
        font-weight: 700;
    }

    .header-actions { display: flex; gap: .6rem; flex-wrap: wrap; }

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
    .btn.secondary { background: transparent; color: var(--ink); border: 1px solid var(--line); }
    .btn.secondary:hover { background: var(--surface); border-color: #CBD5E1; }
    .btn.danger { background: var(--red); border-color: var(--red); }
    .btn.danger:hover { background: #7F1D1D; border-color: #7F1D1D; }
    .btn:disabled { opacity: .5; cursor: not-allowed; }

    /* ---- Status badges ---- */
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

    .muted { color: var(--muted); font-size: .85rem; }

    /* =========================================================
       DOCUMENT / PAPER-FORM THEME
       Mirrors resources/views/documents/purchase-requisition.blade.php
       (the DomPDF output) so the on-screen view matches the PDF.
       ========================================================= */
    .paper {
        background: var(--paper);
        border: 1px solid #D0D0D0;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        padding: 2rem 2.25rem 2.5rem;
        color: var(--doc-text);
        font-size: .86rem;
        line-height: 1.5;
        margin-bottom: 1.75rem;
    }

    .doc-head {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        justify-content: space-between;
    }
    .doc-head .doc-logo {
        width: 56px;
        height: 56px;
        object-fit: contain;
        flex-shrink: 0;
    }
    .doc-head .doc-org {
        flex: 1;
        text-align: center;
    }
    .doc-head .doc-org .org-name {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--doc-text);
    }
    .doc-head .doc-org .org-addr {
        font-size: .78rem;
        color: var(--doc-muted);
        margin-top: .1rem;
    }
    .doc-head .doc-org .doc-title {
        font-weight: 700;
        font-size: .98rem;
        margin-top: .5rem;
        color: var(--doc-text);
    }
    .doc-head .doc-meta {
        min-width: 150px;
        text-align: right;
        font-size: .82rem;
        flex-shrink: 0;
    }
    .doc-head .doc-meta .pr-no { font-weight: 700; }

    .doc-rule {
        border: none;
        border-top: 2px solid #000;
        margin: .85rem 0 1rem;
    }

    .doc-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem 1.5rem;
        font-size: .85rem;
        margin-bottom: 1rem;
    }
    .doc-meta-row span { color: var(--doc-muted); }
    .doc-meta-row strong { color: var(--doc-text); font-weight: 700; }

    .doc-status-badge { margin-left: .5rem; vertical-align: middle; }

    table.doc-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0 0 1rem;
        font-size: .82rem;
    }
    table.doc-table th, table.doc-table td {
        border: 1px solid var(--doc-border);
        padding: .5rem .6rem;
        vertical-align: top;
        text-align: left;
    }
    table.doc-table th {
        background: var(--doc-head-bg);
        font-weight: 700;
        text-transform: uppercase;
        font-size: .68rem;
        letter-spacing: .03em;
    }
    table.doc-table td.num, table.doc-table th.num { text-align: right; font-family: 'JetBrains Mono', monospace; }
    table.doc-table tr.total-row td { background: var(--doc-head-bg); font-weight: 700; }
    table.doc-table td.empty-row { height: 1.6rem; color: transparent; }

    .doc-inword {
        font-weight: 700;
        font-size: .88rem;
        margin: 0 0 .9rem;
    }

    .doc-budget-remarks { vertical-align: top; }
    .doc-budget-remarks .doc-budget-remarks-text { white-space: pre-wrap; }

    table.doc-plain { width: 100%; border-collapse: collapse; margin: 1.25rem 0 0; font-size: .84rem; }
    table.doc-plain td { border: none; padding: .55rem 0; vertical-align: top; }
    table.doc-plain .sig-line { display: block; color: var(--doc-muted); }

    .doc-footer {
        margin-top: 1.75rem;
        padding-top: .75rem;
        border-top: 1px solid var(--line);
        color: var(--doc-muted);
        font-size: .72rem;
        text-align: center;
    }

    /* ---- Additional (web-only) details strip — data not on the paper form ---- */
    .extra-details {
        margin-top: -1rem;
        margin-bottom: 1.75rem;
    }
    .extra-details summary {
        cursor: pointer;
        font-size: .78rem;
        font-weight: 600;
        color: var(--muted);
        list-style: none;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }
    .extra-details summary::-webkit-details-marker { display: none; }
    .extra-details summary::before { content: '▸'; font-size: .7rem; transition: transform .15s ease; }
    .extra-details[open] summary::before { transform: rotate(90deg); }
    .extra-details .field-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1rem 1.5rem;
        margin-top: .9rem;
        padding: 1rem 1.1rem;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 8px;
    }
    .field { display: flex; flex-direction: column; gap: .3rem; }
    .field-label {
        font-size: .68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--muted);
    }
    .field-value { font-size: .87rem; color: var(--ink); font-weight: 500; }
    .field-value.mono { font-family: 'JetBrains Mono', monospace; }
    .field-value.empty { color: #94A3B8; font-weight: 400; }
    .field-value a { color: var(--accent-dark); font-weight: 600; text-decoration: none; }
    .field-value a:hover { text-decoration: underline; }

    /* ---- Approval history (live workflow log, not on the paper form) ---- */
    .pr-section { margin-bottom: 2rem; }
    .pr-section > .eyebrow { margin-bottom: .85rem; }

    .panel {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 1.35rem 1.5rem;
    }

    .table-wrap { overflow-x: auto; }
    table.log-table { width: 100%; border-collapse: collapse; font-size: .87rem; }
    .log-table thead th {
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
    .log-table tbody td {
        text-align: left;
        padding: .7rem .7rem;
        border-bottom: 1px solid var(--line);
        color: var(--ink);
        vertical-align: top;
    }
    .log-table tbody tr:last-child td { border-bottom: none; }
    .log-table tbody tr:hover td { background: var(--surface); }
    .log-table td.muted-cell { color: var(--muted); text-align: center; padding: 1.5rem .7rem; }

    /* ---- Action / status area ---- */
    .status-note {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        display: flex;
        gap: .9rem;
        align-items: flex-start;
        background: var(--surface);
    }
    .status-note .tag {
        font-family: 'JetBrains Mono', monospace;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        background: var(--paper);
        border: 1px solid var(--line);
        padding: .2rem .5rem;
        border-radius: 5px;
        white-space: nowrap;
        margin-top: .1rem;
    }
    .status-note p { margin: 0; font-size: .88rem; color: var(--muted); line-height: 1.5; }

    .action-panel h3 {
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 700;
        color: var(--ink);
    }
    .action-panel .role-tag {
        font-size: .72rem;
        font-weight: 600;
        color: var(--accent-dark);
        background: var(--green-bg);
        border: 1px solid var(--green-line);
        padding: .1rem .55rem;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-left: .5rem;
        vertical-align: middle;
    }

    .form-field { display: flex; flex-direction: column; margin-bottom: 1rem; }
    .form-field:last-of-type { margin-bottom: 0; }
    label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        color: var(--muted);
        margin: 0 0 .4rem;
    }
    input, select, textarea {
        width: 100%;
        padding: .55rem .7rem;
        border: 1px solid var(--line);
        border-radius: 7px;
        font-size: .88rem;
        font-family: inherit;
        color: var(--ink);
        background: var(--paper);
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(13,148,136,.12);
    }
    textarea { resize: vertical; }

    .checkbox-field { flex-direction: row; align-items: center; }
    .checkbox-field label {
        display: flex;
        align-items: center;
        gap: .55rem;
        font-size: .87rem;
        color: var(--ink);
        font-weight: 500;
        margin: 0;
    }
    .checkbox-field input[type="checkbox"] { width: auto; accent-color: var(--accent); }

    .budget-summary-table { margin-top: 1rem; width: 100%; }
    .budget-summary-table td { border-bottom: 1px solid var(--line); padding: .55rem .3rem; }
    .budget-summary-table tr:last-child td { border-bottom: none; }
    .budget-summary-table td.bold { font-weight: 700; font-family: 'JetBrains Mono', monospace; }

    .decision-hint {
        margin: .6rem 0 0;
        font-size: .85rem;
        padding: .6rem .85rem;
        border-radius: 7px;
    }
    .decision-hint.ok { background: var(--green-bg); color: var(--green); border: 1px solid var(--green-line); }
    .decision-hint.bad { background: var(--red-bg); color: var(--red); border: 1px solid var(--red-line); }

    .btn-row { display: flex; gap: .6rem; flex-wrap: wrap; margin-top: 1.25rem; }

    @media (max-width: 560px) {
        .shell { padding: 1.5rem 1.1rem 3rem; }
        .pr-header { flex-direction: column; align-items: flex-start; }
        .paper { padding: 1.25rem 1.1rem 1.75rem; }
        .doc-head { flex-direction: column; align-items: center; text-align: center; }
        .doc-head .doc-meta { text-align: center; }
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="shell">
        <div id="errorBox" class="error-box" style="display:none;"></div>
        <div id="prDetail">
            <p class="muted">Loading…</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    const prId = <?php echo e((int) $id); ?>;
    const currentUserRole = <?php echo json_encode(auth()->user()->roleName(), 15, 512) ?>;

    // Populated by renderBudgetCheckCard(), read by submitBudgetCheck() —
    // both need the same "which budget line, what are its figures" data,
    // but submitBudgetCheck() is a top-level handler with no access to
    // renderBudgetCheckCard()'s local `pr`/`budgetLines` variables.
    let budgetCheckContext = { line: null, budgetLines: [] };
    const errorBox = document.getElementById('errorBox');
    const detail = document.getElementById('prDetail');

    function badge(status) {
        return `<span class="badge ${status}">${status}</span>`;
    }

    function esc(v) {
        if (v === null || v === undefined) return '';
        return String(v).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function money(n) {
        return Number(n || 0).toLocaleString('en-BD', { minimumFractionDigits: 2 });
    }

    function formatDocDate(v) {
        if (!v) return '';
        const d = new Date(v);
        if (isNaN(d.getTime())) return '';
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        return `${dd}.${mm}.${d.getFullYear()}`;
    }

    function amountInWords(n) {
        const num = Math.round(Number(n || 0));
        if (num === 0) return 'Zero Taka Only';

        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function twoDigits(x) {
            if (x < 20) return ones[x];
            return tens[Math.floor(x / 10)] + (x % 10 ? ' ' + ones[x % 10] : '');
        }
        function threeDigits(x) {
            if (x < 100) return twoDigits(x);
            return ones[Math.floor(x / 100)] + ' Hundred' + (x % 100 ? ' ' + twoDigits(x % 100) : '');
        }

        // Bangladeshi grouping: Crore (1,00,00,000) / Lakh (1,00,000) / Thousand / Hundred
        let rem = num;
        const crore = Math.floor(rem / 10000000); rem %= 10000000;
        const lakh = Math.floor(rem / 100000); rem %= 100000;
        const thousand = Math.floor(rem / 1000); rem %= 1000;
        const hundred = rem;

        const parts = [];
        if (crore) parts.push(threeDigits(crore) + ' Crore');
        if (lakh) parts.push(threeDigits(lakh) + ' Lakh');
        if (thousand) parts.push(threeDigits(thousand) + ' Thousand');
        if (hundred) parts.push(threeDigits(hundred));

        return parts.join(' ') + ' Taka Only';
    }

    // Latest "approved" action recorded for a given role — used for the
    // Requested/Endorsed/Finance Requested/Recommend/Approved signature
    // lines, same lookup the PDF (DocumentDownloadController) uses.
    function approvalByRole(pr, role) {
        return (pr.approvals || [])
            .filter(a => a.role_at_action === role && a.action === 'approved')
            .sort((a, b) => {
                const byDate = new Date(b.acted_at || 0) - new Date(a.acted_at || 0);
                return byDate !== 0 ? byDate : (b.id - a.id);
            })[0] || null;
    }

    function latestBudgetCheck(pr) {
        return (pr.budget_checks || [])[0] || null;
    }

    function isBudgetCheckStage(pr) {
        return pr.window_type === 'PR' && pr.status === 'reviewed'
            && ['budget_checker', 'admin'].includes(currentUserRole);
    }

    function isReviewStage(pr) {
        return pr.status === 'draft'
            && ['reviewer', 'admin'].includes(currentUserRole);
    }

    function isApproverStage(pr) {
        if (['approver', 'admin'].includes(currentUserRole) === false) return false;
        // 'checked' now belongs to Focal Person (see below) — the Approver
        // role only still acts on BOQ/TOR/Design & Drawing windows, which
        // go straight from Reviewer to Approver at 'reviewed'.
        return pr.window_type !== 'PR' && pr.status === 'reviewed';
    }

    // Keep in sync with PrApprovalController::HIGH_VALUE_THRESHOLD.
    const HIGH_VALUE_THRESHOLD = 750000;
    function isHighValue(pr) {
        return Number(pr.total_estimated_amount) >= HIGH_VALUE_THRESHOLD;
    }

    // The Budget Checker picks who the PR goes to next (routed_to). For
    // PRs checked before that field existed (routed_to is null), fall
    // back to the old amount-based rule.
    function routedToFocal(pr) {
        return pr.routed_to ? pr.routed_to === 'focal_person' : !isHighValue(pr);
    }

    function routedToEd(pr) {
        return pr.routed_to ? pr.routed_to === 'executive_director' : isHighValue(pr);
    }

    function isFocalReviewStage(pr) {
        return pr.window_type === 'PR' && pr.status === 'checked' && routedToFocal(pr)
            && ['focal_person', 'admin'].includes(currentUserRole);
    }

    function isEdApprovalStage(pr) {
        // 'focal_reviewed' is the legacy status for PRs already routed
        // through the Focal Person before this branching existed — those
        // still need the ED too.
        return pr.window_type === 'PR'
            && ((pr.status === 'checked' && routedToEd(pr)) || pr.status === 'focal_reviewed')
            && ['executive_director', 'admin'].includes(currentUserRole);
    }

    async function load() {
        try {
            const { data: pr } = await api.get(`/purchase-requisitions/${prId}`);
            render(pr);
            renderActionArea(pr);
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    function render(pr) {
        const items = pr.items || [];

        const itemsRows = items.map((li, i) => `
            <tr>
                <td>${i + 1}</td>
                <td>${esc(li.item?.name ?? '')}</td>
                <td>${esc(li.specification ?? li.item?.specification ?? '')}</td>
                <td>${esc(li.unit?.name ?? '')}</td>
                <td class="num">${Number(li.quantity).toLocaleString()}</td>
                <td class="num">${Number(li.rate_bdt).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                <td class="num">${Number(li.total_amount).toLocaleString('en-BD', {minimumFractionDigits: 2})}</td>
                <td>${esc(li.ac_code ?? '')}</td>
            </tr>
        `).join('');

        // Pad with blank rows up to 6, same as the printed PR form.
        let blankRows = '';
        for (let i = items.length; i < 6; i++) {
            blankRows += `<tr><td class="empty-row">&nbsp;</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>`;
        }

        const classificationRows = items.map(li => `
            <tr>
                <td>${esc(pr.category?.name ?? '-')}</td>
                <td>${esc(li.item?.chart_of_account?.name ?? '-')}</td>
                <td>${li.is_fixed_asset ? 'Yes' : 'No'}</td>
                <td>${esc(li.item?.name ?? '')}</td>
            </tr>
        `).join('');

        const approvalRows = (pr.approvals || []).map(a => `
            <tr>
                <td>${esc(a.acted_at)}</td>
                <td>${esc(a.user?.name ?? '')}</td>
                <td>${esc(a.role_at_action)}</td>
                <td>${badge(a.action === 'approved' ? 'approved' : (a.action === 'rejected' ? 'rejected' : 'draft'))} ${esc(a.action)}</td>
                <td>${esc(a.remarks ?? '')}</td>
            </tr>
        `).join('') || '<tr><td colspan="5" class="muted-cell">No action recorded yet.</td></tr>';

        const budgetCheck = latestBudgetCheck(pr);
        const endorsedBy = approvalByRole(pr, 'Reviewer');
        const financeRequestedBy = approvalByRole(pr, 'Budget Checker');
        const recommendedBy = approvalByRole(pr, 'Focal Person');
        const approvedBy = approvalByRole(pr, 'Executive Director');
        const dash = '.................';

        detail.innerHTML = `
            <div class="pr-header">
                <div>
                    <p class="eyebrow">Purchase Requisition</p>
                    <h1 class="pr-title">
                        <span class="pr-number">${esc(pr.pr_number)}</span> ${badge(pr.status)}
                    </h1>
                </div>
                <div class="header-actions">
                    <a href="/api/purchase-requisitions/${pr.id}/pdf" class="btn secondary" target="_blank">Download PDF</a>
                    <a href="<?php echo e(route('purchase-requisitions.index')); ?>" class="btn secondary">← Back to list</a>
                </div>
            </div>

            <div class="paper">
                <div class="doc-head">
                    <img class="doc-logo" src="/img/esdo-logo.png" alt="" onerror="this.style.display='none'">
                    <div class="doc-org">
                        <div class="org-name">Eso-Social Development Organization (ESDO)</div>
                        <div class="org-addr">Collegepara(Gobindanagar), Thakurgaon-5100, Bangladesh</div>
                        <div class="doc-title">Purchase Requisition</div>
                    </div>
                    <div class="doc-meta">
                        <div class="pr-no">PR NO. ${esc(pr.pr_number)}</div>
                        <div>Date: ${formatDocDate(pr.requisition_date)}</div>
                    </div>
                </div>
                <hr class="doc-rule">

                <div class="doc-meta-row">
                    <div><span>Project:</span> <strong>${esc(pr.project_name ?? '-')}</strong></div>
                    <div><span>Requestor:</span> <strong>${esc(pr.requestor_name ?? '-')}</strong></div>
                    <div><span>Designation:</span> <strong>${esc(pr.requestor_designation ?? '-')}</strong></div>
                </div>

                <table class="doc-table">
                    <thead>
                        <tr>
                            <th style="width:5%;">Sl.No</th>
                            <th style="width:19%;">Item Name</th>
                            <th style="width:23%;">Specification</th>
                            <th style="width:8%;">Unit</th>
                            <th style="width:9%;" class="num">Quantity</th>
                            <th style="width:11%;" class="num">Unit Price</th>
                            <th style="width:12%;" class="num">Total Price</th>
                            <th style="width:13%;">A/C Code/Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsRows}
                        ${blankRows}
                        <tr class="total-row">
                            <td colspan="6" style="text-align:right;">Total Tk</td>
                            <td class="num">${money(pr.total_estimated_amount)}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <div class="doc-inword">In-word : ৳ ${amountInWords(pr.total_estimated_amount)}</div>

                <table class="doc-table">
                    <tr><td style="width:35%;">Delivery Locations:</td><td>${esc(pr.delivery_location ?? '')}</td></tr>
                    <tr><td>Estimated Delivery Date:</td><td>${formatDocDate(pr.estimated_delivery_date)}</td></tr>
                    <tr><td>Estimated Delivery Time:</td><td>${esc(pr.estimated_delivery_time ?? '')}</td></tr>
                    <tr><td colspan="2">Receiver Name: ${esc(pr.receiver_name ?? '')}</td></tr>
                    <tr><td colspan="2">Receiver Contact: ${esc(pr.receiver_contact ?? '')}</td></tr>
                    ${pr.attachment_url ? `<tr><td colspan="2">Attachment: <a href="${pr.attachment_url}" target="_blank" rel="noopener">View / Download</a></td></tr>` : ''}
                </table>

                <table class="doc-table">
                    <tr><td colspan="2" style="font-weight:700;">Budgetary Check: (by Accounts personnel)</td></tr>
                    <tr>
                        <td style="width:60%;">
                            Total allocated Budget : ${budgetCheck ? money(budgetCheck.allocated_budget) : dash}<br><br>
                            Remaining Budget B/F : ${budgetCheck ? money(budgetCheck.remaining_budget_bf) : dash}<br><br>
                            Amount of PR&emsp;&emsp;&emsp;&emsp;&nbsp;: ${money(pr.total_estimated_amount)}<br><br>
                            Remaining Budget C/F: ${budgetCheck ? money(budgetCheck.remaining_budget_cf) : dash}<br><br>
                            Name of Accountant : ${esc(budgetCheck?.checked_by?.name ?? '..................')}
                        </td>
                        <td class="doc-budget-remarks">Remarks${budgetCheck?.remarks ? ': <span class="doc-budget-remarks-text">' + esc(budgetCheck.remarks) + '</span>' : ''}</td>
                    </tr>
                </table>

                <table class="doc-plain">
                    <tr>
                        <td style="width:34%;">Requested by: ${esc(pr.requestor_name ?? dash)}</td>
                        <td style="width:33%;">Designation: ${esc(pr.requestor_designation ?? dash)}</td>
                        <td style="width:33%;">Signature: <span class="sig-line">${dash}</span></td>
                    </tr>
                    <tr><td colspan="3">&nbsp;</td></tr>
                    <tr>
                        <td>Endorsed by: ${esc(endorsedBy?.user?.name ?? dash)}</td>
                        <td>Designation: ${esc(endorsedBy?.user?.designation ?? dash)}</td>
                        <td>Signature: <span class="sig-line">${dash}</span></td>
                    </tr>
                    <tr><td colspan="3">&nbsp;</td></tr>
                    <tr>
                        <td>Finance Requested by: ${esc(financeRequestedBy?.user?.name ?? dash)}</td>
                        <td>Designation: ${esc(financeRequestedBy?.user?.designation ?? dash)}</td>
                        <td>Signature: <span class="sig-line">${dash}</span></td>
                    </tr>
                </table>

                <table class="doc-plain" style="margin-top:1.5rem;">
                    <tr>
                        <td style="width:50%;">
                            Recommend by: ${esc(recommendedBy?.user?.name ?? dash)}<br>
                            PC/DPC/APC/Focal Person
                        </td>
                        <td style="width:50%;">Approved by: ${esc(approvedBy?.user?.name ?? dash)}</td>
                    </tr>
                </table>

                <div class="doc-footer">Eso-Social Development Organization (ESDO) — Procurement Management System</div>
            </div>

            <details class="extra-details">
                <summary>Additional system details (window, category, budget line, plan package)</summary>
                <div class="field-grid">
                    <div class="field"><span class="field-label">Window</span><span class="field-value">${esc(pr.window_type)}</span></div>
                    <div class="field"><span class="field-label">Category</span><span class="field-value">${esc(pr.category?.name ?? '-')}</span></div>
                    <div class="field"><span class="field-label">Budget Line</span><span class="field-value ${pr.budget_line ? '' : 'empty'}">${pr.budget_line ? esc(pr.budget_line.item_code + ' — ' + pr.budget_line.item_name) : 'Not linked yet'}</span></div>
                    <div class="field"><span class="field-label">Annual Plan Package</span><span class="field-value ${pr.package ? '' : 'empty'}">${pr.package ? esc(pr.package.package_number + ' — ' + pr.package.budgeted_head) : 'Not linked'}</span></div>
                    <div class="field"><span class="field-label">Raised By</span><span class="field-value">${esc(pr.raised_by_user?.name ?? pr.raisedBy?.name ?? '-')}</span></div>
                    <div class="field"><span class="field-label">Requisition Date</span><span class="field-value mono">${esc(pr.requisition_date)}</span></div>
                </div>
            </details>

            <div class="pr-section">
                <p class="eyebrow">Item Category / Sub Category / Fixed Asset</p>
                <div class="panel table-wrap">
                    <table class="log-table">
                        <thead><tr><th>Item Category</th><th>Sub Category</th><th>Fixed Asset</th><th>Item Name</th></tr></thead>
                        <tbody>${classificationRows}</tbody>
                    </table>
                </div>
            </div>

            <div class="pr-section">
                <p class="eyebrow">Approval History</p>
                <div class="panel table-wrap">
                    <table class="log-table">
                        <thead><tr><th>Date</th><th>User</th><th>Role</th><th>Action</th><th>Remarks</th></tr></thead>
                        <tbody>${approvalRows}</tbody>
                    </table>
                </div>
            </div>

            <div id="budget-check" class="pr-section"></div>
        `;
    }

    async function renderActionArea(pr) {
        const area = document.getElementById('budget-check');

        if (pr.status === 'approved' || pr.status === 'rejected') {
            area.innerHTML = '';
            return;
        }

        if (isBudgetCheckStage(pr)) {
            await renderBudgetCheckCard(pr, area);
        } else if (isReviewStage(pr)) {
            renderGenericActionCard(area, 'Reviewer', 'Review Decision');
        } else if (isFocalReviewStage(pr)) {
            renderGenericActionCard(area, 'Focal Person', 'Focal Review Decision');
        } else if (isEdApprovalStage(pr)) {
            renderGenericActionCard(area, 'Executive Director', 'ED Approval Decision');
        } else if (isApproverStage(pr)) {
            renderGenericActionCard(area, 'Approver', 'Approval Decision');
        } else {
            // Not this user's turn to act — no action card, just show
            // where the PR currently sits in the chain.
            area.innerHTML = `
                <p class="eyebrow">Status</p>
                <div class="status-note">
                    <span class="tag">${esc(pr.status)}</span>
                    <p>This PR is waiting on the next role in the approval chain.</p>
                </div>
            `;
        }
    }

    async function renderBudgetCheckCard(pr, area) {
        area.innerHTML = `<p class="eyebrow">Budget Verification</p><div class="panel">Loading budget information…</div>`;

        let budgetLines = [];
        let check;
        try {
            const results = await Promise.all([
                api.get(`/purchase-requisitions/${prId}/budget-check`),
                pr.budget_line ? Promise.resolve(null) : api.get('/budget-lines'),
            ]);
            check = results[0];
            if (results[1]) budgetLines = results[1].data;
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
            return;
        }

        const line = check.data.budget_line;
        budgetCheckContext = { line, budgetLines };

        const lineOptions = budgetLines.map(l => `
            <option value="${l.id}">${esc(l.code)} — ${esc(l.name)} (${esc(l.category)}) · balance ৳ ${money(l.balance)}</option>
        `).join('');

        area.innerHTML = `
            <p class="eyebrow">Budget Verification — Module 1.4</p>
            <div class="panel action-panel">
                ${!line ? `
                    <div class="form-field">
                        <label for="budgetLineSelect">Select Budget Line</label>
                        <select id="budgetLineSelect">
                            <option value="">-- Select --</option>
                            ${lineOptions}
                        </select>
                    </div>
                    <div id="livePreview"></div>
                ` : `
                    <div class="field-grid" style="margin-bottom:.5rem;">
                        <div class="field"><span class="field-label">Budget Code</span><span class="field-value mono">${esc(line.code)}</span></div>
                        <div class="field"><span class="field-label">Spent so far</span><span class="field-value">৳ ${money(line.spent)}</span></div>
                    </div>
                    <table class="budget-summary-table">
                        <tr><td class="muted">Total allocated Budget</td><td class="bold">৳ ${money(line.total_allocated_budget)}</td></tr>
                        <tr><td class="muted">Remaining Budget B/F</td><td>৳ ${money(line.remaining_budget_bf)}</td></tr>
                        <tr><td class="muted">Amount of PR</td><td>৳ ${money(line.amount_of_pr)}</td></tr>
                        <tr><td class="muted">Remaining Budget C/F</td><td class="bold" style="color:${line.is_sufficient ? 'var(--green)' : 'var(--red)'}">৳ ${money(line.remaining_budget_cf)}</td></tr>
                        <tr><td class="muted">Name of Accountant</td><td>${esc(check.data.accountant_name)}</td></tr>
                    </table>
                    <div class="decision-hint ${line.is_sufficient ? 'ok' : 'bad'}">
                        ${line.is_sufficient ? 'Sufficient balance' : 'Insufficient balance — C/F would go negative'}
                    </div>
                `}

                <div class="form-field checkbox-field" style="margin-top:1.25rem;">
                    <label><input type="checkbox" id="codeVerified"> Budget code verified</label>
                </div>
                <div class="form-field checkbox-field">
                    <label><input type="checkbox" id="availabilityVerified"> Budget availability confirmed</label>
                </div>

                <div class="form-field">
                    <label for="budgetRemarks">Remarks</label>
                    <textarea id="budgetRemarks" rows="2"></textarea>
                </div>

                <div class="form-field">
                    <label for="decision">Decision</label>
                    <select id="decision">
                        <option value="recommended">Recommend for Approval</option>
                        <option value="approved" ${line && !line.is_sufficient ? 'disabled' : ''}>Approved${line && !line.is_sufficient ? ' (insufficient balance)' : ''}</option>
                        <option value="rejected">Reject</option>
                    </select>
                </div>

                <div class="form-field" id="routeToField">
                    <label for="routeTo">Send to</label>
                    <select id="routeTo">
                        <option value="focal_person">Focal Person</option>
                        <option value="executive_director">Executive Director (ED)</option>
                    </select>
                </div>

                <div class="btn-row">
                    <button class="btn primary" onclick="submitBudgetCheck()">Submit</button>
                </div>
            </div>
        `;

        document.getElementById('decision').addEventListener('change', (e) => {
            const routeField = document.getElementById('routeToField');
            routeField.style.display = e.target.value === 'rejected' ? 'none' : '';
        });

        if (!line) {
            document.getElementById('budgetLineSelect').addEventListener('change', (e) => {
                const id = e.target.value;
                const preview = document.getElementById('livePreview');
                const decisionReturned = document.querySelector('#decision option[value="approved"]');
                const picked = budgetLines.find(l => String(l.id) === id);
                if (!picked) {
                    preview.innerHTML = '';
                    decisionReturned.disabled = false;
                    decisionReturned.textContent = 'Approved';
                    return;
                }
                const cf = picked.balance - Number(pr.total_estimated_amount);
                const sufficient = cf >= 0;
                preview.innerHTML = `
                    <table class="budget-summary-table">
                        <tr><td class="muted">Total allocated Budget</td><td class="bold">৳ ${money(picked.approved_budget)}</td></tr>
                        <tr><td class="muted">Remaining Budget B/F</td><td>৳ ${money(picked.balance)}</td></tr>
                        <tr><td class="muted">Amount of PR</td><td>৳ ${money(pr.total_estimated_amount)}</td></tr>
                        <tr><td class="muted">Remaining Budget C/F</td><td class="bold" style="color:${sufficient ? 'var(--green)' : 'var(--red)'}">৳ ${money(cf)}</td></tr>
                    </table>
                    <div class="decision-hint ${sufficient ? 'ok' : 'bad'}">
                        ${sufficient ? 'Sufficient balance' : 'Insufficient balance — C/F would go negative'}
                    </div>
                `;
                decisionReturned.disabled = !sufficient;
                decisionReturned.textContent = sufficient ? 'Approved' : 'Approved (insufficient balance)';
                if (!sufficient && document.getElementById('decision').value === 'approved') {
                    document.getElementById('decision').value = 'recommended';
                }
            });
        }
    }

    function renderGenericActionCard(area, roleLabel, title) {
        area.innerHTML = `
            <p class="eyebrow">Decision</p>
            <div class="panel action-panel">
                <h3>${title}<span class="role-tag">${roleLabel}</span></h3>
                <div class="form-field">
                    <label for="actionRemarks">Remarks</label>
                    <textarea id="actionRemarks" rows="2"></textarea>
                </div>
                <div class="btn-row">
                    <button class="btn primary" onclick="submitAction('approved', '${roleLabel}')">Approve</button>
                    <button class="btn secondary" onclick="submitAction('returned', '${roleLabel}')">Return</button>
                    <button class="btn danger" onclick="submitAction('rejected', '${roleLabel}')">Reject</button>
                </div>
            </div>
        `;
    }

    async function submitAction(action, roleAtAction) {
        errorBox.style.display = 'none';

        try {
            await api.post(`/purchase-requisitions/${prId}/approvals`, {
                action,
                role_at_action: roleAtAction,
                remarks: document.getElementById('actionRemarks').value || null,
            });
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    async function submitBudgetCheck() {
        errorBox.style.display = 'none';
        const select = document.getElementById('budgetLineSelect');
        const budgetLineId = select ? (select.value || null) : null;

        if (select && !budgetLineId) {
            errorBox.textContent = 'Please select a Budget Line.';
            errorBox.style.display = 'block';
            return;
        }

        const decision = document.getElementById('decision').value;

        // Figures for the Budgetary Check box — pulled from whichever
        // source is on screen (an existing budget line, or the one just
        // picked from the dropdown for a PR with no line attached yet).
        let allocatedBudget = null;
        let remainingBudgetBf = null;
        if (budgetCheckContext.line) {
            allocatedBudget = budgetCheckContext.line.total_allocated_budget;
            remainingBudgetBf = budgetCheckContext.line.remaining_budget_bf;
        } else if (select && select.value) {
            const picked = budgetCheckContext.budgetLines.find(l => String(l.id) === select.value);
            if (picked) {
                allocatedBudget = picked.approved_budget;
                remainingBudgetBf = picked.balance;
            }
        }
        if (allocatedBudget === null || remainingBudgetBf === null) {
            errorBox.textContent = 'Could not determine budget figures — please select a Budget Line.';
            errorBox.style.display = 'block';
            return;
        }

        const payload = {
            budget_line_id: budgetLineId,
            allocated_budget: allocatedBudget,
            remaining_budget_bf: remainingBudgetBf,
            is_budget_code_verified: document.getElementById('codeVerified').checked,
            is_budget_available: document.getElementById('availabilityVerified').checked,
            decision,
            remarks: document.getElementById('budgetRemarks').value || null,
        };
        if (decision !== 'rejected') {
            payload.route_to = document.getElementById('routeTo').value;
        }

        try {
            await api.post(`/purchase-requisitions/${prId}/budget-check`, payload);
            load();
        } catch (err) {
            errorBox.textContent = err.message;
            errorBox.style.display = 'block';
        }
    }

    load();
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\New Poject\Project_procrument\resources\views/purchase-requisitions/show.blade.php ENDPATH**/ ?>