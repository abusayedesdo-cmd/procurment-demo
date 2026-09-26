/**
 * Config for every module driven by the generic resource-ui.js engine.
 * Each key is the URL slug used at /modules/{slug}.
 */
const MODULE_CONFIGS = {

    // ---- B. Procurement Plan ----
    'procurement-plans': {
        title: 'Procurement Plan (auto-generated from approved PR)',
        apiPath: '/procurement-plans',
        listFilterField: 'pr_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'purchase_requisition.pr_number', label: 'PR Number' },
            { key: 'nature', label: 'Nature' },
            { key: 'estimated_amount', label: 'Estimated Amount' },
            { key: 'status', label: 'Status' },
            { key: 'est_delivery_date', label: 'Est. Delivery' },
        ],
        formFields: [
            { name: 'pr_id', label: 'Approved PR', type: 'select', source: '/purchase-requisitions?status=approved', labelField: 'pr_number', required: true },
        ],
    },

    // ---- C. Meetings ----
    // NOTE: /modules/meetings, /modules/meeting-attendances and /modules/meeting-minutes
    // are retired route-side (see routes/web.php) — the case-based flow
    // (Cases -> a case -> 1st/2nd meeting, via MeetingController) replaced these
    // generic pages. Kept here in sync with the real schema in case the generic
    // engine is reused elsewhere (e.g. the Data Manager).
    'meetings': {
        title: 'Meeting (1st/2nd)',
        apiPath: '/meetings',
        listColumns: [
            { key: 'procurement_case.ref', label: 'Case' },
            { key: 'meeting_type', label: 'Type' },
            { key: 'meeting_date', label: 'Date' },
            { key: 'notice_number', label: 'Notice #' },
            { key: 'held_at', label: 'Held At' },
        ],
        formFields: [
            { name: 'procurement_case_id', label: 'Procurement Case', type: 'select', source: '/procurement-cases', labelField: 'ref', required: true },
            { name: 'meeting_type', label: 'Meeting Type', type: 'enum', options: ['first', 'second'], required: true },
            { name: 'rezulation_no', label: 'Resolution No.', type: 'text' },
            { name: 'location', label: 'Location', type: 'text' },
            { name: 'meeting_date', label: 'Meeting Date', type: 'date', required: true },
            { name: 'meeting_time', label: 'Meeting Time', type: 'text' },
            { name: 'notice_number', label: 'Notice Number', type: 'text' },
            { name: 'notice_date', label: 'Notice Date', type: 'date' },
            { name: 'notice_file', label: 'Notice File (path/URL)', type: 'file' },
            { name: 'attendance_number', label: 'Attendance Number', type: 'text' },
            { name: 'agenda', label: 'Agenda', type: 'textarea' },
            { name: 'publish_date', label: 'Publish Date', type: 'date' },
            { name: 'closing_date', label: 'Closing Date', type: 'date' },
            { name: 'opening_date', label: 'Opening Date', type: 'date' },
            { name: 'schedule_override_reason', label: 'Schedule Override Reason', type: 'textarea' },
            { name: 'decisions', label: 'Decisions', type: 'textarea' },
            { name: 'attendance_file', label: 'Attendance File (path/URL)', type: 'file' },
            { name: 'minutes_file', label: 'Minutes File (path/URL)', type: 'file' },
            { name: 'held_at', label: 'Held At', type: 'datetime' },
            { name: 'recorded_by', label: '', type: 'currentUser' },
        ],
    },

    'meeting-attendances': {
        title: 'Meeting Attendance',
        apiPath: '/meeting-attendances',
        listColumns: [
            { key: 'meeting.notice_number', label: 'Meeting' },
            { key: 'name', label: 'Name' },
            { key: 'designation', label: 'Designation' },
            { key: 'present', label: 'Present' },
        ],
        formFields: [
            { name: 'meeting_id', label: 'Meeting', type: 'select', source: '/meetings', labelField: r => r.notice_number || `#${r.id}`, required: true },
            { name: 'committee_member_id', label: 'Committee Member', type: 'select', source: '/procurement-committee-members', labelField: 'name', required: true },
            { name: 'name', label: 'Name (snapshot)', type: 'text', required: true },
            { name: 'designation', label: 'Designation (snapshot)', type: 'text', required: true },
            { name: 'present', label: 'Present', type: 'checkbox' },
            { name: 'signature_file', label: 'Signature File (path)', type: 'file' },
            { name: 'remarks', label: 'Remarks', type: 'text' },
            { name: 'sort_order', label: 'Sort Order', type: 'number' },
        ],
    },

    // No REST API exists for meeting-minutes (no apiResource route) — minutes/
    // resolution data now lives on the Meeting record itself (rezulation_no,
    // decisions, minutes_file). This entry has no working backend; consider
    // dropping it from MODULE_GROUPS below rather than fixing its fields.
    'meeting-minutes': {
        title: 'Meeting Minutes / Resolution',
        apiPath: null, // no apiResource route in routes/api.php — page will not function
        listColumns: [
            { key: 'meeting.notice_number', label: 'Meeting' },
            { key: 'meeting.rezulation_no', label: 'Resolution #' },
        ],
        formFields: [
            { name: 'meeting_id', label: 'Meeting', type: 'select', source: '/meetings', labelField: r => r.notice_number || `#${r.id}`, required: true },
            { name: 'resolution_text', label: 'Resolution', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'committee-members': {
        title: 'Committee Members',
        apiPath: '/committee-members',
        // ESDO Procurement Policy §9 — committee membership is formed by
        // Super Admin only. Everyone can still see who's on a committee;
        // only Admin gets the "Add New" form.
        adminOnly: true,
        adminOnlyNote: 'Committee membership is managed by the Super Admin. Ask an Admin to add or change members.',
        listColumns: [
            { key: 'committee.name', label: 'Committee' },
            { key: 'user.name', label: 'User' },
            { key: 'designation_in_committee', label: 'Designation' },
        ],
        formFields: [
            { name: 'committee_id', label: 'Committee', type: 'select', source: '/purchase-committees', labelField: 'name', required: true },
            { name: 'user_id', label: 'User', type: 'select', source: '/users', labelField: 'name', required: true },
            { name: 'designation_in_committee', label: 'Designation', type: 'text' },
        ],
    },

    'purchase-committees': {
        title: 'Committees',
        apiPath: '/purchase-committees',
        // ESDO Procurement Policy §9 — only the Super Admin forms
        // committees (main or sub). Procurement Officers still need to
        // read this list (e.g. to pick a committee on Sub-Committee
        // Transfer), so only the create/edit/delete form is hidden.
        adminOnly: true,
        adminOnlyNote: 'Committees are formed by the Super Admin. Ask an Admin to create or change a committee.',
        listColumns: [
            { key: 'name', label: 'Name' },
            { key: 'address', label: 'Address' },
            { key: 'type', label: 'Type' },
            { key: 'project.name', label: 'Project' },
            { key: 'parent_committee.name', label: 'Parent Committee' },
        ],
        formFields: [
            { name: 'name', label: 'Committee Name', type: 'text', required: true },
            { name: 'address', label: 'Address', type: 'text' },
            { name: 'type', label: 'Type', type: 'enum', options: ['main', 'sub'], required: true },
            // Policy §9: sub-committees are formed for a specific project;
            // the main/central committee is organization-wide and has no
            // project. Required only when Type = sub (server enforces this
            // too — see PurchaseCommitteeController::rulesFor()).
            { name: 'project_id', label: 'Project (required for Sub-committee)', type: 'select', source: '/projects', labelField: 'name' },
            { name: 'parent_committee_id', label: 'Parent Committee', type: 'select', source: '/purchase-committees', labelField: 'name' },
        ],
    },

    'sub-committee-transfers': {
        title: 'Sub-Committee Transfer',
        apiPath: '/sub-committee-transfers',
        listFilterField: 'procurement_plan_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'from_committee.name', label: 'From' },
            { key: 'to_committee.name', label: 'To' },
            { key: 'transfer_date', label: 'Date' },
        ],
        formFields: [
            { name: 'procurement_plan_id', label: 'Procurement Plan', type: 'select', source: '/procurement-plans', labelField: r => r.purchase_requisition?.pr_number ?? `#${r.id}`, required: true },
            { name: 'from_committee_id', label: 'From Committee', type: 'select', source: '/purchase-committees', labelField: 'name', required: true },
            { name: 'to_committee_id', label: 'To Committee', type: 'select', source: '/purchase-committees', labelField: 'name', required: true, createSubCommittee: true },
            { name: 'transfer_date', label: 'Transfer Date', type: 'date', required: true },
            { name: 'transfer_note', label: 'Note', type: 'textarea' },
        ],
    },

    // ---- C. RFQ / Tender ----
    'rfqs': {
        title: 'RFQ / OTM',
        apiPath: '/rfqs',
        listFilterField: 'procurement_case_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'rfq_number', label: 'RFQ #' },
            { key: 'type', label: 'Type' },
            { key: 'distribution_process', label: 'Distribution' },
            { key: 'issue_date', label: 'Issue Date' },
            { key: 'closing_date', label: 'Closing Date' },
            { key: 'status', label: 'Status' },
        ],
        rowActions: [
            { label: 'Manage Items', hrefBuilder: r => `/modules/rfq-items?new=1&field_rfq_id=${r.id}&context_label=${encodeURIComponent('RFQ ' + r.rfq_number)}` },
            { label: 'Preview RFQ', hrefBuilder: r => `/api/rfqs/${r.id}/preview` },
            { label: 'Download RFQ (Word)', hrefBuilder: r => `/api/rfqs/${r.id}/document`, download: true },
            { label: 'Download RFQ (PDF)', hrefBuilder: r => `/api/rfqs/${r.id}/pdf`, download: true },
            { label: 'Preview Schedule', hrefBuilder: r => `/api/rfqs/${r.id}/tender-schedule-preview` },
            { label: 'Download Schedule', hrefBuilder: r => `/api/rfqs/${r.id}/tender-schedule-document`, download: true },
            { label: 'Copy Vendor Link', copyBuilder: r => r.public_token ? `${window.location.origin}/vendor-portal/${r.public_token}` : null },
            { label: 'Finalize RFQ', request: r => r.status === 'finalized' ? null : ({
                path: `/rfqs/${r.id}/finalize`,
                confirm: `Finalize RFQ ${r.rfq_number}? Once finalized it can no longer be edited.`,
            }) },
        ],
        formFields: [
            { name: 'procurement_case_id', label: 'Procurement Case', type: 'select', source: '/procurement-cases', labelField: 'ref', required: true },
            { name: 'subject', label: 'Subject', type: 'text', required: true, autofillFrom: { field: 'procurement_case_id', property: 'project_name_hint' } },
            { name: 'type', label: 'Type', type: 'enum', options: ['RFQ', 'OTM', 'RFP'], required: true, autofillFrom: { field: 'procurement_case_id', property: 'rfq_type_hint' } },
            { name: 'distribution_process', label: 'Distribution Process', type: 'enum', options: ['Email', 'Hand Distribution'] },
            { name: 'issue_date', label: 'Issue Date', type: 'date', required: true, autofillFrom: { field: 'procurement_case_id', property: 'issue_date_hint' } },
            { name: 'closing_date', label: 'Closing Date (RFQ: 5–7 days; RFP: 3–7 days after issue date)', type: 'date', required: true, autofillFrom: { field: 'procurement_case_id', property: 'closing_date_hint' } },
            { name: 'terms_condition_ids', label: 'Select Terms & Conditions', type: 'multi_checkbox', source: '/rfq-terms-conditions?active=1', labelField: 'text' },
            { name: 'terms_conditions', label: 'Additional Terms (optional, free text)', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    // Step 7 "Select Terms & Conditions" — editable master list the RFQ
    // form's checkbox picker reads from. Index is open to everyone (needed
    // to populate the RFQ create form); Add/Edit/Deactivate/Delete are
    // enforced server-side as Admin/Procurement Officer only.
    'rfq-terms-conditions': {
        title: 'RFQ Terms & Conditions (Manage List)',
        apiPath: '/rfq-terms-conditions',
        listColumns: [
            { key: 'sort_order', label: 'Order' },
            { key: 'text', label: 'Text' },
            { key: 'active', label: 'Active' },
        ],
        rowActions: [
            { label: 'Edit Text', request: r => ({
                path: `/rfq-terms-conditions/${r.id}`,
                method: 'put',
                prompt: { message: 'Edit this Terms & Conditions line:', field: 'text', default: r.text },
            }) },
            { label: 'Deactivate', request: r => r.active ? ({
                path: `/rfq-terms-conditions/${r.id}`,
                method: 'put',
                body: { active: false },
                confirm: 'Deactivate this line? It will stop appearing as a choice on NEW RFQs.',
            }) : null },
            { label: 'Activate', request: r => r.active ? null : ({
                path: `/rfq-terms-conditions/${r.id}`,
                method: 'put',
                body: { active: true },
            }) },
            { label: 'Delete', request: r => ({
                path: `/rfq-terms-conditions/${r.id}`,
                method: 'delete',
                confirm: 'Delete this line entirely? It will also disappear from any older RFQ that had selected it — use Deactivate instead if you just want to hide it for new RFQs.',
            }) },
        ],
        formFields: [
            { name: 'text', label: 'Terms & Conditions Text', type: 'textarea', required: true },
            { name: 'sort_order', label: 'Sort Order (lower shows first)', type: 'number' },
        ],
    },

    'rfq-items': {
        title: 'RFQ Items (Rate Schedule)',
        apiPath: '/rfq-items',
        listFilterField: 'rfq_id',
        listColumns: [
            { key: 'scheme_name', label: 'Scheme' },
            { key: 'serial_no', label: 'SL' },
            { key: 'category', label: 'Category' },
            { key: 'description', label: 'Description' },
            { key: 'quantity', label: 'Qty' },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: '_pr_item_picker', label: 'Pick from PR Item (ঐচ্ছিক — নিচেরগুলো অটো-ফিল করবে)', type: 'pr_item_picker' },
            { name: 'scheme_name', label: 'Scheme / Shelter Name (একাধিক sub-BOQ থাকলে)', type: 'text' },
            { name: 'category', label: 'Category (optional sub-heading)', type: 'text' },
            { name: 'serial_no', label: 'SL No.', type: 'number', required: true },
            { name: 'description', label: 'Description', type: 'textarea', required: true },
            { name: 'quantity', label: 'Quantity', type: 'number', required: true },
            { name: 'unit_id', label: 'Unit', type: 'select', source: '/units', labelField: 'name' },
            { name: 'delivery_address', label: 'Delivery Address (optional)', type: 'textarea' },
        ],
    },

    'cash-purchases': {
        title: 'Cash Purchase (Direct)',
        apiPath: '/cash-purchases',
        listFilterField: 'pr_id',
        listColumns: [
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'item_description', label: 'Item' },
            { key: 'amount', label: 'Amount' },
            { key: 'purchase_date', label: 'Date' },
        ],
        formFields: [
            { name: 'pr_id', label: 'PR', type: 'select', source: '/purchase-requisitions', labelField: 'pr_number', required: true },
            { name: 'committee_id', label: 'Committee', type: 'select', source: '/purchase-committees', labelField: 'name' },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'item_description', label: 'Item / Purpose', type: 'textarea', required: true },
            { name: 'amount', label: 'Amount', type: 'number', step: '0.01', required: true, autofillFrom: { field: 'pr_id', property: 'total_estimated_amount' } },
            { name: 'purchase_date', label: 'Purchase Date', type: 'date', required: true },
            { name: 'receipt_file', label: 'Receipt/Bill (path/URL)', type: 'file' },
            { name: 'notes', label: 'Notes', type: 'textarea' },
        ],
    },

    'tender-schedules': {
        title: 'Tender Schedule (Goods/Works)',
        apiPath: '/tender-schedules',
        listFilterField: 'rfq_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'category', label: 'Category' },
        ],
        rowActions: [
            { label: 'Preview', hrefBuilder: r => `/api/rfqs/${r.rfq_id}/tender-schedule-preview` },
            { label: 'Download', hrefBuilder: r => `/api/rfqs/${r.rfq_id}/tender-schedule-document`, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Goods', 'Works'], required: true },
            { name: 'schedule_details', label: 'Details', type: 'textarea' },
            { name: 'validity_days', label: 'Validity (days)', type: 'number' },
            { name: 'performance_security_percent', label: 'Performance Security %', type: 'number', step: '0.01' },
            { name: 'delay_penalty_percent', label: 'Delay Penalty %', type: 'number', step: '0.01' },
            { name: 'payment_terms', label: 'Payment Terms', type: 'textarea' },
            { name: 'award_type', label: 'Award Type', type: 'text' },
            { name: 'contract_type', label: 'Contract Type', type: 'text' },
            { name: 'technical_weight', label: 'Technical Weight', type: 'number', step: '0.01' },
            { name: 'financial_weight', label: 'Financial Weight', type: 'number', step: '0.01' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'tender-proposals': {
        title: 'Tender Proposal (Professional Service)',
        apiPath: '/tender-proposals',
        listFilterField: 'rfq_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'proposal_details', label: 'Details', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'tender-advertisements': {
        title: 'Tender Advertisement',
        apiPath: '/tender-advertisements',
        listFilterField: 'rfq_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'medium', label: 'Medium' },
            { key: 'category', label: 'Category' },
            { key: 'publish_date', label: 'Publish Date' },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'medium', label: 'Medium', type: 'enum', options: ['BD Jobs', 'National Newspaper', 'Local Newspaper'], required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Goods', 'Works', 'Service'], required: true },
            { name: 'publish_date', label: 'Publish Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    // ---- Vendors & Quotations ----
    'vendors': {
        title: 'Vendors',
        apiPath: '/vendors',
        listColumns: [
            { key: 'name', label: 'Name' },
            { key: 'contact_person', label: 'Contact' },
            { key: 'phone', label: 'Phone' },
            { key: 'trade_license_no', label: 'Trade License' },
        ],
        formFields: [
            { name: 'name', label: 'Vendor Name', type: 'text', required: true },
            { name: 'address', label: 'Address', type: 'text' },
            { name: 'contact_person', label: 'Contact Person', type: 'text' },
            { name: 'email', label: 'Email', type: 'text' },
            { name: 'phone', label: 'Phone', type: 'text' },
            { name: 'trade_license_no', label: 'Trade License No.', type: 'text' },
            { name: 'vat_reg_no', label: 'VAT Reg No.', type: 'text' },
            { name: 'tax_id', label: 'Tax ID', type: 'text' },
        ],
    },

    'quotations': {
        title: 'Quotations Received',
        apiPath: '/quotations',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'quoted_amount', label: 'Amount' },
            { key: 'status', label: 'Status' },
            { key: 'submitted_via_portal', label: 'Vendor Portal?' },
        ],
        rowActions: [
            { label: 'View Submission', hrefBuilder: r => `/api/quotations/${r.id}/submission-preview` },
            { label: 'Preview', hrefBuilder: r => r.file_path || null },
            { label: 'Download', hrefBuilder: r => r.file_path || null, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'submitted_at', label: 'Submitted At', type: 'datetime', required: true },
            { name: 'quoted_amount', label: 'Quoted Amount', type: 'number', step: '0.01', required: true },
            { name: 'status', label: 'Status', type: 'enum', options: ['received', 'opened', 'evaluated', 'disqualified', 'forwarded', 'rejected'] },
            { name: 'representative_name', label: 'Representative Name', type: 'text' },
            { name: 'representative_contact', label: 'Representative Contact', type: 'text' },
            { name: 'attended', label: 'Attended', type: 'checkbox' },
            { name: 'trade_license_submitted', label: 'Trade License Submitted', type: 'checkbox' },
            { name: 'tin_submitted', label: 'TIN Submitted', type: 'checkbox' },
            { name: 'bin_submitted', label: 'BIN Submitted', type: 'checkbox' },
            { name: 'opening_remarks', label: 'Opening Remarks', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
            // Vendor-portal submission fields (self-service quotation drop —
            // see VendorPortalController). Shown here mainly for staff
            // reference/editing; a fuller read-only view is the "View
            // Submission" row action above.
            { name: 'general_experience', label: 'General Experience', type: 'textarea' },
            { name: 'relevant_experience', label: 'Relevant Experience', type: 'textarea' },
            { name: 'terms_accepted', label: 'Terms & Conditions Accepted', type: 'checkbox' },
            { name: 'delivery_terms_accepted', label: 'Delivery Terms Accepted', type: 'checkbox' },
            { name: 'submitted_via_portal', label: 'Submitted via Vendor Portal', type: 'checkbox' },
            // Earnest Money (ESDO Procurement Policy §24) — required for
            // enlistment/OTM purchases; tracked per bidder since each vendor's
            // EM is refunded, forfeited, or (if they win) converted to a
            // Security Deposit on the Contract Award independently.
            { name: 'earnest_money_required', label: 'Earnest Money Required', type: 'checkbox' },
            { name: 'earnest_money_amount', label: 'Earnest Money Amount', type: 'number', step: '0.01' },
            { name: 'earnest_money_status', label: 'Earnest Money Status', type: 'enum', options: ['not_required', 'held', 'refunded', 'forfeited'] },
            { name: 'earnest_money_notes', label: 'Earnest Money Notes', type: 'textarea' },
        ],
    },

    'tender-openings': {
        title: 'Tender Opening Report',
        apiPath: '/tender-openings',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'opening_date', label: 'Opening Date' },
        ],
        rowActions: [
            { label: 'Download', hrefBuilder: r => `/api/tender-openings/${r.id}/document` },
            { label: 'Forward All for Evaluation', request: r => ({
                path: `/rfqs/${r.rfq_id}/quotations/forward-for-evaluation`,
                confirm: 'Forward ALL pending quotations of this RFQ for evaluation?',
            }) },
            { label: 'Reject All Quotations', request: r => ({
                path: `/rfqs/${r.rfq_id}/quotations/reject`,
                askReason: 'Reason for rejecting ALL pending quotations of this RFQ:',
            }) },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'opening_date', label: 'Opening Date', type: 'date', required: true },
            { name: 'venue', label: 'Venue', type: 'text' },
            { name: 'opening_time', label: 'Opening Time', type: 'text' },
            { name: 'opened_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'file' },
            { name: 'remarks', label: 'Remarks', type: 'textarea' },
        ],
    },

    // Step 11 — per-vendor review of the opened quotations: Forward for
    // Evaluation or Reject. Read-only list (quotations themselves are
    // recorded in step 10); the buttons only show while a quotation is still
    // pending review.
    'opening-quotation-review': {
        title: 'Quotation Review — Forward for Evaluation / Reject',
        apiPath: '/quotations',
        listFilterField: 'rfq_id',
        readOnly: true,
        readOnlyNote: 'Review each vendor\'s quotation after the opening: forward it for evaluation or reject it (a reason is required). To act on every pending quotation at once, use the buttons on the Tender Opening Report.',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'quoted_amount', label: 'Amount' },
            { key: 'status', label: 'Status' },
            { key: 'rejection_reason', label: 'Rejection Reason' },
        ],
        rowActions: [
            { label: 'View Submission', hrefBuilder: r => `/api/quotations/${r.id}/submission-preview` },
            { label: 'Forward for Evaluation', request: r => ['received', 'opened'].includes(r.status) ? ({
                path: `/rfqs/${r.rfq_id}/quotations/forward-for-evaluation`,
                body: { quotation_ids: [r.id] },
                confirm: `Forward ${r.vendor?.name ?? 'this vendor'}'s quotation for evaluation?`,
            }) : null },
            { label: 'Reject Quotation', request: r => ['received', 'opened'].includes(r.status) ? ({
                path: `/rfqs/${r.rfq_id}/quotations/reject`,
                body: { quotation_ids: [r.id] },
                askReason: `Reason for rejecting ${r.vendor?.name ?? 'this vendor'}'s quotation:`,
            }) : null },
        ],
        formFields: [],
    },

    // ---- Evaluation ----
    'eligibility-reports': {
        title: 'Eligibility Report (ER)',
        apiPath: '/eligibility-reports',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'prepared_by.name', label: 'Prepared By' },
        ],
        rowActions: [
            { label: 'Preview', hrefBuilder: r => `/api/eligibility-reports/${r.id}/preview` },
            { label: 'Download', hrefBuilder: r => `/api/eligibility-reports/${r.id}/document`, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'file' },
        ],
    },

    'eligibility-report-items': {
        title: 'Eligibility Report — Vendor Result',
        apiPath: '/eligibility-report-items',
        listColumns: [
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'eligible', label: 'Eligible' },
        ],
        formFields: [
            { name: 'eligibility_report_id', label: 'Eligibility Report', type: 'select', source: '/eligibility-reports', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'quotation_id', label: 'Vendor Quotation (auto-fills checks below)', type: 'select', source: '/quotations', labelField: r => `${r.vendor?.name ?? '-'} — ${r.rfq?.rfq_number ?? ''}` },
            { name: 'trade_license_verified', label: 'Trade License', type: 'checkbox' },
            { name: 'tin_verified', label: 'TIN Certificate', type: 'checkbox' },
            { name: 'bin_verified', label: 'BIN Certificate', type: 'checkbox' },
            { name: 'psr_verified', label: 'PSR (Tax Return Submission)', type: 'checkbox' },
            { name: 'remarks', label: 'Remarks', type: 'textarea' },
        ],
    },

    'technical-evaluation-reports': {
        title: 'Technical Evaluation Report (TER)',
        apiPath: '/technical-evaluation-reports',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'prepared_by.name', label: 'Prepared By' },
        ],
        rowActions: [
            { label: 'Preview', hrefBuilder: r => `/api/technical-evaluation-reports/${r.id}/preview` },
            { label: 'Download', hrefBuilder: r => `/api/technical-evaluation-reports/${r.id}/document`, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'file' },
        ],
    },

    'technical-evaluation-items': {
        title: 'Technical Evaluation — Vendor Score',
        apiPath: '/technical-evaluation-items',
        listColumns: [
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'score', label: 'Score' },
        ],
        formFields: [
            { name: 'ter_id', label: 'Technical Evaluation Report', type: 'select', source: '/technical-evaluation-reports', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
           
            { name: 'remarks', label: 'Remarks', type: 'textarea' },
        ],
    },

    'technical-evaluation-criteria': {
        title: 'Technical Evaluation Criteria',
        apiPath: '/technical-evaluation-criteria',
        listFilterField: 'ter_id',
        listColumns: [
            { key: 'name', label: 'Criterion' },
            { key: 'max_marks', label: 'Max Marks' },
            { key: 'sort_order', label: 'Order' },
        ],
        formFields: [
            { name: 'ter_id', label: 'Technical Evaluation Report', type: 'select', source: '/technical-evaluation-reports', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'name', label: 'Criterion Name', type: 'text', required: true },
            { name: 'max_marks', label: 'Max Marks', type: 'number', step: '0.01', required: true },
            { name: 'sort_order', label: 'Sort Order', type: 'number' },
        ],
    },

    'technical-evaluation-scores': {
        title: 'Technical Evaluation — Criteria Score',
        apiPath: '/technical-evaluation-scores',
        listFilterField: 'technical_evaluation_item_id',
        listColumns: [
            { key: 'item.vendor.name', label: 'Vendor' },
            { key: 'criterion.name', label: 'Criterion' },
            { key: 'score', label: 'Score' },
        ],
        formFields: [
            { name: 'technical_evaluation_item_id', label: 'Vendor (Technical Evaluation Row)', type: 'select', source: '/technical-evaluation-items', labelField: r => r.vendor?.name ?? `#${r.id}`, required: true },
            { name: 'criterion_id', label: 'Criterion', type: 'select', source: '/technical-evaluation-criteria', labelField: r => `${r.name} (Max ${r.max_marks})`, required: true },
            { name: 'score', label: 'Score', type: 'number', step: '0.01', required: true },
        ],
    },

    'financial-evaluation-reports': {
        title: 'Financial Evaluation Report (FER)',
        apiPath: '/financial-evaluation-reports',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'prepared_by.name', label: 'Prepared By' },
        ],
        rowActions: [
            { label: 'Preview', hrefBuilder: r => `/api/financial-evaluation-reports/${r.id}/preview` },
            { label: 'Download', hrefBuilder: r => `/api/financial-evaluation-reports/${r.id}/document`, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'file' },
        ],
    },

    'financial-evaluation-items': {
        title: 'Financial Evaluation — Vendor Amount',
        apiPath: '/financial-evaluation-items',
        listColumns: [
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'quoted_amount', label: 'Amount' },
            { key: 'financial_marks', label: 'Financial Marks' },
        ],
        formFields: [
            { name: 'fer_id', label: 'Financial Evaluation Report', type: 'select', source: '/financial-evaluation-reports', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'quotation_id', label: 'Vendor Quotation (auto-fills Amount)', type: 'select', source: '/quotations', labelField: r => `${r.vendor?.name ?? '-'} — ${r.rfq?.rfq_number ?? ''}` },
            { name: 'quoted_amount', label: 'Amount', type: 'number', step: '0.01', required: true, autofillFrom: { field: 'quotation_id', property: 'quoted_amount' } },
            { name: 'remarks', label: 'Remarks', type: 'textarea' },
        ],
    },

    'comparative-statements': {
        title: 'Comparative Statement (CS)',
        apiPath: '/comparative-statements',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'lowest_evaluated_vendor.name', label: 'Lowest Evaluated Vendor' },
        ],
        rowActions: [
            { label: 'Preview', hrefBuilder: r => `/api/comparative-statements/${r.id}/preview` },
            { label: 'Download', hrefBuilder: r => `/api/comparative-statements/${r.id}/document`, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'lowest_evaluated_vendor_id', label: 'Lowest Evaluated Vendor', type: 'select', source: '/vendors', labelField: 'name' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'comparative-statement-items': {
        title: 'Comparative Statement — Vendor Ranking',
        apiPath: '/comparative-statement-items',
        listColumns: [
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'financial_marks', label: 'Financial Marks' },
            { key: 'technical_marks', label: 'Technical Marks' },
            { key: 'total_marks', label: 'Total Marks' },
            { key: 'rank', label: 'Rank' },
            { key: 'amount', label: 'Amount' },
        ],
        formFields: [
            { name: 'comparative_statement_id', label: 'Comparative Statement', type: 'select', source: '/comparative-statements', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'financial_evaluation_item_id', label: 'Financial Evaluation Row', type: 'select', source: '/financial-evaluation-items', labelField: r => `${r.vendor?.name ?? '-'} — Amt ${r.quoted_amount}` },
            { name: 'technical_evaluation_item_id', label: 'Technical Evaluation Row', type: 'select', source: '/technical-evaluation-items', labelField: r => `${r.vendor?.name ?? '-'} — Score ${r.score}` },
            { name: 'amount', label: 'Amount', type: 'number', step: '0.01', required: true, autofillFrom: { field: 'financial_evaluation_item_id', property: 'quoted_amount' } },
        ],
    },

    // ---- Award & Contract ----
    'contract-awards': {
        title: 'Notification of Contract Award (NOA)',
        apiPath: '/contract-awards',
        listFilterField: 'procurement_plan_id',
        splitLogAndForm: true,
        listColumns: [
            { key: 'noa_number', label: 'NOA #' },
            { key: 'category', label: 'Category' },
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'noa_date', label: 'Date' },
        ],
        formFields: [
            { name: 'procurement_plan_id', label: 'Procurement Plan', type: 'select', source: '/procurement-plans', labelField: r => r.purchase_requisition?.pr_number ?? `#${r.id}`, required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Work', 'Goods', 'Service'], required: true },
            { name: 'vendor_id', label: 'Awarded Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'noa_number', label: 'NOA Number', type: 'text', required: true },
            { name: 'noa_date', label: 'NOA Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
            // Security Deposit (ESDO Procurement Policy §23) — the winning
            // bidder's Earnest Money converted and held until the warranty
            // period ends; rate is set by the Project Coordinator per the
            // nature/volume of the award (tender documents call this
            // "Performance Security", e.g. 2% held for 90 days).
            { name: 'security_deposit_required', label: 'Security Deposit Required', type: 'checkbox' },
            { name: 'security_deposit_amount', label: 'Security Deposit Amount', type: 'number', step: '0.01' },
            { name: 'security_deposit_percentage', label: 'Security Deposit %', type: 'number', step: '0.01' },
            { name: 'security_deposit_status', label: 'Security Deposit Status', type: 'enum', options: ['held', 'returned', 'waived'] },
            { name: 'warranty_period_ends_at', label: 'Warranty Period Ends', type: 'date' },
            { name: 'security_deposit_returned_at', label: 'Security Deposit Returned On', type: 'date' },
        ],
    },

    'pay-orders': {
        title: 'Pay Order',
        apiPath: '/pay-orders',
        listColumns: [
            { key: 'contract_award.noa_number', label: 'NOA' },
            { key: 'awarded_amount', label: 'Awarded' },
            { key: 'pay_order_amount', label: 'Pay Order' },
            { key: 'received_amount', label: 'Received' },
        ],
        formFields: [
            { name: 'contract_award_id', label: 'Contract Award', type: 'select', source: '/contract-awards', labelField: 'noa_number', required: true },
            { name: 'awarded_amount', label: 'Awarded Amount', type: 'number', step: '0.01', required: true },
            { name: 'pay_order_amount', label: 'Pay Order Amount', type: 'number', step: '0.01', required: true },
            { name: 'received_amount', label: 'Received Amount', type: 'number', step: '0.01' },
            { name: 'received_date', label: 'Received Date', type: 'date' },
            { name: 'calculation_details', label: 'Calculation Details', type: 'textarea' },
        ],
    },

    'contract-agreements': {
        title: 'Contract Agreement',
        apiPath: '/contract-agreements',
        listColumns: [
            { key: 'agreement_number', label: 'Agreement #' },
            { key: 'category', label: 'Category' },
            { key: 'agreement_date', label: 'Date' },
        ],
        formFields: [
            { name: 'contract_award_id', label: 'Contract Award', type: 'select', source: '/contract-awards', labelField: 'noa_number', required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Work', 'Goods', 'Service'], required: true },
            { name: 'agreement_number', label: 'Agreement Number', type: 'text', required: true },
            { name: 'agreement_date', label: 'Agreement Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'work-orders': {
        title: 'Work Order',
        apiPath: '/work-orders',
        listColumns: [
            { key: 'wo_number', label: 'WO #' },
            { key: 'category', label: 'Category' },
            { key: 'wo_date', label: 'Date' },
        ],
        formFields: [
            { name: 'contract_agreement_id', label: 'Contract Agreement', type: 'select', source: '/contract-agreements', labelField: 'agreement_number', required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Work', 'Goods', 'Service'], required: true },
            { name: 'wo_number', label: 'WO Number', type: 'text', required: true },
            { name: 'wo_date', label: 'WO Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'delivery-receipts': {
        title: 'Delivery Received',
        apiPath: '/delivery-receipts',
        listColumns: [
            { key: 'work_order.wo_number', label: 'WO' },
            { key: 'category', label: 'Category' },
            { key: 'delivery_date', label: 'Delivery Date' },
        ],
        formFields: [
            { name: 'work_order_id', label: 'Work Order', type: 'select', source: '/work-orders', labelField: 'wo_number', required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Work', 'Goods', 'Service'], required: true },
            { name: 'delivery_date', label: 'Delivery Date', type: 'date', required: true },
            { name: 'received_by', label: '', type: 'currentUser' },
            { name: 'remarks', label: 'Remarks', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    // ---- D. Framework Agreement ----
    'framework-agreements': {
        title: 'Framework Agreement',
        apiPath: '/framework-agreements',
        listColumns: [
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'category.name', label: 'Category' },
            { key: 'start_date', label: 'Start' },
            { key: 'end_date', label: 'End' },
        ],
        formFields: [
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'category_id', label: 'Category', type: 'select', source: '/procurement-categories', labelField: 'name', required: true },
            { name: 'start_date', label: 'Start Date', type: 'date', required: true },
            { name: 'end_date', label: 'End Date', type: 'date', required: true },
            { name: 'terms', label: 'Terms', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    // ---- E. Sole Sourcing ----
    'sole-sourcing-requests': {
        title: 'Sole Sourcing Request',
        apiPath: '/sole-sourcing-requests',
        listColumns: [
            { key: 'purchase_requisition.pr_number', label: 'PR' },
            { key: 'vendor.name', label: 'Vendor' },
            { key: 'approval_date', label: 'Approval Date' },
        ],
        formFields: [
            { name: 'pr_id', label: 'PR', type: 'select', source: '/purchase-requisitions', labelField: 'pr_number', required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'justification', label: 'Justification', type: 'textarea' },
            { name: 'approved_by', label: 'Approved By', type: 'select', source: '/users', labelField: 'name' },
            { name: 'approval_date', label: 'Approval Date', type: 'date' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },
};

/** Grouped for the /modules hub page nav, matching the document sections. */
const MODULE_GROUPS = [
    { title: 'B. Procurement Plan', slugs: ['procurement-plans'] },
    { title: 'C. Meetings & Committee', slugs: ['purchase-committees', 'committee-members', 'meetings', 'meeting-attendances', 'meeting-minutes', 'sub-committee-transfers'] },
    { title: 'C. RFQ / Tender', slugs: ['rfqs', 'rfq-items', 'tender-schedules', 'tender-proposals', 'tender-advertisements'] },
    { title: 'Vendors & Quotations', slugs: ['vendors', 'quotations', 'tender-openings'] },
    { title: 'C. Evaluation', slugs: ['eligibility-reports', 'eligibility-report-items', 'technical-evaluation-reports', 'technical-evaluation-items', 'technical-evaluation-criteria', 'technical-evaluation-scores', 'financial-evaluation-reports', 'financial-evaluation-items', 'comparative-statements', 'comparative-statement-items'] },
    { title: 'C. Award & Contract', slugs: ['contract-awards', 'pay-orders', 'contract-agreements', 'work-orders', 'delivery-receipts'] },
    { title: 'D. Framework Agreement', slugs: ['framework-agreements'] },
    { title: 'E. Sole Sourcing', slugs: ['sole-sourcing-requests'] },
];