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
function createEducationMonitorStudentManager(EducationMonitor $monitor, array $attributes = []): User
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
    PolicyRegistrar::register(Request::create('/education-monitor/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access education monitor student pages', function () {
    $student = Student::factory()->create();

    $this->get(route('education-monitor.students.index'))
        ->assertRedirect(route('education-monitor.login'));

    $this->get(route('education-monitor.students.show', ['student' => $student]))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without permission cannot access education monitor student pages', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);
    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index'))
        ->assertForbidden();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.show', ['student' => $student]))
        ->assertForbidden();
});

test('student index renders school selection without querying students', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $school = School::factory()->for($monitor, 'monitor')->create();

    Student::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/index')
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('school_id', null)
            ->missing('students')
            ->missing('nationalities')
            ->missing('registrationStatuses')
            ->where('filter', [])
        );
});

test('student index only lists schools for the current education monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $school = School::factory()->for($monitor, 'monitor')->create([
        'name' => 'مدرسة النور',
    ]);

    School::factory()->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/index')
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('schools.0.name', 'مدرسة النور')
            ->missing('students')
        );
});

test('student index loads students only when school is selected', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $school = School::factory()->for($monitor, 'monitor')->create();
    $otherSchool = School::factory()->for($monitor, 'monitor')->create();

    $students = Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $otherSchool->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index', [
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/index')
            ->where('school_id', $school->id)
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $students->pluck('uuid')->sort()->values()->all())
            ->where('students.data.0.can.view', true)
        );
});

test('student index filters students by registration status and nationality', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $school = School::factory()->for($monitor, 'monitor')->create();
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'nationality_id' => $nationality->id,
        'registration_status' => 'new',
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'registration_status' => 'repeater',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index', [
            'school_id' => $school->id,
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

test('student index ignores school that does not belong to the current education monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($otherMonitor, 'monitor')->create();

    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index', [
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('school_id', null)
            ->missing('students')
            ->missing('nationalities')
            ->missing('registrationStatuses')
        );
});

test('student index does not include students from other education monitors', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create();

    $ownStudent = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.index', [
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $ownStudent->uuid)
        );
});

test('student show displays student details', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $school = School::factory()->for($monitor, 'monitor')->create();

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/show')
            ->where('student.uuid', $student->uuid)
            ->where('student.first_name', $student->first_name)
            ->where('student.father_name', $student->father_name)
            ->where('student.grandfather_name', $student->grandfather_name)
            ->where('student.surname', $student->surname)
            ->where('student.monitor.name', $monitor->name)
            ->where('student.school.name', $school->name)
            ->where('student.nationality.name', $student->nationality->name)
        );
});

test('users cannot view students belonging to another education monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorStudentManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $student = Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.show', ['student' => $student]))
        ->assertForbidden();
});
