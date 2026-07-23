<?php

use App\Http\Controllers\AccountSettings\ProfileController;
use App\Http\Controllers\AccountSettings\SecurityController;
use App\Http\Controllers\EducationMonitor\DashboardController;
use App\Http\Controllers\EducationMonitor\EducationServicesOfficeController;
use App\Http\Controllers\EducationMonitor\EducationServicesOfficeReportController;
use App\Http\Controllers\EducationMonitor\SchoolClassroomDistributionResetController;
use App\Http\Controllers\EducationMonitor\SchoolController;
use App\Http\Controllers\EducationMonitor\SchoolReportController;
use App\Http\Controllers\EducationMonitor\StudentController;
use App\Http\Controllers\EducationMonitor\StudentCountByGradeLevelReportController;
use App\Http\Controllers\EducationMonitor\StudentTransferController;
use App\Http\Controllers\EducationMonitor\StudentUnassignedToSchoolController;
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

    // Schools - Classroom Distribution
    Route::prefix('schools')->group(function () {
        Route::post('/{school}/classroom-distribution/reset', [SchoolClassroomDistributionResetController::class, 'reset'])->name('schools.classroom-distribution.reset');
    });

    // Students
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        Route::get('/unassigned-to-school', [StudentUnassignedToSchoolController::class, 'index'])->name('students.unassigned-to-school.index');
        Route::get('/{student}', [StudentController::class, 'show'])->name('students.show');

        // Student Transfers
        Route::get('/transfers/create', [StudentTransferController::class, 'create'])->name('students.transfers.create');
        Route::post('/transfers', [StudentTransferController::class, 'store'])->name('students.transfers.store');
        Route::delete('/transfers/{student}', [StudentTransferController::class, 'destroy'])->name('students.transfers.destroy');
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

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/education-services-offices', [EducationServicesOfficeReportController::class, 'index'])->name('reports.education-services-offices.index');
        Route::get('/education-services-offices/print', [EducationServicesOfficeReportController::class, 'print'])->name('reports.education-services-offices.print');

        Route::get('/schools', [SchoolReportController::class, 'index'])->name('reports.schools.index');
        Route::get('/schools/print', [SchoolReportController::class, 'print'])->name('reports.schools.print');

        Route::get('/student-count-by-grade-level', [StudentCountByGradeLevelReportController::class, 'index'])->name('reports.student-count-by-grade-level.index');
        Route::get('/student-count-by-grade-level/print', [StudentCountByGradeLevelReportController::class, 'print'])->name('reports.student-count-by-grade-level.print');
    });

    // Account Settings
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
