<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AnnualPlanPageController;
use App\Http\Controllers\BudgetDashboardPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModulePageController;
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

        // Only the Requester role raises a PR (document section A).
        Route::get('/create', [PurchaseRequisitionPageController::class, 'create'])
            ->name('create')
            ->middleware('role:requester');

        Route::get('/{id}', [PurchaseRequisitionPageController::class, 'show'])->name('show')->whereNumber('id');
    });

    // Generic config-driven pages for every other module (Procurement
    // Plan, RFQ, Meetings, Evaluation, Contract Award, etc) — restricted
    // to the Procurement Officer (and Admin, for support/oversight).
    Route::middleware('role:procurement_officer,admin')->group(function () {
        Route::get('/modules', [ModulePageController::class, 'index'])->name('modules.index');
        Route::get('/modules/{slug}', [ModulePageController::class, 'show'])->name('modules.show');
    });
});
