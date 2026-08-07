<?php

use App\Enums\GradeLevelEnum;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

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

if (! function_exists('createGradeLevelForStage')) {
    function createGradeLevelForStage(SchoolEducationalStageEnum $stage): GradeLevel
    {
        $grade = collect(GradeLevelEnum::cases())
            ->first(fn (GradeLevelEnum $case): bool => $case->stage() === $stage);

        return GradeLevel::factory()->create([
            'code' => $grade->value,
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ]);
    }
}

function educationServicesOfficePublicSchoolPayload(array $overrides = []): array
{
    $payload = [
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'name' => 'مدرسة الشهداء',
        'students_gender' => SchoolStudentsGender::MIXED->value,
        'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
    ];

    if (! array_key_exists('grade_levels', $overrides)) {
        $payload['grade_levels'] = [
            createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION)->id,
        ];
    }

    return array_merge($payload, $overrides);
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
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $otherOffice = EducationServicesOffice::factory()->create();
    School::factory()->has(SchoolPeriod::factory(), 'periods')->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

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

    School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create([
        'name' => 'مدرسة أ',
        'type' => SchoolType::PUBLIC->value,
    ]);
    School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create([
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
            ->has('gradeLevels')
            ->where('schoolPrivateType', SchoolType::PRIVATE->value)
            ->where('schoolDualAcademicPeriod', SchoolAcademicPeriod::DUAL_PERIOD->value)
        );
});

test('authenticated users can store a public single-period school', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $gradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), educationServicesOfficePublicSchoolPayload([
            'grade_levels' => [$gradeLevel->id],
        ]))
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 1);
    $this->assertDatabaseCount('school_periods', 1);

    $school = School::query()->firstOrFail();
    $schoolPeriod = $school->periods()->firstOrFail();

    expect($schoolPeriod->education_monitor_id)->toBe($office->education_monitor_id)
        ->and($schoolPeriod->education_services_office_id)->toBe($office->id)
        ->and($school->type)->toBe(SchoolType::PUBLIC)
        ->and($schoolPeriod->academic_period)->toBe(SchoolAcademicPeriod::MORNING);

    $this->assertDatabaseHas('school_educational_stages', [
        'school_period_id' => $schoolPeriod->id,
        'stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION->value,
    ]);

    $this->assertDatabaseHas('grade_level_school_period', [
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => AcademicYear::currentId(),
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

test('authenticated users can store separate dual-period schools', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $primaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $payload = [
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
        'name_morning' => 'مدرسة الصباح',
        'name_evening' => 'مدرسة المساء',
        'students_gender_morning' => SchoolStudentsGender::BOYS->value,
        'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
        'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
        'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
        'grade_levels_morning' => [$primaryGradeLevel->id],
        'grade_levels_evening' => [$secondaryGradeLevel->id],
    ];

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 2);
    $this->assertDatabaseCount('school_periods', 2);

    $this->assertDatabaseHas('schools', [
        'education_services_office_id' => $office->id,
        'name' => 'مدرسة الصباح',
    ]);
    $this->assertDatabaseHas('schools', [
        'education_services_office_id' => $office->id,
        'name' => 'مدرسة المساء',
    ]);
    $this->assertDatabaseHas('school_periods', [
        'name' => 'مدرسة الصباح',
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'students_gender' => SchoolStudentsGender::BOYS->value,
    ]);
    $this->assertDatabaseHas('school_periods', [
        'name' => 'مدرسة المساء',
        'academic_period' => SchoolAcademicPeriod::EVENING->value,
        'students_gender' => SchoolStudentsGender::GIRLS->value,
    ]);

    expect(SchoolEducationalStage::query()->count())->toBe(2);

    $morningSchoolPeriod = SchoolPeriod::query()->where('academic_period', SchoolAcademicPeriod::MORNING)->firstOrFail();
    $eveningSchoolPeriod = SchoolPeriod::query()->where('academic_period', SchoolAcademicPeriod::EVENING)->firstOrFail();

    expect($morningSchoolPeriod->school_id)->not->toBe($eveningSchoolPeriod->school_id);

    $this->assertDatabaseHas('grade_level_school_period', [
        'school_period_id' => $morningSchoolPeriod->id,
        'grade_level_id' => $primaryGradeLevel->id,
    ]);
    $this->assertDatabaseHas('grade_level_school_period', [
        'school_period_id' => $eveningSchoolPeriod->id,
        'grade_level_id' => $secondaryGradeLevel->id,
    ]);
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

test('authenticated users can store a single-period school without students gender or grade levels', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), educationServicesOfficePublicSchoolPayload([
            'students_gender' => null,
            'grade_levels' => null,
        ]))
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 1);
    $this->assertDatabaseCount('school_periods', 1);

    $schoolPeriod = SchoolPeriod::query()->firstOrFail();

    expect($schoolPeriod->students_gender)->toBeNull()
        ->and($schoolPeriod->gradeLevels()->count())->toBe(0);

    $this->assertDatabaseHas('school_educational_stages', [
        'school_period_id' => $schoolPeriod->id,
        'stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION->value,
    ]);
});

