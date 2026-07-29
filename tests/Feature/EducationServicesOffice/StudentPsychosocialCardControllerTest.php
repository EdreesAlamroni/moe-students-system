<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentPsychosocialCard;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @return array{office: EducationServicesOffice, school: School, user: User}
 */
function createEducationServicesOfficePsychosocialCardContext(): array
{
    $office = EducationServicesOffice::factory()->create();
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    foreach (['student:view', 'student:view-psychosocial-card'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_SERVICES_OFFICE->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-psychosocial-card',
    ]);

    return compact('office', 'school', 'user');
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

test('guests cannot view education services office psychosocial cards', function () {
    $student = Student::factory()->create();

    $this->get(route('education-services-office.students.psychosocial-card.show', ['student' => $student]))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without permission cannot view education services office psychosocial cards', function () {
    ['office' => $office, 'school' => $school] = createEducationServicesOfficePsychosocialCardContext();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    $student = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can view education services office psychosocial cards', function () {
    ['office' => $office, 'school' => $school, 'user' => $user] = createEducationServicesOfficePsychosocialCardContext();

    $student = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_id' => $school->id,
    ]);

    StudentPsychosocialCard::factory()->create([
        'student_id' => $student->id,
        'behavioral_problems' => [],
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.psychosocial-card.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/students/psychosocial-cards/show')
            ->where('student.uuid', $student->uuid)
            ->has('psychosocialCard')
        );
});

test('users cannot view psychosocial cards for students outside their education services office', function () {
    ['user' => $user] = createEducationServicesOfficePsychosocialCardContext();
    $otherOffice = EducationServicesOffice::factory()->create();
    $otherSchool = School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $student = Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_id' => $otherSchool->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});
