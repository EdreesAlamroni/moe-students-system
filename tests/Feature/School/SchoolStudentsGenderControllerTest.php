<?php

use App\Enums\SchoolStudentsGender;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Support\Organization\OrganizationContextManager;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;

function createSchoolUser(School $school): User
{
    return User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/dashboard', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot update school students gender', function () {
    $this->patch(route('school.students-gender.update'), [
        'students_gender' => SchoolStudentsGender::BOYS->value,
    ])->assertRedirect(route('school.login'));
});

test('authenticated school users can update school students gender when it is not configured', function () {
    $school = School::factory()->create(['students_gender' => null]);
    $user = createSchoolUser($school);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->patch(route('school.students-gender.update'), [
            'students_gender' => SchoolStudentsGender::GIRLS->value,
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($school->fresh()->students_gender)->toBe(SchoolStudentsGender::GIRLS);
});

test('school users cannot update students gender when it is already configured', function () {
    $school = School::factory()->create([
        'students_gender' => SchoolStudentsGender::BOYS,
    ]);
    $user = createSchoolUser($school);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->patch(route('school.students-gender.update'), [
            'students_gender' => SchoolStudentsGender::GIRLS->value,
        ])
        ->assertForbidden();

    expect($school->fresh()->students_gender)->toBe(SchoolStudentsGender::BOYS);
});

test('students gender update requires a valid value', function () {
    $school = School::factory()->create(['students_gender' => null]);
    $user = createSchoolUser($school);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->patch(route('school.students-gender.update'), [])
        ->assertSessionHasErrors('students_gender');

    expect($school->fresh()->students_gender)->toBeNull();
});

test('school organization context is shared when students gender is not configured', function () {
    $school = School::factory()->create(['students_gender' => null]);
    $user = createSchoolUser($school);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('organization.type', 'school')
            ->where('organization.id', $school->id)
            ->where('organization.students_gender', null)
            ->has('organization.students_gender_options', 3));
});

test('school organization context omits gender options when students gender is configured', function () {
    $school = School::factory()->create([
        'students_gender' => SchoolStudentsGender::MIXED,
    ]);
    $user = createSchoolUser($school);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('organization.type', 'school')
            ->where('organization.students_gender.id', SchoolStudentsGender::MIXED->value)
            ->missing('organization.students_gender_options'));
});

test('organization context is not shared outside supported dashboards', function () {
    $user = User::factory()->create([
        'scope' => UserScope::ADMINISTRATION,
        'role' => UserRole::MANAGER,
    ]);

    PolicyRegistrar::register(Request::create('/administration/dashboard', 'GET'));

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('organization', null));
});

it('resolves education monitor organization context for the education monitor dashboard', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $request = Request::create('/education-monitor/dashboard', 'GET');
    $request->setUserResolver(fn (?string $guard = null): ?User => $guard === 'education_monitor' ? $user : null);

    expect(app(OrganizationContextManager::class)->resolve($request))->toBe([
        'type' => 'education_monitor',
        'id' => $monitor->id,
        'name' => $monitor->name,
    ]);
});

it('resolves education services office organization context for the education services office dashboard', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    $request = Request::create('/education-services-office/dashboard', 'GET');
    $request->setUserResolver(fn (?string $guard = null): ?User => $guard === 'education_services_office' ? $user : null);

    expect(app(OrganizationContextManager::class)->resolve($request))->toBe([
        'type' => 'education_services_office',
        'id' => $office->id,
        'name' => $office->name,
    ]);
});

it('returns null when the authenticated user belongs to a different organization type', function () {
    $school = School::factory()->create();
    $user = createSchoolUser($school);

    $request = Request::create('/education-monitor/dashboard', 'GET');
    $request->setUserResolver(fn (?string $guard = null): ?User => $guard === 'education_monitor' ? $user : null);

    expect(app(OrganizationContextManager::class)->resolve($request))->toBeNull();
});

it('returns null when no organization context is registered for the dashboard', function () {
    $user = User::factory()->create([
        'scope' => UserScope::ADMINISTRATION,
    ]);

    $request = Request::create('/administration/dashboard', 'GET');
    $request->setUserResolver(fn (?string $guard = null): ?User => $guard === 'administration' ? $user : null);

    expect(app(OrganizationContextManager::class)->resolve($request))->toBeNull();
});
