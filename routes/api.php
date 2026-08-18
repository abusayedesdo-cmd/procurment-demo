<?php

use App\Http\Controllers\Api\Admin\DatabaseTableController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BoqDetailController;
use App\Http\Controllers\Api\BudgetCategoryController;
use App\Http\Controllers\Api\ChartOfAccountController;
use App\Http\Controllers\Api\CommitteeMemberController;
use App\Http\Controllers\Api\ComparativeStatementController;
use App\Http\Controllers\Api\ComparativeStatementItemController;
use App\Http\Controllers\Api\ContractAgreementController;
use App\Http\Controllers\Api\ContractAwardController;
use App\Http\Controllers\Api\DeliveryReceiptController;
use App\Http\Controllers\Api\DesignDrawingController;
use App\Http\Controllers\Api\DocumentDownloadController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\EligibilityReportController;
use App\Http\Controllers\Api\EligibilityReportItemController;
use App\Http\Controllers\Api\FinancialEvaluationItemController;
use App\Http\Controllers\Api\FinancialEvaluationReportController;
use App\Http\Controllers\Api\FrameworkAgreementController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MeetingAttendanceController;
use App\Http\Controllers\Api\MeetingController;
use App\Http\Controllers\Api\MeetingMinuteController;
use App\Http\Controllers\Api\PayOrderController;
use App\Http\Controllers\Api\PrApprovalController;
use App\Http\Controllers\Api\PrItemController;
use App\Http\Controllers\Api\ProcurementAnnualPlanController;
use App\Http\Controllers\Api\ProcurementDistrictController;
use App\Http\Controllers\Api\ProcurementUpazilaController;
use App\Http\Controllers\Api\ProcurementPlanPackageController;
use App\Http\Controllers\Api\ProcurementCategoryController;
use App\Http\Controllers\Api\ProcurementPlanController;
use App\Http\Controllers\Api\PurchaseCommitteeController;
use App\Http\Controllers\Api\PurchaseRequisitionController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\RfqController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SoleSourcingRequestController;
use App\Http\Controllers\Api\SubCommitteeTransferController;
use App\Http\Controllers\Api\TechnicalEvaluationItemController;
use App\Http\Controllers\Api\TechnicalEvaluationReportController;
use App\Http\Controllers\Api\TenderAdvertisementController;
use App\Http\Controllers\Api\TenderOpeningController;
use App\Http\Controllers\Api\TenderProposalController;
use App\Http\Controllers\Api\TenderScheduleController;
use App\Http\Controllers\Api\TorDetailController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\VendorDocumentController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\BudgetLineController;
use App\Http\Controllers\Api\PrBudgetCheckController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Loaded by RouteServiceProvider with the 'api' middleware group and an
| automatic '/api' prefix.
*/