test('authenticated users can store a dual-period school without students gender or grade levels', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), [
            'type' => SchoolType::PUBLIC->value,
            'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
            'name_morning' => 'مدرسة الصباح',
            'name_evening' => 'مدرسة المساء',
            'students_gender_morning' => null,
            'students_gender_evening' => null,
            'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
            'grade_levels_morning' => null,
            'grade_levels_evening' => null,
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 2);
    $this->assertDatabaseCount('school_periods', 2);

    $morningSchoolPeriod = SchoolPeriod::query()->where('academic_period', SchoolAcademicPeriod::MORNING)->firstOrFail();
    $eveningSchoolPeriod = SchoolPeriod::query()->where('academic_period', SchoolAcademicPeriod::EVENING)->firstOrFail();

    expect($morningSchoolPeriod->students_gender)->toBeNull()
        ->and($eveningSchoolPeriod->students_gender)->toBeNull()
        ->and($morningSchoolPeriod->gradeLevels()->count())->toBe(0)
        ->and($eveningSchoolPeriod->gradeLevels()->count())->toBe(0);

    expect(SchoolEducationalStage::query()->count())->toBe(2);
});

test('selected grade levels must belong to the selected educational stages', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.schools.store'), educationServicesOfficePublicSchoolPayload([
            'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'grade_levels' => [$secondaryGradeLevel->id],
        ]))
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_belong_to_educational_stages'),
        ]);
});

test('authenticated users can visit the show school page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/schools/show')
            ->where('school.uuid', $school->uuid)
            ->where('school.number', $school->number)
            ->where('school.office.name', $office->name)
        );
});

test('show school page includes periods and grade levels with aggregate counts', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->for($office->monitor, 'monitor')->for($office, 'office')->create();
    $schoolPeriod = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $gradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $schoolPeriod->gradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    Classroom::factory()->for($schoolPeriod)->for($gradeLevel)->create();
    Student::factory()->for($schoolPeriod)->count(2)->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/schools/show')
            ->has('school.periods', 1)
            ->where('school.periods.0.grade_levels_count', 1)
            ->where('school.periods.0.classrooms_count', 1)
            ->where('school.periods.0.students_count', 2)
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.academic_period.id', SchoolAcademicPeriod::MORNING->value)
        );
});

test('users cannot view schools from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.show', ['school' => $school]))
        ->assertForbidden();
});

test('authenticated users can visit the edit school page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create();

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
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office')->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.schools.edit', ['school' => $school]))
        ->assertForbidden();
});

test('authenticated users can update the school name for a public school', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create([
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
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->for($office->monitor, 'monitor')->for($office, 'office')->create([
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

test('authenticated users can delete a school without assigned students', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()
        ->for($office->monitor, 'monitor')
        ->for($office, 'office')
        ->create();
    $morningPeriod = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $eveningPeriod = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.schools.destroy', ['school' => $school]))
        ->assertRedirect(route('education-services-office.schools.index'));

    $this->assertSoftDeleted('schools', ['id' => $school->id]);
    $this->assertSoftDeleted('school_periods', ['id' => $morningPeriod->id]);
    $this->assertSoftDeleted('school_periods', ['id' => $eveningPeriod->id]);
});

test('schools with assigned students cannot be deleted', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $school = School::factory()
        ->has(SchoolPeriod::factory(), 'periods')
        ->for($office->monitor, 'monitor')
        ->for($office, 'office')
        ->create();
    Student::factory()->for($school->periods->first())->create();

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.schools.destroy', ['school' => $school]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('schools', ['id' => $school->id]);
    $this->assertNotSoftDeleted('school_periods', ['id' => $school->periods->first()->id]);
});

test('users cannot delete schools from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeSchoolManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $school = School::factory()
        ->has(SchoolPeriod::factory(), 'periods')
        ->for($otherOffice->monitor, 'monitor')
        ->for($otherOffice, 'office')
        ->create();

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.schools.destroy', ['school' => $school]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('schools', ['id' => $school->id]);
});
