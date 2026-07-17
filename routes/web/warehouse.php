<?php

use App\Http\Controllers\AccountSettings\ProfileController;
use App\Http\Controllers\AccountSettings\SecurityController;
use App\Http\Controllers\Warehouse\DashboardController;
use App\Http\Controllers\Warehouse\EducationMonitorController;
use App\Http\Controllers\Warehouse\UserController;
use App\Support\Auth\DashboardAuth;
use App\Support\Auth\RegistersDashboardAuthRoutes;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

RegistersDashboardAuthRoutes::registerGuestRoutes(DashboardAuth::warehouse());

Route::middleware(['auth:warehouse', 'ensure.password.changed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Education Monitors
    Route::prefix('education-monitors')->group(function () {
        Route::get('/', [EducationMonitorController::class, 'index'])->name('education-monitors.index');
        Route::get('/{monitor}', [EducationMonitorController::class, 'show'])->name('education-monitors.show');
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::prefix('/account-settings')->group(function () {
        Route::redirect('/', '/warehouse/account-settings/profile');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('account-settings.profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('account-settings.profile.update');

        Route::get('/security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::using('warehouse.password.confirm'))
            ->name('account-settings.security.edit');

        Route::put('/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('account-settings.password.update');
    });

    RegistersDashboardAuthRoutes::registerAuthenticatedRoutes(DashboardAuth::warehouse());
});
