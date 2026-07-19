<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationServicesOffice;
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
function createEducationServicesOfficeStudentManager(EducationServicesOffice $office, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ], $attributes));

    foreach (['student:view-any', 'student:view'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_SERVICES_OFFICE->value);
    }

    $user->givePermissionTo([
        'student:view-any',
        'student:view',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-services-office/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access education services office student pages', function () {
    $student = Student::factory()->create();

    $this->get(route('education-services-office.students.index'))
        ->assertRedirect(route('education-services-office.login'));

    $this->get(route('education-services-office.students.show', ['student' => $student]))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without permission cannot access education services office student pages', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $student = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index'))
        ->assertForbidden();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.show', ['student' => $student]))
        ->assertForbidden();
});

test('student index renders school selection without querying students', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();

    Student::factory()->count(3)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/students/index')
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('school_id', null)
            ->missing('students')
            ->missing('nationalities')
            ->missing('registrationStatuses')
            ->where('filter', [])
        );
});

test('student index only lists schools for the current education services office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create([
        'name' => 'مدرسة النور',
    ]);

    $otherOffice = EducationServicesOffice::factory()->create();
    School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/students/index')
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('schools.0.name', 'مدرسة النور')
            ->missing('students')
        );
});

test('student index loads students only when school is selected', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $otherSchool = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();

    $students = Student::factory()->count(2)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $otherSchool->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index', [
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/students/index')
            ->where('school_id', $school->id)
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $students->pluck('uuid')->sort()->values()->all())
            ->where('students.data.0.can.view', true)
        );
});

test('student index filters students by registration status and nationality', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
        'nationality_id' => $nationality->id,
        'registration_status' => 'new',
    ]);

    Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
        'registration_status' => 'repeater',
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index', [
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

test('student index ignores school that does not belong to the current education services office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index', [
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

test('student index does not include students from other education services offices', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $otherSchool = School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $ownStudent = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_id' => $otherSchool->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.index', [
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $ownStudent->uuid)
        );
});

test('student show displays student details', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();

    $student = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/students/show')
            ->where('student.uuid', $student->uuid)
            ->where('student.first_name', $student->first_name)
            ->where('student.father_name', $student->father_name)
            ->where('student.grandfather_name', $student->grandfather_name)
            ->where('student.surname', $student->surname)
            ->where('student.monitor.name', $office->monitor->name)
            ->where('student.school.name', $school->name)
            ->where('student.nationality.name', $student->nationality->name)
        );
});

test('users cannot view students belonging to another education services office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeStudentManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $otherSchool = School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();
    $student = Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_id' => $otherSchool->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.show', ['student' => $student]))
        ->assertForbidden();
});
