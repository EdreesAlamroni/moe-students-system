<?php

use App\Http\Controllers\AccountSettings\ProfileController;
use App\Http\Controllers\AccountSettings\SecurityController;
use App\Http\Controllers\Administration\AcademicYearController;
use App\Http\Controllers\Administration\DashboardController;
use App\Http\Controllers\Administration\EducationMonitorController;
use App\Http\Controllers\Administration\EducationServicesOfficeController;
use App\Http\Controllers\Administration\GradeLevelController;
use App\Http\Controllers\Administration\MunicipalController;
use App\Http\Controllers\Administration\SubjectController;
use App\Http\Controllers\Administration\UserController;
use App\Http\Controllers\Administration\UserStateController;
use App\Http\Controllers\Administration\WarehouseController;
use App\Support\Auth\DashboardAuth;
use App\Support\Auth\RegistersDashboardAuthRoutes;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

RegistersDashboardAuthRoutes::registerGuestRoutes(DashboardAuth::administration());

Route::middleware(['auth:administration', 'ensure.password.changed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Municipals
    Route::prefix('/municipals')->group(function () {
        Route::get('/', [MunicipalController::class, 'index'])->name('municipals.index');
    });

    // Academic Years
    Route::prefix('academic-years')->group(function () {
        Route::get('/', [AcademicYearController::class, 'index'])->name('academic-years.index');
        Route::get('/create', [AcademicYearController::class, 'create'])->name('academic-years.create');
        Route::post('/', [AcademicYearController::class, 'store'])->name('academic-years.store');
        Route::get('/{academicYear}', [AcademicYearController::class, 'show'])->name('academic-years.show');
        Route::delete('/{academicYear}/close', [AcademicYearController::class, 'close'])->name('academic-years.close');
    });

    // Grade Levels
    Route::prefix('grade-levels')->group(function () {
        Route::get('/', [GradeLevelController::class, 'index'])->name('grade-levels.index');
    });

    // Subjects
    Route::prefix('subjects')->group(function () {
        Route::get('/', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('/', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::get('/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    });

    // Warehouses
    Route::prefix('warehouses')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('/', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // Education Monitors
    Route::prefix('education-monitors')->group(function () {
        Route::get('/', [EducationMonitorController::class, 'index'])->name('education-monitors.index');
        Route::get('/create', [EducationMonitorController::class, 'create'])->name('education-monitors.create');
        Route::post('/', [EducationMonitorController::class, 'store'])->name('education-monitors.store');
        Route::get('/{monitor}', [EducationMonitorController::class, 'show'])->name('education-monitors.show');
        Route::get('/{monitor}/edit', [EducationMonitorController::class, 'edit'])->name('education-monitors.edit');
        Route::put('/{monitor}', [EducationMonitorController::class, 'update'])->name('education-monitors.update');
        Route::delete('/{monitor}', [EducationMonitorController::class, 'destroy'])->name('education-monitors.destroy');
    });

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
        Route::patch('/{user}/state/update', [UserStateController::class, 'stateUpdate'])->name('users.state.update');
        Route::patch('/{user}/request-state/update', [UserStateController::class, 'requestStateUpdate'])->name('users.request-state.update');
    });

    // Account Settings
    Route::prefix('/account-settings')->group(function () {
        Route::redirect('/', '/administration/account-settings/profile');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('account-settings.profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('account-settings.profile.update');

        Route::get('/security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::using('administration.password.confirm'))
            ->name('account-settings.security.edit');

        Route::put('/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('account-settings.password.update');
    });

    RegistersDashboardAuthRoutes::registerAuthenticatedRoutes(DashboardAuth::administration());
});
