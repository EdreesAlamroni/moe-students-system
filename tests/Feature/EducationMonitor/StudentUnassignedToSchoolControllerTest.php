<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createUnassignedToSchoolEducationMonitorStudentManager(EducationMonitor $monitor, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ], $attributes));

    foreach (['student:view-any', 'student:view'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
    }

    $user->givePermissionTo([
        'student:view-any',
        'student:view',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/students/unassigned-to-school', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access unassigned to school students page', function () {
    $this->get(route('education-monitor.students.unassigned-to-school.index'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without permission cannot access unassigned to school students page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.unassigned-to-school.index'))
        ->assertForbidden();
});

test('unassigned to school students index lists only students for the current monitor without school', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createUnassignedToSchoolEducationMonitorStudentManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create();

    $unassignedStudents = Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => null,
    ]);

    Student::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => null,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.unassigned-to-school.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/unassigned-to-school/index')
            ->has('nationalities')
            ->has('registrationStatuses')
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $unassignedStudents->pluck('uuid')->sort()->values()->all())
            ->where('students.data.0.can.view', true)
            ->where('filter', [])
        );
});

test('unassigned to school students index filters by registration status and nationality', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createUnassignedToSchoolEducationMonitorStudentManager($monitor);
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => null,
        'nationality_id' => $nationality->id,
        'registration_status' => 'new',
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => null,
        'registration_status' => 'repeater',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.unassigned-to-school.index', [
            'filter' => [
                'registration_status' => 'new',
                'nationality_id' => $nationality->id,
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $matchingStudent->uuid)
        );
});

test('unassigned to school students show page reuses education monitor student show page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createUnassignedToSchoolEducationMonitorStudentManager($monitor);

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => null,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/show')
            ->where('student.uuid', $student->uuid)
            ->where('student.first_name', $student->first_name)
            ->where('student.monitor.uuid', $monitor->uuid)
            ->where('student.school', null)
        );
});

test('users cannot view unassigned students belonging to another education monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createUnassignedToSchoolEducationMonitorStudentManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $student = Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => null,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.show', ['student' => $student]))
        ->assertForbidden();
});
