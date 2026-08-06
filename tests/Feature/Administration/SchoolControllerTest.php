<?php

use App\Enums\GradeLevelEnum;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

function createSchoolAdminUser(): User
{
    $user = User::factory()->create();

    $permissions = [
        'school:view-any',
        'school:view',
        'school:create',
        'school:update',
        'school:delete',
    ];

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

/**
 * Wrap a persisted school in a partial mock so route-model binding resolves it,
 * allowing the instance-level hasAnyRelations() check to be controlled in tests.
 */
function bindSchoolBinding(School $school, bool $hasAnyRelations): School
{
    /** @var School&MockInterface $mock */
    $mock = Mockery::mock($school)->makePartial();
    $mock->shouldReceive('hasAnyRelations')->andReturn($hasAnyRelations);
    $mock->shouldReceive('resolveRouteBinding')->andReturn($mock);

    app()->instance(School::class, $mock);

    return $mock;
}

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

function publicSchoolPayload(EducationMonitor $monitor, array $overrides = []): array
{
    $payload = [
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => null,
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
    PolicyRegistrar::register(Request::create('/administration/schools', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

afterEach(function () {
    app()->forgetInstance(School::class);
});

test('guests are redirected from the schools page', function () {
    $this->get(route('administration.schools.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without school permissions cannot view schools', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.index'))
        ->assertForbidden();
});

test('authenticated users can visit the schools index page', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()
        ->for($monitor, 'monitor')
        ->has(SchoolPeriod::factory(), 'periods')
        ->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/index')
            ->has('schools.data', 1)
            ->where('schools.data.0.uuid', $school->uuid)
            ->where('schools.data.0.monitor.name', $monitor->name)
            ->has('monitors')
            ->has('types')
        );
});

test('authenticated users can filter schools by education monitor', function () {
    $user = createSchoolAdminUser();
    $monitorA = EducationMonitor::factory()->create();
    $monitorB = EducationMonitor::factory()->create();

    School::factory()->for($monitorA, 'monitor')->has(SchoolPeriod::factory(), 'periods')->create(['name' => 'مدرسة أ']);
    School::factory()->for($monitorB, 'monitor')->has(SchoolPeriod::factory(), 'periods')->create(['name' => 'مدرسة ب']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.index', ['filter' => ['education_monitor_id' => $monitorA->id]]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('schools.data', 1)
            ->where('schools.data.0.monitor.name', $monitorA->name)
        );
});

test('authenticated users can visit the create school page', function () {
    $user = createSchoolAdminUser();
    EducationMonitor::factory()->create();
    createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/create')
            ->has('monitors')
            ->has('types')
            ->has('academicPeriods')
            ->has('studentsGender')
            ->has('gradeLevels', 1)
            ->where('schoolPrivateType', SchoolType::PRIVATE->value)
            ->where('schoolDualAcademicPeriod', SchoolAcademicPeriod::DUAL_PERIOD->value)
        );
});

test('authenticated users can store a public single-period school', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $gradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
            'grade_levels' => [$gradeLevel->id],
        ]))
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 1);
    $this->assertDatabaseCount('school_periods', 1);

    $school = School::query()->firstOrFail();
    $schoolPeriod = $school->periods()->firstOrFail();

    expect($schoolPeriod->education_monitor_id)->toBe($monitor->id)
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

test('authenticated users can store separate dual-period schools', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $primaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $payload = [
        'education_monitor_id' => $monitor->id,
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

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 2);
    $this->assertDatabaseCount('school_periods', 2);

    $this->assertDatabaseHas('schools', [
        'education_monitor_id' => $monitor->id,
        'name' => 'مدرسة الصباح',
    ]);
    $this->assertDatabaseHas('schools', [
        'education_monitor_id' => $monitor->id,
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

test('authenticated users can store a dual-period school sharing the same name', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $primaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $payload = [
        'education_monitor_id' => $monitor->id,
        'type' => SchoolType::PUBLIC->value,
        'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
        'is_same_school' => '1',
        'name' => 'مدرسة الوحدة',
        'students_gender_morning' => SchoolStudentsGender::BOYS->value,
        'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
        'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
        'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
        'grade_levels_morning' => [$primaryGradeLevel->id],
        'grade_levels_evening' => [$secondaryGradeLevel->id],
    ];

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), $payload)
        ->assertRedirect();

    $this->assertDatabaseCount('schools', 1);
    $this->assertDatabaseCount('school_periods', 2);

    $this->assertDatabaseHas('schools', [
        'name' => 'مدرسة الوحدة',
    ]);
    $this->assertDatabaseHas('school_periods', [
        'name' => 'مدرسة الوحدة',
        'academic_period' => SchoolAcademicPeriod::MORNING->value,
        'students_gender' => SchoolStudentsGender::BOYS->value,
    ]);
    $this->assertDatabaseHas('school_periods', [
        'name' => 'مدرسة الوحدة',
        'academic_period' => SchoolAcademicPeriod::EVENING->value,
        'students_gender' => SchoolStudentsGender::GIRLS->value,
    ]);

    $morningSchoolPeriod = SchoolPeriod::query()->where('academic_period', SchoolAcademicPeriod::MORNING)->firstOrFail();
    $eveningSchoolPeriod = SchoolPeriod::query()->where('academic_period', SchoolAcademicPeriod::EVENING)->firstOrFail();

    expect($morningSchoolPeriod->school_id)->toBe($eveningSchoolPeriod->school_id);
});

test('dual-period school with shared name requires the single name field', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $primaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
            'type' => SchoolType::PUBLIC->value,
            'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
            'is_same_school' => '1',
            'students_gender_morning' => SchoolStudentsGender::BOYS->value,
            'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
            'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
            'grade_levels_morning' => [$primaryGradeLevel->id],
            'grade_levels_evening' => [$secondaryGradeLevel->id],
        ])
        ->assertSessionHasErrors('name');
});

