<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AdminDatabasePageController;
use App\Http\Controllers\AdminUserPageController;
use App\Http\Controllers\AnnualPlanPageController;
use App\Http\Controllers\BudgetDashboardPageController;
use App\Http\Controllers\CommitteeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ModulePageController;
use App\Http\Controllers\ProcessStepPageController;
use App\Http\Controllers\ProcurementCaseController;
use App\Http\Controllers\PurchaseRequisitionPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Session-auth shell + the Blade UI pages. Data comes from /api/*
| (routes/api.php) via client-side fetch, using the Sanctum stateful
| (cookie) middleware.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/budget-dashboard', [BudgetDashboardPageController::class, 'index'])->name('budget-dashboard');
    Route::get('/annual-plans', [AnnualPlanPageController::class, 'index'])->name('annual-plans.index');
    Route::get('/annual-plans/{id}', [AnnualPlanPageController::class, 'show'])->name('annual-plans.show');

    Route::prefix('purchase-requisitions')->name('purchase-requisitions.')->group(function () {
        Route::get('/', [PurchaseRequisitionPageController::class, 'index'])->name('index');

        // Requester and Program Manager may raise a PR (document section A).
        Route::get('/create', [PurchaseRequisitionPageController::class, 'create'])
            ->name('create')
            ->middleware('role:requester,program_manager');

        Route::get('/{id}', [PurchaseRequisitionPageController::class, 'show'])->name('show')->whereNumber('id');
    });

    // Generic config-driven pages for every other module (Procurement
    // Plan, RFQ, Meetings, Evaluation, Contract Award, etc) — restricted
    // to the Procurement Officer (and Admin, for support/oversight).
    Route::middleware('role:procurement_officer,admin')->group(function () {
        Route::get('/modules', [ModulePageController::class, 'index'])->name('modules.index');
        Route::get('/process-steps/{slug}', [ProcessStepPageController::class, 'show'])->name('process-steps.show');

        // Retired — the plan-based Meeting/Attendance/Minutes module pages were
        // replaced by the case-based flow (Cases —> a case —> 1st/2nd meeting).
        // Redirect straight there instead of hitting the now-broken generic page.
        Route::get('/modules/meetings', fn () => redirect()->route('cases.index'));
        Route::get('/modules/meeting-attendances', fn () => redirect()->route('cases.index'));
        Route::get('/modules/meeting-minutes', fn () => redirect()->route('cases.index'));

        Route::get('/modules/{slug}', [ModulePageController::class, 'show'])->name('modules.show');

        Route::prefix('cases')->name('cases.')->group(function () {
            Route::get('/', [ProcurementCaseController::class, 'index'])->name('index');
            Route::get('/create', [ProcurementCaseController::class, 'create'])->name('create');
            Route::post('/', [ProcurementCaseController::class, 'store'])->name('store');
            Route::get('/{case}', [ProcurementCaseController::class, 'show'])->name('show');
            Route::post('/{case}/complete-step', [ProcurementCaseController::class, 'completeStep'])->name('complete-step');
        });

        Route::get('/cases/{case}/meetings/{type}/create', [MeetingController::class, 'create'])
            ->name('meetings.create')->where('type', 'first|second');
        Route::post('/cases/{case}/meetings/{type}', [MeetingController::class, 'store'])
            ->name('meetings.store')->where('type', 'first|second');
        Route::get('/meetings/{meeting}', [MeetingController::class, 'show'])->name('meetings.show');

        Route::prefix('settings/committee')->name('settings.committee.')->group(function () {
            Route::get('/', [CommitteeController::class, 'index'])->name('index');
            Route::post('/', [CommitteeController::class, 'store'])->name('store');
            Route::post('/{member}/toggle', [CommitteeController::class, 'toggle'])->name('toggle');
            Route::post('/{member}/email', [CommitteeController::class, 'updateEmail'])->name('update-email');
        });
    });

    // Super Admin — User Management dashboard.
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminUserPageController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/database', [AdminDatabasePageController::class, 'index'])->name('admin.database.index');
    });
});