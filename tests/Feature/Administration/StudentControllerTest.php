<?php

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

function createStudentAdminUser(): User
{
    $user = User::factory()->create();

    $permissions = [
        'student:view-any',
        'student:view',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access administration student pages', function () {
    $student = Student::factory()->create();

    $this->get(route('administration.students.index'))
        ->assertRedirect(route('administration.login'));

    $this->get(route('administration.students.show', ['student' => $student]))
        ->assertRedirect(route('administration.login'));
});

test('users without permission cannot access administration student pages', function () {
    $user = User::factory()->create();
    $student = Student::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.index'))
        ->assertForbidden();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.show', ['student' => $student]))
        ->assertForbidden();
});

test('student index renders organization selection without querying students', function () {
    $user = createStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);

    Student::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/index')
            ->has('monitors')
            ->where('schools', [])
            ->where('education_monitor_id', null)
            ->where('school_id', null)
            ->missing('students')
            ->missing('nationalities')
            ->missing('registrationStatuses')
            ->where('filter', [])
        );
});

test('student index loads schools when education monitor is selected', function () {
    $user = createStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'name' => 'مدرسة النور',
    ]);

    School::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.index', [
            'education_monitor_id' => $monitor->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/index')
            ->where('education_monitor_id', $monitor->id)
            ->where('school_id', null)
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('schools.0.name', 'مدرسة النور')
            ->missing('students')
            ->missing('nationalities')
            ->missing('registrationStatuses')
        );
});

test('student index loads students only when school is selected', function () {
    $user = createStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);
    $otherSchool = School::factory()->create(['education_monitor_id' => $monitor->id]);

    $students = Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $otherSchool->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.index', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/index')
            ->where('education_monitor_id', $monitor->id)
            ->where('school_id', $school->id)
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $students->pluck('uuid')->sort()->values()->all())
            ->where('students.data.0.can.view', true)
        );
});

test('student index filters students by registration status and nationality', function () {
    $user = createStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);
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

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.index', [
            'education_monitor_id' => $monitor->id,
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

test('student index ignores school that does not belong to selected monitor', function () {
    $user = createStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $otherMonitor = EducationMonitor::factory()->create();
    $school = School::factory()->create(['education_monitor_id' => $otherMonitor->id]);

    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.index', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('education_monitor_id', $monitor->id)
            ->where('school_id', null)
            ->missing('students')
            ->missing('nationalities')
            ->missing('registrationStatuses')
        );
});

test('student show displays student details', function () {
    $user = createStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/show')
            ->where('student.uuid', $student->uuid)
            ->where('student.full_name', $student->full_name)
            ->where('student.monitor.name', $monitor->name)
            ->where('student.school.name', $school->name)
            ->where('student.nationality.name', $student->nationality->name)
        );
});
