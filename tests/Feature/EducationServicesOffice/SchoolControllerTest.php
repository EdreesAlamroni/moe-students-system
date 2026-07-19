<?php

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

/**
 * Wrap a persisted school in a partial mock so route-model binding resolves it,
 * allowing the instance-level hasAnyRelations() check to be controlled in tests.
 */
function bindEducationServicesOfficeSchoolBinding(School $school, bool $hasAnyRelations): School
{
    /** @var School&MockInterface $mock */
    $mock = Mockery::mock($school)->makePartial();
    $mock->shouldReceive('hasAnyRelations')->andReturn($hasAnyRelations);
    $mock->shouldReceive('resolveRouteBinding')->andReturn($mock);

    app()->instance(School::class, $mock);

    return $mock;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationServicesOfficeSchoolManager(EducationServicesOffice $office, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ], $attributes));

    foreach (['school:view-any', 'school:view', 'school:create', 'school:update', 'school:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_SERVICES_OFFICE->value);
    }

    $user->givePermissionTo([
        'school:view-any',
        'school:view',
        'school:create',
        'school:update',
        'school:delete',
    ]);

    return $user;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function educationServicesOfficePublicSchoolPayload(array $overrides = []): array
{
    return array_merge([
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'name' => 'مدرسة الشهداء',
        'students_gender' => SchoolStudentsGender::MIXED->value,
        'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
    ], $overrides);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-services-office/schools', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the schools page', function () {
    $this->get(route('education-services-office.schools.index'))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without school permissions cannot view schools', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.index'))
        ->assertForbidden();
});

test('authenticated users can visit the schools index page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $otherOffice = EducationServicesOffice::factory()->create();
    School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/schools/index')
            ->has('schools.data', 1)
            ->where('schools.data.0.uuid', $school->uuid)
            ->has('types')
            ->where('filter', [])
        );
});

test('authenticated users can filter schools by type and name', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create([
        'name' => 'مدرسة أ',
        'type' => SchoolType::PUBLIC->value,
    ]);
    School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create([
        'name' => 'مدرسة ب',
        'type' => SchoolType::PRIVATE->value,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.index', ['filter' => ['type' => SchoolType::PUBLIC->value]]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('schools.data', 1)
            ->where('schools.data.0.name', 'مدرسة أ')
        );
});

test('authenticated users can visit the create school page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/schools/create')
            ->has('types')
            ->has('academicPeriods')
            ->has('studentsGender')
            ->where('schoolPrivateType', SchoolType::PRIVATE->value)
            ->where('schoolDualAcademicPeriod', SchoolAcademicPeriod::DUAL_PERIOD->value)
        );
});

test('authenticated users can store a public single-period school', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), educationServicesOfficePublicSchoolPayload())
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 1);

    $school = School::query()->firstOrFail();

    expect($school->education_monitor_id)->toBe($office->education_monitor_id)
        ->and($school->education_services_office_id)->toBe($office->id)
        ->and($school->type)->toBe(SchoolType::PUBLIC)
        ->and($school->academic_period)->toBe(SchoolAcademicPeriod::MORNING);

    $this->assertDatabaseHas('school_educational_stages', [
        'school_id' => $school->id,
        'stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION->value,
    ]);
});

test('store associates the school with the current office even if another office id is submitted', function () {
    $office = EducationServicesOffice::factory()->create();
    $otherOffice = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), educationServicesOfficePublicSchoolPayload([
            'education_services_office_id' => $otherOffice->id,
            'education_monitor_id' => $otherOffice->education_monitor_id,
        ]))
        ->assertRedirect();

    $school = School::query()->firstOrFail();

    expect($school->education_services_office_id)->toBe($office->id)
        ->and($school->education_monitor_id)->toBe($office->education_monitor_id);
});

