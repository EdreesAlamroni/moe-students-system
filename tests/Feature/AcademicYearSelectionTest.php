<?php

use App\Http\Controllers\AcademicYearSelectionController;
use App\Models\AcademicYear;
use App\Models\User;
use App\Support\Auth\DashboardAuth;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    AcademicYear::clearCachedCurrent();

    Route::middleware(['web', 'auth:administration', 'bind.dashboard:administration'])
        ->patch('/administration/academic-year/select', AcademicYearSelectionController::class)
        ->name('administration.academic-year.select.test');

    Route::getRoutes()->refreshNameLookups();
});

test('guests cannot select an academic year', function () {
    $year = AcademicYear::factory()->create();

    $this->patch('/administration/academic-year/select', [
        'academic_year_id' => $year->id,
    ])->assertRedirect(route('administration.login'));
});

test('academic year selection requires an academic year id', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->from('/administration/dashboard')
        ->patch('/administration/academic-year/select', [])
        ->assertSessionHasErrors([
            'academic_year_id' => 'الرجاء اختيار السنة الدراسية',
        ]);
});

test('academic year selection requires a valid academic year id', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->from('/administration/dashboard')
        ->patch('/administration/academic-year/select', [
            'academic_year_id' => 99999,
        ])
        ->assertSessionHasErrors([
            'academic_year_id' => 'السنة الدراسية المحددة غير موجودة',
        ]);
});

test('academic year selection requires an integer academic year id', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->from('/administration/dashboard')
        ->patch('/administration/academic-year/select', [
            'academic_year_id' => 'not-an-integer',
        ])
        ->assertSessionHasErrors('academic_year_id');
});

test('authenticated users can select an academic year', function () {
    $user = User::factory()->create();
    $year = AcademicYear::factory()->create(['is_active' => false]);

    $response = $this->actingAs($user, 'administration')
        ->from('/administration/dashboard')
        ->patch('/administration/academic-year/select', [
            'academic_year_id' => $year->id,
        ]);

    $response
        ->assertRedirect('/administration/dashboard')
        ->assertSessionHas('laravel_flash_message.message', __('تم تغيير العام الدراسي بنجاح.'))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    app()->instance(DashboardAuth::class, DashboardAuth::administration());

    expect(session(AcademicYear::selectedSessionKey()))->toBe($year->id);
});

test('selecting an academic year clears the cached current academic year', function () {
    $user = User::factory()->create();
    $activeYear = AcademicYear::factory()->active()->create();
    $inactiveYear = AcademicYear::factory()->create(['is_active' => false]);

    $this->actingAs($user, 'administration');
    app()->instance(DashboardAuth::class, DashboardAuth::administration());

    expect(AcademicYear::current()->id)->toBe($activeYear->id);

    $this->from('/administration/dashboard')
        ->patch('/administration/academic-year/select', [
            'academic_year_id' => $inactiveYear->id,
        ])
        ->assertRedirect('/administration/dashboard');

    expect(AcademicYear::current()->id)->toBe($inactiveYear->id);
});

test('academic year selection redirects to the previous url path only', function () {
    $user = User::factory()->create();
    $year = AcademicYear::factory()->create();

    $this->actingAs($user, 'administration')
        ->from('https://example.test/administration/dashboard?tab=students')
        ->patch('/administration/academic-year/select', [
            'academic_year_id' => $year->id,
        ])
        ->assertRedirect('/administration/dashboard');
});

test('academic year selection redirects to root when no previous url is stored', function () {
    $user = User::factory()->create();
    $year = AcademicYear::factory()->create();

    $this->actingAs($user, 'administration')
        ->withSession(['_previous.url' => null])
        ->patch('/administration/academic-year/select', [
            'academic_year_id' => $year->id,
        ])
        ->assertRedirect('/');
});
