<?php

use App\Http\Controllers\AccountSettings\ProfileController;
use App\Http\Controllers\AccountSettings\SecurityController;
use App\Http\Controllers\EducationServicesOffice\DashboardController;
use App\Http\Controllers\EducationServicesOffice\SchoolController;
use App\Http\Controllers\EducationServicesOffice\SchoolReportController;
use App\Http\Controllers\EducationServicesOffice\StudentController;
use App\Http\Controllers\EducationServicesOffice\StudentCountByGradeLevelReportController;
use App\Http\Controllers\EducationServicesOffice\UserController;
use App\Support\Auth\DashboardAuth;
use App\Support\Auth\RegistersDashboardAuthRoutes;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

RegistersDashboardAuthRoutes::registerGuestRoutes(DashboardAuth::educationServicesOffice());

Route::middleware(['auth:education_services_office', 'ensure.password.changed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Schools
    Route::prefix('schools')->group(function () {
        Route::get('/', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('/', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('/{school}', [SchoolController::class, 'show'])->name('schools.show');
        Route::get('/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::put('/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
    });

    // Students
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        Route::get('/{student}', [StudentController::class, 'show'])->name('students.show');
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/schools', [SchoolReportController::class, 'index'])->name('reports.schools.index');
        Route::get('/schools/print', [SchoolReportController::class, 'print'])->name('reports.schools.print');

        Route::get('/student-count-by-grade-level', [StudentCountByGradeLevelReportController::class, 'index'])->name('reports.student-count-by-grade-level.index');
        Route::get('/student-count-by-grade-level/print', [StudentCountByGradeLevelReportController::class, 'print'])->name('reports.student-count-by-grade-level.print');
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
        Route::redirect('/', '/education-services-office/account-settings/profile');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('account-settings.profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('account-settings.profile.update');

        Route::get('/security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::using('education-services-office.password.confirm'))
            ->name('account-settings.security.edit');

        Route::put('/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('account-settings.password.update');
    });

    RegistersDashboardAuthRoutes::registerAuthenticatedRoutes(DashboardAuth::educationServicesOffice());
});
