<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationMonitorOfficeReportUser(EducationMonitor $monitor, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ], $attributes));

    foreach (['report:education-services-office:view', 'report:education-services-office:print'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
    }

    $user->givePermissionTo([
        'report:education-services-office:view',
        'report:education-services-office:print',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/reports/education-services-offices', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the education services office report page', function () {
    $this->get(route('education-monitor.reports.education-services-offices.index'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without education services office report permissions cannot view the report', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.education-services-offices.index'))
        ->assertForbidden();
});

test('authenticated users can visit the education services office report page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeReportUser($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create([
        'name' => 'مكتب الخدمات التعليمية المركز',
    ]);
    $otherMonitor = EducationMonitor::factory()->create();
    EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.education-services-offices.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/reports/education-services-offices')
            ->has('offices.data', 1)
            ->where('offices.data.0.uuid', $office->uuid)
            ->where('offices.data.0.name', $office->name)
            ->where('offices.data.0.schools_count', 0)
            ->where('offices.data.0.students_count', 0)
            ->where('can.print', true)
            ->where('filter', [])
        );
});

test('guests are redirected from the education services office report print page', function () {
    $this->get(route('education-monitor.reports.education-services-offices.print'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without print permission cannot print the education services office report', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    Permission::findOrCreate('report:education-services-office:view', UserScope::EDUCATION_MONITOR->value);
    $user->givePermissionTo('report:education-services-office:view');

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.education-services-offices.print'))
        ->assertForbidden();
});

test('authenticated users can print the education services office report', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeReportUser($monitor);
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create([
        'name' => 'مكتب الخدمات التعليمية المركز',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.education-services-offices.print'))
        ->assertOk()
        ->assertViewIs('print.education-monitor.reports.education-services-offices')
        ->assertSee('تقرير مكاتب الخدمات التعليمية')
        ->assertSee('مكتب الخدمات التعليمية المركز')
        ->assertSee('2024-2025')
        ->assertSee($user->name);
});
