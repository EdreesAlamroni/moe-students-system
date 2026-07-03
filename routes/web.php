<?php

use App\Http\Controllers\AcademicYearSelectionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('welcome');

Route::redirect('/login', '/administration/login')->name('login');

Route::name('administration.')->prefix('administration')->middleware('bind.dashboard:administration')->group(function () {
    require sprintf('%s/web/administration.php', __DIR__);
});

Route::name('warehouse.')->prefix('warehouse')->middleware('bind.dashboard:warehouse')->group(function () {
    require sprintf('%s/web/warehouse.php', __DIR__);
});

Route::name('education-monitor.')->prefix('education-monitor')->middleware('bind.dashboard:education-monitor')->group(function () {
    require sprintf('%s/web/education-monitor.php', __DIR__);
});

Route::name('education-services-office.')->prefix('education-services-office')->middleware('bind.dashboard:education-services-office')->group(function () {
    require sprintf('%s/web/education-services-office.php', __DIR__);
});

Route::name('school.')->prefix('school')->middleware('bind.dashboard:school')->group(function () {
    require sprintf('%s/web/school.php', __DIR__);
});

// Academic Year selection (available to all guards)
Route::middleware(['auth:administration,warehouse,education_monitor,education_services_office,school'])->group(function () {
    Route::patch('academic-year/select', AcademicYearSelectionController::class)->name('academic-year.select');
});
