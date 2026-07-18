<?php

use App\Enums\SchoolType;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationMonitorSchoolReportUser(EducationMonitor $monitor, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ], $attributes));

    foreach (['report:school:view', 'report:school:print'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
    }

    $user->givePermissionTo([
        'report:school:view',
        'report:school:print',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/reports/schools', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the school report page', function () {
    $this->get(route('education-monitor.reports.schools.index'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without school report permissions cannot view the report', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.schools.index'))
        ->assertForbidden();
});

test('authenticated users can visit the school report page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorSchoolReportUser($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create([
        'name' => 'مكتب الخدمات التعليمية المركز',
    ]);
    $school = School::factory()->for($monitor, 'monitor')->for($office, 'office')->create([
        'name' => 'مدرسة الأمل',
        'type' => SchoolType::PUBLIC,
    ]);
    $otherMonitor = EducationMonitor::factory()->create();
    School::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.schools.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/reports/schools')
            ->has('schools.data', 1)
            ->where('schools.data.0.uuid', $school->uuid)
            ->where('schools.data.0.name', $school->name)
            ->where('schools.data.0.serial_number', $school->serial_number)
            ->where('schools.data.0.office.name', $office->name)
            ->where('schools.data.0.students_count', 0)
            ->has('types')
            ->where('can.print', true)
            ->where('filter', [])
        );
});

test('guests are redirected from the school report print page', function () {
    $this->get(route('education-monitor.reports.schools.print'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without print permission cannot print the school report', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    Permission::findOrCreate('report:school:view', UserScope::EDUCATION_MONITOR->value);
    $user->givePermissionTo('report:school:view');

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.schools.print'))
        ->assertForbidden();
});

test('authenticated users can print the school report', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorSchoolReportUser($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    School::factory()->for($monitor, 'monitor')->for($office, 'office')->create([
        'name' => 'مدرسة الأمل',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.reports.schools.print'))
        ->assertOk()
        ->assertViewIs('print.education-monitor.reports.schools')
        ->assertSee('تقرير المدارس')
        ->assertSee('مدرسة الأمل')
        ->assertSee('2024-2025')
        ->assertSee($user->name);
});
