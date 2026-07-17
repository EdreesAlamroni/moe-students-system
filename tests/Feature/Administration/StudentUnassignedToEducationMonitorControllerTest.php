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

function createUnassignedStudentAdminUser(): User
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
    PolicyRegistrar::register(Request::create('/administration/students/unassigned-to-education-monitor', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access unassigned students page', function () {
    $this->get(route('administration.students.unassigned-to-education-monitor.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without permission cannot access unassigned students page', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.unassigned-to-education-monitor.index'))
        ->assertForbidden();
});

test('unassigned students index lists only students without education monitor', function () {
    $user = createUnassignedStudentAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);

    $unassignedStudents = Student::factory()->count(2)->create([
        'education_monitor_id' => null,
        'school_id' => null,
    ]);

    Student::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.unassigned-to-education-monitor.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/unassigned-to-education-monitor/index')
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

test('unassigned students index filters by registration status and nationality', function () {
    $user = createUnassignedStudentAdminUser();
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->create([
        'education_monitor_id' => null,
        'school_id' => null,
        'nationality_id' => $nationality->id,
        'registration_status' => 'new',
    ]);

    Student::factory()->create([
        'education_monitor_id' => null,
        'school_id' => null,
        'registration_status' => 'repeater',
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.unassigned-to-education-monitor.index', [
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

test('unassigned students show page reuses administration student show page', function () {
    $user = createUnassignedStudentAdminUser();

    $student = Student::factory()->create([
        'education_monitor_id' => null,
        'school_id' => null,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/students/show')
            ->where('student.uuid', $student->uuid)
            ->where('student.first_name', $student->first_name)
            ->where('student.monitor', null)
            ->where('student.school', null)
        );
});