test('authenticated users can store a single-period school without students gender or grade levels', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
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
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
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
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
            'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'grade_levels' => [$secondaryGradeLevel->id],
        ]))
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_belong_to_educational_stages'),
        ]);
});

test('same-school dual-period schools cannot share grade levels across periods', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $primaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);
    $secondaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::SECONDARY_EDUCATION);

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
            'type' => SchoolType::PUBLIC->value,
            'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
            'is_same_school' => '1',
            'name' => 'مدرسة الوحدة',
            'students_gender_morning' => SchoolStudentsGender::BOYS->value,
            'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
            'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'educational_stages_evening' => [SchoolEducationalStageEnum::SECONDARY_EDUCATION->value],
            'grade_levels_morning' => [$primaryGradeLevel->id, $secondaryGradeLevel->id],
            'grade_levels_evening' => [$secondaryGradeLevel->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels_morning' => __('validation.custom.grade_levels.must_be_unique_across_periods'),
            'grade_levels_evening' => __('validation.custom.grade_levels.must_be_unique_across_periods'),
        ]);
});

test('separate dual-period schools can share grade levels across periods', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $primaryGradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
            'type' => SchoolType::PUBLIC->value,
            'academic_period' => SchoolAcademicPeriod::DUAL_PERIOD->value,
            'name_morning' => 'مدرسة الصباح',
            'name_evening' => 'مدرسة المساء',
            'students_gender_morning' => SchoolStudentsGender::BOYS->value,
            'students_gender_evening' => SchoolStudentsGender::GIRLS->value,
            'educational_stages_morning' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'educational_stages_evening' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'grade_levels_morning' => [$primaryGradeLevel->id],
            'grade_levels_evening' => [$primaryGradeLevel->id],
        ])
        ->assertRedirect();
});

test('education services office is required when the monitor has offices', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
            'education_services_office_id' => null,
        ]))
        ->assertSessionHasErrors('education_services_office_id');
});

test('private school requires company name and branch and building types', function () {
    $user = createSchoolAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.schools.store'), publicSchoolPayload($monitor, [
            'type' => SchoolType::PRIVATE->value,
        ]))
        ->assertSessionHasErrors(['educational_company_name', 'branch_type', 'building_type']);
});

test('authenticated users can visit the show school page', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/show')
            ->where('school.uuid', $school->uuid)
            ->where('school.serial_number', $school->serial_number)
        );
});

test('show school page includes periods with aggregate counts', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->create();
    $schoolPeriod = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $gradeLevel = createGradeLevelForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $schoolPeriod->gradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    Classroom::factory()->for($schoolPeriod)->for($gradeLevel)->create();
    Student::factory()->for($schoolPeriod)->count(2)->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/show')
            ->has('school.periods', 1)
            ->where('school.periods.0.grade_levels_count', 1)
            ->where('school.periods.0.classrooms_count', 1)
            ->where('school.periods.0.students_count', 2)
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.academic_period.id', SchoolAcademicPeriod::MORNING->value)
        );
});

function schoolUpdatePayload(School $school, array $overrides = []): array
{
    return array_merge([
        'education_monitor_id' => $school->education_monitor_id,
        'education_services_office_id' => $school->education_services_office_id,
        'name' => $school->name,
    ], $overrides);
}

test('authenticated users can visit the edit school page', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.schools.edit', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/schools/edit')
            ->where('school.uuid', $school->uuid)
            ->has('monitors')
            ->has('branchTypes')
            ->has('buildingTypes')
        );
});

test('authenticated users can update the school name for a public school', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->create([
        'type' => SchoolType::PUBLIC->value,
        'name' => 'الاسم القديم',
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), schoolUpdatePayload($school, [
            'name' => 'الاسم الجديد',
        ]))
        ->assertRedirect(route('administration.schools.show', ['school' => $school]));

    expect($school->refresh()->name)->toBe('الاسم الجديد');
});