// Public — no token yet, this is how you GET one.
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Auth / session
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all', [AuthController::class, 'logoutAll']);

    // Self-service profile — signature image, reused on generated PDFs.
    Route::post('profile/signature', [ProfileController::class, 'uploadSignature']);
    Route::delete('profile/signature', [ProfileController::class, 'deleteSignature']);

    // Masters — readable by everyone logged in.
    Route::get('users', [UserController::class, 'index']);
    Route::apiResource('roles', RoleController::class);

    // Super Admin — User Management (create/edit/deactivate/delete users,
    // assign role, reset password). Everything else in this file stays
    // untouched; this is purely additive.
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [UserController::class, 'adminIndex']);
        Route::get('users/{user}', [UserController::class, 'show']);
        Route::post('users', [UserController::class, 'store']);
        Route::put('users/{user}', [UserController::class, 'update']);
        Route::delete('users/{user}', [UserController::class, 'destroy']);
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);

        // Data Manager — generic CRUD over every table (see
        // DatabaseTableController for the exclusion list: framework/system
        // tables and 'users' are never reachable here).
        Route::prefix('tables')->group(function () {
            Route::get('/', [DatabaseTableController::class, 'index']);
            Route::get('{table}/columns', [DatabaseTableController::class, 'columns'])->where('table', '[a-z_]+');
            Route::get('{table}/options', [DatabaseTableController::class, 'options'])->where('table', '[a-z_]+');
            Route::get('{table}/rows', [DatabaseTableController::class, 'rows'])->where('table', '[a-z_]+');
            Route::post('{table}/rows', [DatabaseTableController::class, 'store'])->where('table', '[a-z_]+');
            Route::put('{table}/rows/{id}', [DatabaseTableController::class, 'update'])->where('table', '[a-z_]+')->where('id', '[0-9]+');
            Route::delete('{table}/rows/{id}', [DatabaseTableController::class, 'destroy'])->where('table', '[a-z_]+')->where('id', '[0-9]+');
        });
    });
    Route::apiResource('procurement-categories', ProcurementCategoryController::class);
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('purchase-committees', PurchaseCommitteeController::class);
    Route::apiResource('committee-members', CommitteeMemberController::class);
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('vendor-documents', VendorDocumentController::class);

    // A. Input PR
    Route::apiResource('purchase-requisitions', PurchaseRequisitionController::class)->except(['store']);
    Route::post('purchase-requisitions', [PurchaseRequisitionController::class, 'store'])
        ->middleware('role:requester,program_manager');

    Route::apiResource('pr-items', PrItemController::class)->except(['store']);
    Route::post('purchase-requisitions/{purchaseRequisition}/attachment', [PurchaseRequisitionController::class, 'uploadAttachment']);
    Route::get('purchase-requisitions/{purchaseRequisition}/pdf', [DocumentDownloadController::class, 'purchaseRequisitionPdf']);
    Route::get('purchase-requisitions/{purchaseRequisition}/approvals', [PrApprovalController::class, 'index']);
    Route::post('purchase-requisitions/{purchaseRequisition}/approvals', [PrApprovalController::class, 'store']);
    Route::apiResource('boq-details', BoqDetailController::class);
    Route::apiResource('tor-details', TorDetailController::class);
    Route::apiResource('design-drawings', DesignDrawingController::class);

    Route::apiResource('vendor-documents', VendorDocumentController::class);

    // Budget dashboard — readable by everyone logged in, same as other masters.
    Route::get('budget-dashboard', [BudgetLineController::class, 'dashboard']);
    Route::get('budget-lines', [BudgetLineController::class, 'index']);
    Route::get('budget-categories', [BudgetCategoryController::class, 'index']);
    Route::get('procurement-plan-packages', [ProcurementPlanPackageController::class, 'all']);

     Route::middleware('role:budget_checker,admin')->group(function () {
        Route::post('budget-categories', [BudgetCategoryController::class, 'store']);
        Route::put('budget-categories/{budgetCategory}', [BudgetCategoryController::class, 'update']);
        Route::delete('budget-categories/{budgetCategory}', [BudgetCategoryController::class, 'destroy']);

        Route::post('budget-lines', [BudgetLineController::class, 'store']);
        Route::put('budget-lines/{budgetLine}', [BudgetLineController::class, 'update']);
        Route::delete('budget-lines/{budgetLine}', [BudgetLineController::class, 'destroy']);
    });


    Route::get('purchase-requisitions/{purchaseRequisition}/approvals', [PrApprovalController::class, 'index']);
    Route::post('purchase-requisitions/{purchaseRequisition}/approvals', [PrApprovalController::class, 'store']);

    Route::get('purchase-requisitions/{purchaseRequisition}/budget-check', [PrBudgetCheckController::class, 'show']);
    Route::post('purchase-requisitions/{purchaseRequisition}/budget-check', [PrBudgetCheckController::class, 'store'])
        ->middleware('role:budget_checker,admin');

    Route::apiResource('boq-details', BoqDetailController::class);

    // B, C, D, E — Procurement Officer's desk.
    Route::middleware('role:procurement_officer,admin')->group(function () {
    Route::apiResource('procurement-plans', ProcurementPlanController::class)->except(['destroy']);
    Route::apiResource('procurement-annual-plans', ProcurementAnnualPlanController::class);
    Route::get('procurement-annual-plans/{procurementAnnualPlan}/packages', [ProcurementPlanPackageController::class, 'index']);
    Route::post('procurement-annual-plans/{procurementAnnualPlan}/packages', [ProcurementPlanPackageController::class, 'store']);
    Route::put('procurement-plan-packages/{procurementPlanPackage}', [ProcurementPlanPackageController::class, 'update']);
    Route::delete('procurement-plan-packages/{procurementPlanPackage}', [ProcurementPlanPackageController::class, 'destroy']);
    Route::get('procurement-annual-plans/{procurementAnnualPlan}/pdf', [DocumentDownloadController::class, 'annualPlanPdf']);
    Route::get('procurement-annual-plans/{procurementAnnualPlan}/pdf/preview', [DocumentDownloadController::class, 'annualPlanPdfPreview']);
    Route::get('procurement-annual-plans/{procurementAnnualPlan}/excel', [DocumentDownloadController::class, 'annualPlanExcel']);
    Route::get('procurement-districts', [ProcurementDistrictController::class, 'index']);
    Route::get('procurement-upazilas', [ProcurementUpazilaController::class, 'index']);

        Route::apiResource('meetings', MeetingController::class);
        Route::apiResource('meeting-attendances', MeetingAttendanceController::class);
        Route::apiResource('meeting-minutes', MeetingMinuteController::class);
        Route::apiResource('sub-committee-transfers', SubCommitteeTransferController::class);
        Route::apiResource('rfqs', RfqController::class);
        Route::apiResource('tender-schedules', TenderScheduleController::class);
        Route::apiResource('tender-proposals', TenderProposalController::class);
        Route::apiResource('tender-advertisements', TenderAdvertisementController::class);
        Route::apiResource('quotations', QuotationController::class);
        Route::apiResource('tender-openings', TenderOpeningController::class);
        Route::apiResource('eligibility-reports', EligibilityReportController::class);
        Route::apiResource('eligibility-report-items', EligibilityReportItemController::class);
        Route::apiResource('technical-evaluation-reports', TechnicalEvaluationReportController::class);
        Route::apiResource('technical-evaluation-items', TechnicalEvaluationItemController::class);
        Route::apiResource('financial-evaluation-reports', FinancialEvaluationReportController::class);
        Route::apiResource('financial-evaluation-items', FinancialEvaluationItemController::class);
        Route::apiResource('comparative-statements', ComparativeStatementController::class);
        Route::apiResource('comparative-statement-items', ComparativeStatementItemController::class);
        Route::apiResource('contract-awards', ContractAwardController::class);
        Route::apiResource('pay-orders', PayOrderController::class);
        Route::apiResource('contract-agreements', ContractAgreementController::class);
        Route::apiResource('work-orders', WorkOrderController::class);
        Route::apiResource('delivery-receipts', DeliveryReceiptController::class);

        Route::apiResource('framework-agreements', FrameworkAgreementController::class);
        Route::apiResource('sole-sourcing-requests', SoleSourcingRequestController::class);

        // Document generation — downloadable .docx matching ESDO's real
        // paper formats.
        Route::get('rfqs/{rfq}/document', [DocumentDownloadController::class, 'rfq']);
        Route::get('rfqs/{rfq}/tender-schedule-document', [DocumentDownloadController::class, 'tenderSchedule']);
        Route::get('tender-openings/{tenderOpening}/document', [DocumentDownloadController::class, 'tenderOpening']);
    });
});