<?php

use App\Http\Controllers\AccountSettings\ProfileController;
use App\Http\Controllers\AccountSettings\SecurityController;
use App\Http\Controllers\EducationMonitor\DashboardController;
use App\Http\Controllers\EducationMonitor\EducationServicesOfficeController;
use App\Http\Controllers\EducationMonitor\UserController;
use App\Support\Auth\DashboardAuth;
use App\Support\Auth\RegistersDashboardAuthRoutes;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

RegistersDashboardAuthRoutes::registerGuestRoutes(DashboardAuth::educationMonitor());

Route::middleware(['auth:education_monitor', 'ensure.password.changed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Education Services Offices
    Route::prefix('education-services-offices')->group(function () {
        Route::get('/', [EducationServicesOfficeController::class, 'index'])->name('education-services-offices.index');
        Route::get('/create', [EducationServicesOfficeController::class, 'create'])->name('education-services-offices.create');
        Route::post('/', [EducationServicesOfficeController::class, 'store'])->name('education-services-offices.store');
        Route::get('/{office}', [EducationServicesOfficeController::class, 'show'])->name('education-services-offices.show');
        Route::get('/{office}/edit', [EducationServicesOfficeController::class, 'edit'])->name('education-services-offices.edit');
        Route::put('/{office}', [EducationServicesOfficeController::class, 'update'])->name('education-services-offices.update');
        Route::delete('/{office}', [EducationServicesOfficeController::class, 'destroy'])->name('education-services-offices.destroy');
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('users.index');
        Route::get('/create/{scope}', [UserController::class, 'create'])->name('users.create');
        Route::post('/', [UserController::class, 'store'])->name('users.store');
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::prefix('/account-settings')->group(function () {
        Route::redirect('/', '/education-monitor/account-settings/profile');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('account-settings.profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('account-settings.profile.update');

        Route::get('/security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::using('education-monitor.password.confirm'))
            ->name('account-settings.security.edit');

        Route::put('/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('account-settings.password.update');
    });

    RegistersDashboardAuthRoutes::registerAuthenticatedRoutes(DashboardAuth::educationMonitor());
});