test('authenticated users can update private school specific fields', function () {
    $user = createSchoolAdminUser();
    $school = School::factory()->has(SchoolPeriod::factory(), 'periods')->create([
        'type' => SchoolType::PRIVATE->value,
        'educational_company_name' => 'الشركة القديمة',
        'branch_type' => SchoolBranchType::MAIN->value,
        'building_type' => SchoolBuildingType::SCHOOL->value,
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), schoolUpdatePayload($school, [
            'educational_company_name' => 'الشركة الجديدة',
            'branch_type' => SchoolBranchType::SUB->value,
            'building_type' => SchoolBuildingType::VILLA->value,
        ]))
        ->assertRedirect(route('administration.schools.show', ['school' => $school]));

    $school->refresh();

    expect($school->educational_company_name)->toBe('الشركة الجديدة')
        ->and($school->branch_type)->toBe(SchoolBranchType::SUB)
        ->and($school->building_type)->toBe(SchoolBuildingType::VILLA);
});

test('authenticated users can update school organization and sync related records', function () {
    $user = createSchoolAdminUser();
    $originalMonitor = EducationMonitor::factory()->create();
    $newMonitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($newMonitor, 'monitor')->create();

    $school = School::factory()
        ->for($originalMonitor, 'monitor')
        ->has(SchoolPeriod::factory(), 'periods')
        ->create([
            'type' => SchoolType::PUBLIC->value,
            'name' => 'الاسم القديم',
        ]);

    $schoolPeriod = $school->periods()->firstOrFail();
    $student = Student::factory()->for($schoolPeriod)->create([
        'education_monitor_id' => $originalMonitor->id,
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), schoolUpdatePayload($school, [
            'education_monitor_id' => $newMonitor->id,
            'education_services_office_id' => $office->id,
            'name' => 'الاسم الجديد',
        ]))
        ->assertRedirect(route('administration.schools.show', ['school' => $school]));

    $school->refresh();
    $schoolPeriod->refresh();
    $student->refresh();

    expect($school->education_monitor_id)->toBe($newMonitor->id)
        ->and($school->education_services_office_id)->toBe($office->id)
        ->and($school->name)->toBe('الاسم الجديد')
        ->and($schoolPeriod->education_monitor_id)->toBe($newMonitor->id)
        ->and($schoolPeriod->education_services_office_id)->toBe($office->id)
        ->and($schoolPeriod->name)->toBe('الاسم الجديد')
        ->and($student->education_monitor_id)->toBe($newMonitor->id);
});

test('education services office is required when updating to a monitor with offices', function () {
    $user = createSchoolAdminUser();
    $originalMonitor = EducationMonitor::factory()->create();
    $newMonitor = EducationMonitor::factory()->create();
    EducationServicesOffice::factory()->for($newMonitor, 'monitor')->create();

    $school = School::factory()
        ->for($originalMonitor, 'monitor')
        ->has(SchoolPeriod::factory(), 'periods')
        ->create(['type' => SchoolType::PUBLIC->value]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), schoolUpdatePayload($school, [
            'education_monitor_id' => $newMonitor->id,
            'education_services_office_id' => null,
        ]))
        ->assertSessionHasErrors('education_services_office_id');
});

test('education services office must belong to the selected monitor when updating', function () {
    $user = createSchoolAdminUser();
    $originalMonitor = EducationMonitor::factory()->create();
    $newMonitor = EducationMonitor::factory()->create();
    $otherMonitor = EducationMonitor::factory()->create();
    $foreignOffice = EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $school = School::factory()
        ->for($originalMonitor, 'monitor')
        ->has(SchoolPeriod::factory(), 'periods')
        ->create(['type' => SchoolType::PUBLIC->value]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), schoolUpdatePayload($school, [
            'education_monitor_id' => $newMonitor->id,
            'education_services_office_id' => $foreignOffice->id,
        ]))
        ->assertSessionHasErrors('education_services_office_id');
});

test('updating to a monitor without offices clears education services office on related records', function () {
    $user = createSchoolAdminUser();
    $monitorWithOffice = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitorWithOffice, 'monitor')->create();
    $monitorWithoutOffice = EducationMonitor::factory()->create();

    $school = School::factory()
        ->for($monitorWithOffice, 'monitor')
        ->has(SchoolPeriod::factory(), 'periods')
        ->create([
            'type' => SchoolType::PUBLIC->value,
            'education_services_office_id' => $office->id,
        ]);

    $schoolPeriod = $school->periods()->firstOrFail();

    $this->actingAs($user, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), [
            'education_monitor_id' => $monitorWithoutOffice->id,
            'name' => $school->name,
        ])
        ->assertRedirect(route('administration.schools.show', ['school' => $school]));

    $school->refresh();
    $schoolPeriod->refresh();

    expect($school->education_monitor_id)->toBe($monitorWithoutOffice->id)
        ->and($school->education_services_office_id)->toBeNull()
        ->and($schoolPeriod->education_services_office_id)->toBeNull();
});

test('authenticated users can delete a school without relations', function () {
    $user = createSchoolAdminUser();
    $school = bindSchoolBinding(School::factory()->create(), hasAnyRelations: false);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.schools.destroy', ['school' => $school]))
        ->assertRedirect(route('administration.schools.index'));

    $this->assertSoftDeleted('schools', ['id' => $school->id]);
});

test('schools with relations cannot be deleted', function () {
    $user = createSchoolAdminUser();
    $school = bindSchoolBinding(School::factory()->create(), hasAnyRelations: true);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.schools.destroy', ['school' => $school]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('schools', ['id' => $school->id]);
});
