<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Minimal shell only. The actual procurement UI is the separate Next.js
| frontend, which talks to this app entirely through routes/api.php
| (Sanctum auth). This file just gives session-based access to a
| logged-in status page — handy for a quick server-side sanity check,
| and as the place to add server-rendered admin/reporting pages later
| if you ever need one outside the SPA.
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
});
