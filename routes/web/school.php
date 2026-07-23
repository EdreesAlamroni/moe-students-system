<?php

use App\Http\Controllers\AccountSettings\ProfileController;
use App\Http\Controllers\AccountSettings\SecurityController;
use App\Http\Controllers\School\ClassroomController;
use App\Http\Controllers\School\ClassScheduleController;
use App\Http\Controllers\School\DashboardController;
use App\Http\Controllers\School\GradeLevelController;
use App\Http\Controllers\School\StudentAcademicRecordController;
use App\Http\Controllers\School\StudentClassroomEnrollmentController;
use App\Http\Controllers\School\StudentController;
use App\Http\Controllers\School\StudentGradeLevelEnrollmentController;
use App\Http\Controllers\School\StudentPsychosocialCardController;
use App\Http\Controllers\School\StudentTransferController;
use App\Http\Controllers\School\UserController;
use App\Support\Auth\DashboardAuth;
use App\Support\Auth\RegistersDashboardAuthRoutes;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

RegistersDashboardAuthRoutes::registerGuestRoutes(DashboardAuth::school());

Route::middleware(['auth:school', 'ensure.password.changed'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Grade Levels
    Route::prefix('grade-levels')->group(function () {
        Route::get('/', [GradeLevelController::class, 'index'])->name('grade-levels.index');
    });

    // Classrooms
    Route::prefix('classrooms')->group(function () {
        Route::get('/', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::get('/create', [ClassroomController::class, 'create'])->name('classrooms.create');
        Route::post('/', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::get('/{classroom}', [ClassroomController::class, 'show'])->name('classrooms.show');
        Route::get('/{classroom}/edit', [ClassroomController::class, 'edit'])->name('classrooms.edit');
        Route::put('/{classroom}', [ClassroomController::class, 'update'])->name('classrooms.update');

        // Class Schedules
        Route::get('/{classroom}/schedule', [ClassScheduleController::class, 'show'])->name('classrooms.class-schedules.show');
        Route::get('/{classroom}/schedule/edit', [ClassScheduleController::class, 'edit'])->name('classrooms.class-schedules.edit');
        Route::put('/{classroom}/schedule', [ClassScheduleController::class, 'update'])->name('classrooms.class-schedules.update');
        Route::get('/{classroom}/schedule/print', [ClassScheduleController::class, 'print'])->name('classrooms.class-schedules.print');
    });

    // Students
    Route::prefix('students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('students.index');
        // Route::get('/unenrolled-from-grade-level', [StudentUnenrolledFromGradeLevelController::class, 'index'])->name('students.unenrolled-from-grade-level.index');
        // Route::get('/unenrolled-from-classroom', [StudentUnenrolledFromClassroomController::class, 'index'])->name('students.unenrolled-from-classroom.index');
        Route::get('/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/', [StudentController::class, 'store'])->name('students.store');
        Route::get('/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/{student}', [StudentController::class, 'update'])->name('students.update');

        // Grade Level Enrollments
        Route::post('/{student}/grade-level-enrollments', [StudentGradeLevelEnrollmentController::class, 'store'])->name('students.grade-level-enrollments.store');
        Route::post('/{student}/classroom-enrollments', [StudentClassroomEnrollmentController::class, 'store'])->name('students.classroom-enrollments.store');
        Route::put('/{student}/classroom-enrollments', [StudentClassroomEnrollmentController::class, 'update'])->name('students.classroom-enrollments.update');

        // Student Psychosocial Card
        Route::get('/{student}/psychosocial-card', [StudentPsychosocialCardController::class, 'show'])->name('students.psychosocial-card.show');
        Route::get('/{student}/psychosocial-card/edit', [StudentPsychosocialCardController::class, 'edit'])->name('students.psychosocial-card.edit');
        Route::put('/{student}/psychosocial-card', [StudentPsychosocialCardController::class, 'update'])->name('students.psychosocial-card.update');
        Route::get('/{student}/psychosocial-card/print', [StudentPsychosocialCardController::class, 'print'])->name('students.psychosocial-card.print');

        // Student Transfers
        Route::get('/transfers/create', [StudentTransferController::class, 'create'])->name('students.transfers.create');
        Route::post('/transfers', [StudentTransferController::class, 'store'])->name('students.transfers.store');
        Route::delete('/transfers/{student}', [StudentTransferController::class, 'destroy'])->name('students.transfers.destroy');

        // Academic Records
        Route::get('/{student}/academic-record', [StudentAcademicRecordController::class, 'show'])->name('students.academic-record.show');
        Route::get('/{student}/academic-record/create', [StudentAcademicRecordController::class, 'create'])->name('students.academic-record.create');
        Route::post('/{student}/academic-record', [StudentAcademicRecordController::class, 'store'])->name('students.academic-record.store');
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
        Route::redirect('/', '/school/account-settings/profile');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('account-settings.profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('account-settings.profile.update');

        Route::get('/security', [SecurityController::class, 'edit'])
            ->middleware(RequirePassword::using('school.password.confirm'))
            ->name('account-settings.security.edit');

        Route::put('/password', [SecurityController::class, 'update'])
            ->middleware('throttle:6,1')
            ->name('account-settings.password.update');
    });

    RegistersDashboardAuthRoutes::registerAuthenticatedRoutes(DashboardAuth::school());
});
