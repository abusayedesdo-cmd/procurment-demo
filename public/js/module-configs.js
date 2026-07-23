/**
 * Config for every module driven by the generic resource-ui.js engine.
 * Each key is the URL slug used at /modules/{slug}.
 */
const MODULE_CONFIGS = {

    // ---- B. Procurement Plan ----
    'procurement-plans': {
        title: 'Procurement Plan (approved PR থেকে auto-generate)',
        apiPath: '/procurement-plans',
        listColumns: [
            { key: 'purchase_requisition.pr_number', label: 'PR নাম্বার' },
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
    'meetings': {
        title: 'Meeting (1st/2nd)',
        apiPath: '/meetings',
        listColumns: [
            { key: 'procurement_plan.purchase_requisition.pr_number', label: 'PR' },
            { key: 'meeting_sequence', label: 'Sequence' },
            { key: 'meeting_date', label: 'Date' },
            { key: 'notice_number', label: 'Notice #' },
        ],
        formFields: [
            { name: 'procurement_plan_id', label: 'Procurement Plan', type: 'select', source: '/procurement-plans', labelField: r => r.purchase_requisition?.pr_number ?? `#${r.id}`, required: true },
            { name: 'meeting_sequence', label: 'Sequence', type: 'enum', options: ['1st', '2nd'], required: true },
            { name: 'meeting_date', label: 'Meeting Date', type: 'date', required: true },
            { name: 'notice_number', label: 'Notice Number', type: 'text' },
            { name: 'notice_file', label: 'Notice File (path/URL)', type: 'text' },
            { name: 'created_by', label: '', type: 'currentUser' },
        ],
    },

    'meeting-attendances': {
        title: 'Meeting Attendance',
        apiPath: '/meeting-attendances',
        listColumns: [
            { key: 'meeting.notice_number', label: 'Meeting' },
            { key: 'user.name', label: 'ইউজার' },
            { key: 'present', label: 'উপস্থিত' },
        ],
        formFields: [
            { name: 'meeting_id', label: 'Meeting', type: 'select', source: '/meetings', labelField: r => r.notice_number || `#${r.id}`, required: true },
            { name: 'user_id', label: 'ইউজার', type: 'select', source: '/users', labelField: 'name', required: true },
            { name: 'present', label: 'উপস্থিত ছিলেন', type: 'checkbox' },
            { name: 'signature_file', label: 'Signature File (path)', type: 'text' },
        ],
    },

    'meeting-minutes': {
        title: 'Meeting Minutes / রেজুলেশন',
        apiPath: '/meeting-minutes',
        listColumns: [
            { key: 'minutes_number', label: 'Minutes/Rezulation #' },
            { key: 'meeting.notice_number', label: 'Meeting' },
        ],
        formFields: [
            { name: 'meeting_id', label: 'Meeting', type: 'select', source: '/meetings', labelField: r => r.notice_number || `#${r.id}`, required: true },
            { name: 'resolution_text', label: 'Resolution', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { label: 'RFQ ডাউনলোড', hrefBuilder: r => `/api/rfqs/${r.id}/document` },
            { label: 'Tender Schedule ডাউনলোড', hrefBuilder: r => `/api/rfqs/${r.id}/tender-schedule-document` },
        ],
        formFields: [
            { name: 'procurement_plan_id', label: 'Procurement Plan', type: 'select', source: '/procurement-plans', labelField: r => r.purchase_requisition?.pr_number ?? `#${r.id}`, required: true },
            { name: 'type', label: 'Type', type: 'enum', options: ['RFQ', 'OTM'], required: true },
            { name: 'issue_date', label: 'Issue Date', type: 'date', required: true },
            { name: 'closing_date', label: 'Closing Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
        ],
    },

    'tender-schedules': {
        title: 'Tender Schedule (Goods/Works)',
        apiPath: '/tender-schedules',
        listColumns: [
            { key: 'rfq.rfq_number', label: 'RFQ' },
            { key: 'category', label: 'Category' },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'category', label: 'Category', type: 'enum', options: ['Goods', 'Works'], required: true },
            { name: 'schedule_details', label: 'Details', type: 'textarea' },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
        ],
    },

    // ---- Vendors & Quotations ----
    'vendors': {
        title: 'Vendors',
        apiPath: '/vendors',
        listColumns: [
            { key: 'name', label: 'নাম' },
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
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'vendor_id', label: 'Vendor', type: 'select', source: '/vendors', labelField: 'name', required: true },
            { name: 'submitted_at', label: 'Submitted At', type: 'datetime', required: true },
            { name: 'quoted_amount', label: 'Quoted Amount', type: 'number', step: '0.01', required: true },
            { name: 'status', label: 'Status', type: 'enum', options: ['received', 'opened', 'evaluated', 'disqualified'] },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { label: 'ডাউনলোড', hrefBuilder: r => `/api/tender-openings/${r.id}/document` },
        ],
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'opening_date', label: 'Opening Date', type: 'date', required: true },
            { name: 'opened_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'text' },
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
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'text' },
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
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'text' },
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
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'report_file', label: 'Report File (path/URL)', type: 'text' },
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
        formFields: [
            { name: 'rfq_id', label: 'RFQ', type: 'select', source: '/rfqs', labelField: 'rfq_number', required: true },
            { name: 'prepared_by', label: '', type: 'currentUser' },
            { name: 'lowest_evaluated_vendor_id', label: 'Lowest Evaluated Vendor', type: 'select', source: '/vendors', labelField: 'name' },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'noa_date', label: 'NOA Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'agreement_date', label: 'Agreement Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'wo_date', label: 'WO Date', type: 'date', required: true },
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
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
            { name: 'file_path', label: 'File (path/URL)', type: 'text' },
        ],
    },
};

/** Grouped for the /modules hub page nav, matching the document sections. */
const MODULE_GROUPS = [
    { title: 'B. Procurement Plan', slugs: ['procurement-plans'] },
    { title: 'C. Meetings & Committee', slugs: ['meetings', 'meeting-attendances', 'meeting-minutes', 'sub-committee-transfers'] },
    { title: 'C. RFQ / Tender', slugs: ['rfqs', 'tender-schedules', 'tender-proposals', 'tender-advertisements'] },
    { title: 'Vendors & Quotations', slugs: ['vendors', 'quotations', 'tender-openings'] },
    { title: 'C. Evaluation', slugs: ['eligibility-reports', 'eligibility-report-items', 'technical-evaluation-reports', 'technical-evaluation-items', 'financial-evaluation-reports', 'financial-evaluation-items', 'comparative-statements', 'comparative-statement-items'] },
    { title: 'C. Award & Contract', slugs: ['contract-awards', 'pay-orders', 'contract-agreements', 'work-orders', 'delivery-receipts'] },
    { title: 'D. Framework Agreement', slugs: ['framework-agreements'] },
    { title: 'E. Sole Sourcing', slugs: ['sole-sourcing-requests'] },
];