test('authenticated users can store a dual-period school as two records', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $payload = [
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
        'name_morning' => 'مدرسة الصباح',
        'name_evening' => 'مدرسة المساء',
        'students_gender_morning' => SchoolStudentsGender::BOYS->value,
        'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
        'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
        'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
    ];

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 2);

    $this->assertDatabaseHas('schools', [
        'education_services_office_id' => $office->id,
        'name' => 'مدرسة الصباح',
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'students_gender' => SchoolStudentsGender::BOYS->value,
    ]);
    $this->assertDatabaseHas('schools', [
        'education_services_office_id' => $office->id,
        'name' => 'مدرسة المساء',
        'academic_period' => SchoolAcademicPeriod::EVENING->value,
        'students_gender' => SchoolStudentsGender::GIRLS->value,
    ]);

    expect(SchoolEducationalStage::query()->count())->toBe(2);
});

test('private school requires company name and branch and building types', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), educationServicesOfficePublicSchoolPayload([
            'type' => SchoolType::PRIVATE->value,
        ]))
        ->assertSessionHasErrors(['educational_company_name', 'branch_type', 'building_type']);
});

test('authenticated users can visit the show school page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/schools/show')
            ->where('school.uuid', $school->uuid)
            ->where('school.serial_number', $school->serial_number)
            ->where('school.office.name', $office->name)
        );
});

test('users cannot view schools from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.show', ['school' => $school]))
        ->assertForbidden();
});

test('authenticated users can visit the edit school page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.edit', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/schools/edit')
            ->where('school.uuid', $school->uuid)
            ->has('branchTypes')
            ->has('buildingTypes')
        );
});

test('users cannot edit schools from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.edit', ['school' => $school]))
        ->assertForbidden();
});

test('authenticated users can update the school name for a public school', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create([
        'type' => SchoolType::PUBLIC->value,
        'name' => 'الاسم القديم',
    ]);

    $this->actingAs($user, 'education_services_office')
        ->put(route('education-services-office.schools.update', ['school' => $school]), [
            'name' => 'الاسم الجديد',
        ])
        ->assertRedirect(route('education-services-office.schools.show', ['school' => $school]));

    expect($school->refresh()->name)->toBe('الاسم الجديد');
});

test('authenticated users can update private school specific fields', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create([
        'type' => SchoolType::PRIVATE->value,
        'educational_company_name' => 'الشركة القديمة',
        'branch_type' => SchoolBranchType::MAIN->value,
        'building_type' => SchoolBuildingType::SCHOOL->value,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->put(route('education-services-office.schools.update', ['school' => $school]), [
            'name' => $school->name,
            'educational_company_name' => 'الشركة الجديدة',
            'branch_type' => SchoolBranchType::SUB->value,
            'building_type' => SchoolBuildingType::VILLA->value,
        ])
        ->assertRedirect(route('education-services-office.schools.show', ['school' => $school]));

    $school->refresh();

    expect($school->educational_company_name)->toBe('الشركة الجديدة')
        ->and($school->branch_type)->toBe(SchoolBranchType::SUB)
        ->and($school->building_type)->toBe(SchoolBuildingType::VILLA)
        ->and($school->education_services_office_id)->toBe($office->id);
});

test('authenticated users can delete a school without relations', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = bindEducationServicesOfficeSchoolBinding(
        School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create(),
        hasAnyRelations: false,
    );

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.schools.destroy', ['school' => $school]))
        ->assertRedirect(route('education-services-office.schools.index'));

    $this->assertSoftDeleted('schools', ['id' => $school->id]);
});

test('schools with relations cannot be deleted', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = bindEducationServicesOfficeSchoolBinding(
        School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create(),
        hasAnyRelations: true,
    );

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.schools.destroy', ['school' => $school]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('schools', ['id' => $school->id]);
});

test('users cannot delete schools from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = bindEducationServicesOfficeSchoolBinding(
        School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create(),
        hasAnyRelations: false,
    );

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.schools.destroy', ['school' => $school]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('schools', ['id' => $school->id]);
});
