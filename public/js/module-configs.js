/**
 * Config for every module driven by the generic resource-ui.js engine.
 * Each key is the URL slug used at /modules/{slug}.
 */
const MODULE_CONFIGS = {

    // ---- B. Procurement Plan ----
    'procurement-plans': {
        title: 'Procurement Plan (auto-generated from approved PR)',
        apiPath: '/procurement-plans',
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
        listColumns: [
            { key: 'name', label: 'Name' },
            { key: 'address', label: 'Address' },
            { key: 'type', label: 'Type' },
            { key: 'parent_committee.name', label: 'Parent Committee' },
        ],
        formFields: [
            { name: 'name', label: 'Committee Name', type: 'text', required: true },
            { name: 'address', label: 'Address', type: 'text' },
            { name: 'type', label: 'Type', type: 'enum', options: ['main', 'sub'], required: true },
            { name: 'parent_committee_id', label: 'Parent Committee', type: 'select', source: '/purchase-committees', labelField: 'name' },
        ],
    },

    'sub-committee-transfers': {
        title: 'Sub-Committee Transfer',
        apiPath: '/sub-committee-transfers',
        listColumns: [
            { key: 'from_committee.name', label: 'From' },
            { key: 'to_committee.name', label: 'To' },
            { key: 'transfer_date', label: 'Date' },
        ],
        formFields: [
            { name: 'procurement_plan_id', label: 'Procurement Plan', type: 'select', source: '/procurement-plans', labelField: r => r.purchase_requisition?.pr_number ?? `#${r.id}`, required: true },
            { name: 'from_committee_id', label: 'From Committee', type: 'select', source: '/purchase-committees', labelField: 'name', required: true },
            { name: 'to_committee_id', label: 'To Committee', type: 'select', source: '/purchase-committees', labelField: 'name', required: true },
            { name: 'transfer_date', label: 'Transfer Date', type: 'date', required: true },
            { name: 'transfer_note', label: 'Note', type: 'textarea' },
        ],
    },

    // ---- C. RFQ / Tender ----
    'rfqs': {
        title: 'RFQ / OTM',
        apiPath: '/rfqs',
        listColumns: [
            { key: 'rfq_number', label: 'RFQ #' },
            { key: 'type', label: 'Type' },
            { key: 'issue_date', label: 'Issue Date' },
            { key: 'closing_date', label: 'Closing Date' },
        ],
        rowActions: [
            { label: 'Preview RFQ', hrefBuilder: r => `/api/rfqs/${r.id}/preview` },
            { label: 'Download RFQ', hrefBuilder: r => `/api/rfqs/${r.id}/document`, download: true },
            { label: 'Preview Schedule', hrefBuilder: r => `/api/rfqs/${r.id}/tender-schedule-preview` },
            { label: 'Download Schedule', hrefBuilder: r => `/api/rfqs/${r.id}/tender-schedule-document`, download: true },
        ],
        formFields: [
            { name: 'procurement_case_id', label: 'Procurement Case', type: 'select', source: '/procurement-cases', labelField: 'ref', required: true },
            { name: 'subject', label: 'Subject', type: 'text', required: true, autofillFrom: { field: 'procurement_case_id', property: 'title' } },
            { name: 'type', label: 'Type', type: 'enum', options: ['RFQ', 'OTM'], required: true, autofillFrom: { field: 'procurement_case_id', property: 'rfq_type_hint' } },
            { name: 'issue_date', label: 'Issue Date', type: 'date', required: true, autofillFrom: { field: 'procurement_case_id', property: 'issue_date_hint' } },
            { name: 'closing_date', label: 'Closing Date', type: 'date', required: true, autofillFrom: { field: 'procurement_case_id', property: 'closing_date_hint' } },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
        ],
    },

    'tender-schedules': {
        title: 'Tender Schedule (Goods/Works)',
        apiPath: '/tender-schedules',
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
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'medium', label: 'Medium' },
            { key: 'category', label: 'Category' },
            { key: 'publish_date', label: 'Publish Date' },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'medium', label: 'Medium', type: 'enum', options: ['Newspaper', 'bdjobs'], required: true },
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
        ],
        rowActions: [
            { label: 'Preview', hrefBuilder: r => r.file_path || null },
            { label: 'Download', hrefBuilder: r => r.file_path || null, download: true },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'submitted_at', label: 'Submitted At', type: 'datetime', required: true },
            { name: 'quoted_amount', label: 'Quoted Amount', type: 'number', step: '0.01', required: true },
            { name: 'status', label: 'Status', type: 'enum', options: ['received', 'opened', 'evaluated', 'disqualified'] },
            { name: 'representative_name', label: 'Representative Name', type: 'text' },
            { name: 'representative_contact', label: 'Representative Contact', type: 'text' },
            { name: 'attended', label: 'Attended', type: 'checkbox' },
            { name: 'trade_license_submitted', label: 'Trade License Submitted', type: 'checkbox' },
            { name: 'tin_submitted', label: 'TIN Submitted', type: 'checkbox' },
            { name: 'bin_submitted', label: 'BIN Submitted', type: 'checkbox' },
            { name: 'opening_remarks', label: 'Opening Remarks', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'file' },
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
            { name: 'eligible', label: 'Eligible', type: 'checkbox' },
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
            { name: 'score', label: 'Score (0-100)', type: 'number', step: '0.01' },
            { name: 'remarks', label: 'Remarks', type: 'textarea' },
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
        ],
        formFields: [
            { name: 'fer_id', label: 'Financial Evaluation Report', type: 'select', source: '/financial-evaluation-reports', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'quoted_amount', label: 'Amount', type: 'number', step: '0.01', required: true },
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
            { key: 'rank', label: 'Rank' },
            { key: 'amount', label: 'Amount' },
        ],
        formFields: [
            { name: 'comparative_statement_id', label: 'Comparative Statement', type: 'select', source: '/comparative-statements', labelField: r => r.rfq?.rfq_number ?? `#${r.id}`, required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'rank', label: 'Rank', type: 'number' },
            { name: 'amount', label: 'Amount', type: 'number', step: '0.01', required: true },
        ],
    },

    // ---- Award & Contract ----
    'contract-awards': {
        title: 'Notification of Contract Award (NOA)',
        apiPath: '/contract-awards',
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
    { title: 'C. RFQ / Tender', slugs: ['rfqs', 'tender-schedules', 'tender-proposals', 'tender-advertisements'] },
    { title: 'Vendors & Quotations', slugs: ['vendors', 'quotations', 'tender-openings'] },
    { title: 'C. Evaluation', slugs: ['eligibility-reports', 'eligibility-report-items', 'technical-evaluation-reports', 'technical-evaluation-items', 'financial-evaluation-reports', 'financial-evaluation-items', 'comparative-statements', 'comparative-statement-items'] },
    { title: 'C. Award & Contract', slugs: ['contract-awards', 'pay-orders', 'contract-agreements', 'work-orders', 'delivery-receipts'] },
    { title: 'D. Framework Agreement', slugs: ['framework-agreements'] },
    { title: 'E. Sole Sourcing', slugs: ['sole-sourcing-requests'] },
];