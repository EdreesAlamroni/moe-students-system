<?php

use App\Enums\DayOfWeek;
use App\Enums\GradeLevelEnum;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createSchoolGradeLevelManager(
    SchoolPeriod $schoolPeriod,
    array $attributes = [],
    array $permissions = ['grade-level:view-any', 'grade-level:view'],
): User {
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ], $attributes));

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

function createSchoolGradeLevelCreator(SchoolPeriod $schoolPeriod): User
{
    return createSchoolGradeLevelManager($schoolPeriod, [], [
        'grade-level:view-any',
        'grade-level:view',
        'grade-level:create',
    ]);
}

function createSchoolGradeLevelTransferrer(SchoolPeriod $schoolPeriod): User
{
    return createSchoolGradeLevelManager($schoolPeriod, [], [
        'grade-level:view-any',
        'grade-level:view',
        'grade-level:transfer',
    ]);
}

/**
 * @return array{0: SchoolPeriod, 1: SchoolPeriod}
 */
function createSiblingSchoolPeriods(SchoolEducationalStageEnum $stage): array
{
    $school = School::factory()->create();

    $morningSchoolPeriod = createSchoolWithStage($stage, [
        'school_id' => $school->id,
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);

    $eveningSchoolPeriod = createSchoolWithStage($stage, [
        'school_id' => $school->id,
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    return [$morningSchoolPeriod, $eveningSchoolPeriod];
}

/**
 * Create the grade levels belonging to the given educational stage, ordered as they are expected.
 */
function createGradeLevelsForStage(SchoolEducationalStageEnum $stage): Collection
{
    return GradeLevelEnum::filteredByStage($stage)
        ->map(fn (GradeLevelEnum $grade): GradeLevel => GradeLevel::factory()->create([
            'code' => $grade->value,
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ]));
}

function attachGradeLevelsToSchool(SchoolPeriod $schoolPeriod, GradeLevel ...$gradeLevels): void
{
    foreach ($gradeLevels as $gradeLevel) {
        $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
            'academic_year_id' => AcademicYear::currentId(),
        ]);
    }
}

function createSchoolWithStage(SchoolEducationalStageEnum $stage, array $attributes = []): SchoolPeriod
{
    $schoolPeriod = SchoolPeriod::factory()->create($attributes);

    SchoolEducationalStage::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'academic_year_id' => AcademicYear::currentId(),
        'stage' => $stage,
    ]);

    return $schoolPeriod;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/grade-levels', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access school grade levels index', function () {
    $this->get(route('school.grade-levels.index'))
        ->assertRedirect(route('school.login'));
});

test('users without permission cannot access school grade levels index', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.index'))
        ->assertForbidden();
});

test('authenticated school users can visit the grade levels index', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolGradeLevelManager($schoolPeriod);
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $otherGradeLevel = GradeLevel::factory()->create(['name' => 'صف مدرسة أخرى']);

    $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $otherSchoolPeriod->allGradeLevels()->attach($otherGradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/grade-levels/index')
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.uuid', $gradeLevel->uuid)
            ->where('gradeLevels.0.can.view', true)
            ->where('gradeLevels.0.canAny', true)
            ->has('educationalStages')
            ->where('can.create', false)
            ->where('can.transfer', false)
            ->has('availableGradeLevels', 0)
            ->has('transferableGradeLevels', 0)
            ->where('siblingPeriod', null)
            ->where('filter', [])
        );
});

test('the grade levels index exposes transfer data when a sibling period exists', function () {
    [$morningSchoolPeriod, $eveningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($morningSchoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('can.transfer', true)
            ->where('siblingPeriod.id', $eveningSchoolPeriod->id)
            ->where('siblingPeriod.name', $eveningSchoolPeriod->display_name)
            ->has('transferableGradeLevels', 1)
            ->where('transferableGradeLevels.0.id', $gradeLevels->first()->id)
        );
});

test('the grade levels index offers the grade levels that can still be added', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($schoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($schoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('can.create', true)
            ->has('availableGradeLevels', 1)
            ->where('availableGradeLevels.0.id', $gradeLevels->last()->id)
            ->where('availableGradeLevels.0.name', $gradeLevels->last()->name)
        );
});

test('guests cannot add grade levels to the school', function () {
    $gradeLevel = GradeLevel::factory()->create();

    $this->post(route('school.grade-levels.store'), ['grade_levels' => [$gradeLevel->id]])
        ->assertRedirect(route('school.login'));
});

test('school users without the create permission cannot add grade levels', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelManager($schoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), ['grade_levels' => [$gradeLevels->first()->id]])
        ->assertForbidden();

    $this->assertDatabaseCount('grade_level_school_period', 0);
});

test('school users can add the missing grade levels for the current academic year', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($schoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [
            'grade_levels' => $gradeLevels->pluck('id')->all(),
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($schoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toEqualCanonicalizing($gradeLevels->pluck('id')->all())
        ->and($schoolPeriod->availableGradeLevels())->toBeEmpty();
});

test('school users can add only the remaining grade levels when some are already configured', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($schoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($schoolPeriod, $gradeLevels->first());

    expect($schoolPeriod->availableGradeLevels()->pluck('id')->all())->toBe([$gradeLevels->last()->id]);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [
            'grade_levels' => [$gradeLevels->last()->id],
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($schoolPeriod->gradeLevels()->count())->toBe($gradeLevels->count());
});

test('grade levels already added to the school cannot be added again', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($schoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($schoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [
            'grade_levels' => [$gradeLevels->first()->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_be_available_for_school'),
        ]);

    expect($schoolPeriod->gradeLevels()->count())->toBe(1);
});

test('grade levels outside the school educational stages cannot be added', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($schoolPeriod);
    $primaryGradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [
            'grade_levels' => [$primaryGradeLevels->first()->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_be_available_for_school'),
        ]);

    $this->assertDatabaseCount('grade_level_school_period', 0);
});

test('adding grade levels requires at least one existing grade level', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($schoolPeriod);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [])
        ->assertSessionHasErrors('grade_levels');

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), ['grade_levels' => [-1]])
        ->assertSessionHasErrors('grade_levels.0');

    $this->assertDatabaseCount('grade_level_school_period', 0);
});

test('grade levels taken by the other period of the same school are not available', function () {
    $school = School::factory()->create();

    $morningSchoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN, [
        'school_id' => $school->id,
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);

    $eveningSchoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN, [
        'school_id' => $school->id,
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($morningSchoolPeriod);

    attachGradeLevelsToSchool($eveningSchoolPeriod, $gradeLevels->first());

    expect($morningSchoolPeriod->availableGradeLevels()->pluck('id')->all())->toBe([$gradeLevels->last()->id]);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [
            'grade_levels' => [$gradeLevels->first()->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_be_available_for_school'),
        ]);

    expect($morningSchoolPeriod->gradeLevels()->count())->toBe(0);
});

test('guests cannot access school grade level show page', function () {
    $gradeLevel = GradeLevel::factory()->create();

    $this->get(route('school.grade-levels.show', ['gradeLevel' => $gradeLevel]))
        ->assertRedirect(route('school.login'));
});

test('users without permission cannot access school grade level show page', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);
    $gradeLevel = GradeLevel::factory()->create();

    attachGradeLevelsToSchool($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.show', ['gradeLevel' => $gradeLevel]))
        ->assertForbidden();
});

test('authenticated school users can visit the grade level show page', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolGradeLevelManager($schoolPeriod);
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);

    attachGradeLevelsToSchool($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.show', ['gradeLevel' => $gradeLevel]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/grade-levels/show')
            ->where('gradeLevel.uuid', $gradeLevel->uuid)
            ->where('gradeLevel.name', 'الصف الأول')
            ->has('gradeLevel.educational_stage')
            ->where('gradeLevel.students_count', 0)
            ->where('gradeLevel.classrooms_count', 0)
            ->where('can.delete', false)
        );
});

test('school users cannot view grade levels from another school', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolGradeLevelManager($schoolPeriod);
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();

    attachGradeLevelsToSchool($otherSchoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.show', ['gradeLevel' => $gradeLevel]))
        ->assertForbidden();
});

test('school users cannot view grade levels assigned to a previous academic year', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolGradeLevelManager($schoolPeriod);
    $gradeLevel = GradeLevel::factory()->create();

    $previousAcademicYear = AcademicYear::query()->create([
        'name' => '2023-2024',
        'start_date' => now()->subYear()->startOfYear(),
        'end_date' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $previousAcademicYear->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.show', ['gradeLevel' => $gradeLevel]))
        ->assertForbidden();
});

test('separate dual-period schools can add the same grade levels', function () {
    $morningSchoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN, [
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);

    $eveningSchoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN, [
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelCreator($morningSchoolPeriod);

    attachGradeLevelsToSchool($eveningSchoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.store'), [
            'grade_levels' => [$gradeLevels->first()->id],
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($morningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())->toBe([$gradeLevels->first()->id]);
});

test('guests cannot transfer grade levels', function () {
    $gradeLevel = GradeLevel::factory()->create();

    $this->post(route('school.grade-levels.transfers.store'), ['grade_levels' => [$gradeLevel->id]])
        ->assertRedirect(route('school.login'));
});

test('school users without the transfer permission cannot transfer grade levels', function () {
    [$morningSchoolPeriod, $eveningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelManager($morningSchoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($morningSchoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => [$gradeLevels->first()->id],
        ])
        ->assertForbidden();

    expect($morningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toBe([$gradeLevels->first()->id])
        ->and($eveningSchoolPeriod->gradeLevels()->count())->toBe(0);
});

test('school users can transfer grade levels to the sibling period', function () {
    [$morningSchoolPeriod, $eveningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($morningSchoolPeriod, ...$gradeLevels->all());

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => $gradeLevels->pluck('id')->all(),
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($morningSchoolPeriod->gradeLevels()->count())->toBe(0)
        ->and($eveningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toEqualCanonicalizing($gradeLevels->pluck('id')->all());
});

test('transferring grade levels moves related period-scoped records to the sibling period', function () {
    [$morningSchoolPeriod, $eveningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $transferredGradeLevel = $gradeLevels->first();
    $retainedGradeLevel = $gradeLevels->last();

    attachGradeLevelsToSchool($morningSchoolPeriod, $transferredGradeLevel, $retainedGradeLevel);

    $transferredClassroom = Classroom::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $morningSchoolPeriod->id,
        'grade_level_id' => $transferredGradeLevel->id,
        'name' => 'أ',
    ]);

    $retainedClassroom = Classroom::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $morningSchoolPeriod->id,
        'grade_level_id' => $retainedGradeLevel->id,
        'name' => 'ب',
    ]);

    $morningClassPeriod = ClassPeriod::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'academic_period' => SchoolAcademicPeriod::MORNING,
        'order' => 1,
        'name' => 'الحصة 1',
    ]);

    $transferredSchedule = ClassSchedule::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $morningSchoolPeriod->id,
        'classroom_id' => $transferredClassroom->id,
        'class_period_id' => $morningClassPeriod->id,
        'day_of_week' => DayOfWeek::SUNDAY,
    ]);

    $retainedSchedule = ClassSchedule::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $morningSchoolPeriod->id,
        'classroom_id' => $retainedClassroom->id,
        'class_period_id' => $morningClassPeriod->id,
        'day_of_week' => DayOfWeek::SUNDAY,
    ]);

    $transferredStudent = Student::factory()->create([
        'school_period_id' => $morningSchoolPeriod->id,
        'education_monitor_id' => $morningSchoolPeriod->education_monitor_id,
    ]);

    $retainedStudent = Student::factory()->create([
        'school_period_id' => $morningSchoolPeriod->id,
        'education_monitor_id' => $morningSchoolPeriod->education_monitor_id,
    ]);

    $transferredEnrollment = StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $morningSchoolPeriod->id,
        'grade_level_id' => $transferredGradeLevel->id,
        'classroom_id' => $transferredClassroom->id,
        'student_id' => $transferredStudent->id,
    ]);

    $retainedEnrollment = StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $morningSchoolPeriod->id,
        'grade_level_id' => $retainedGradeLevel->id,
        'classroom_id' => $retainedClassroom->id,
        'student_id' => $retainedStudent->id,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => [$transferredGradeLevel->id],
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($morningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toBe([$retainedGradeLevel->id])
        ->and($eveningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toBe([$transferredGradeLevel->id])
        ->and($transferredClassroom->fresh()->school_period_id)->toBe($eveningSchoolPeriod->id)
        ->and($retainedClassroom->fresh()->school_period_id)->toBe($morningSchoolPeriod->id)
        ->and(ClassSchedule::query()->whereKey($transferredSchedule->id)->exists())->toBeFalse()
        ->and($retainedSchedule->fresh()->school_period_id)->toBe($morningSchoolPeriod->id)
        ->and($transferredEnrollment->fresh()->school_period_id)->toBe($eveningSchoolPeriod->id)
        ->and($retainedEnrollment->fresh()->school_period_id)->toBe($morningSchoolPeriod->id)
        ->and($transferredStudent->fresh()->school_period_id)->toBe($eveningSchoolPeriod->id)
        ->and($retainedStudent->fresh()->school_period_id)->toBe($morningSchoolPeriod->id);
});

test('transferring grade levels creates missing educational stages on the sibling period', function () {
    $school = School::factory()->create();

    $morningSchoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN, [
        'school_id' => $school->id,
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);

    SchoolEducationalStage::factory()->create([
        'school_period_id' => $morningSchoolPeriod->id,
        'academic_year_id' => AcademicYear::currentId(),
        'stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION,
    ]);

    $eveningSchoolPeriod = SchoolPeriod::factory()->create([
        'school_id' => $school->id,
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    SchoolEducationalStage::factory()->create([
        'school_period_id' => $eveningSchoolPeriod->id,
        'academic_year_id' => AcademicYear::currentId(),
        'stage' => SchoolEducationalStageEnum::KINDERGARTEN,
    ]);

    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);
    $kindergartenGradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $primaryGradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::PRIMARY_EDUCATION);

    attachGradeLevelsToSchool(
        $morningSchoolPeriod,
        $kindergartenGradeLevels->first(),
        $primaryGradeLevels->first(),
    );

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => [
                $kindergartenGradeLevels->first()->id,
                $primaryGradeLevels->first()->id,
            ],
        ])
        ->assertRedirect(route('school.dashboard'));

    expect($eveningSchoolPeriod->educationalStages()->pluck('stage')->map->value->all())
        ->toEqualCanonicalizing([
            SchoolEducationalStageEnum::KINDERGARTEN->value,
            SchoolEducationalStageEnum::PRIMARY_EDUCATION->value,
        ])
        ->and($eveningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toEqualCanonicalizing([
            $kindergartenGradeLevels->first()->id,
            $primaryGradeLevels->first()->id,
        ])
        ->and($morningSchoolPeriod->gradeLevels()->count())->toBe(0);
});

test('grade levels that do not belong to the current period cannot be transferred', function () {
    [$morningSchoolPeriod, $eveningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($eveningSchoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => [$gradeLevels->first()->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_belong_to_current_period'),
        ]);

    expect($eveningSchoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toBe([$gradeLevels->first()->id])
        ->and($morningSchoolPeriod->gradeLevels()->count())->toBe(0);
});

test('grade levels cannot be transferred when the school period has no sibling', function () {
    $schoolPeriod = createSchoolWithStage(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($schoolPeriod);
    $gradeLevels = createGradeLevelsForStage(SchoolEducationalStageEnum::KINDERGARTEN);

    attachGradeLevelsToSchool($schoolPeriod, $gradeLevels->first());

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => [$gradeLevels->first()->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_have_sibling_period'),
        ]);

    expect($schoolPeriod->gradeLevels()->pluck('grade_levels.id')->all())
        ->toBe([$gradeLevels->first()->id]);
});

test('transferring grade levels requires at least one existing grade level', function () {
    [$morningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [])
        ->assertSessionHasErrors('grade_levels');

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), ['grade_levels' => [-1]])
        ->assertSessionHasErrors('grade_levels.0');
});

test('grade levels already present on the destination period cannot be transferred', function () {
    [$morningSchoolPeriod, $eveningSchoolPeriod] = createSiblingSchoolPeriods(SchoolEducationalStageEnum::KINDERGARTEN);
    $user = createSchoolGradeLevelTransferrer($morningSchoolPeriod);
    $gradeLevel = GradeLevel::factory()->create([
        'educational_stage' => SchoolEducationalStageEnum::KINDERGARTEN,
    ]);

    attachGradeLevelsToSchool($morningSchoolPeriod, $gradeLevel);
    attachGradeLevelsToSchool($eveningSchoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->from(route('school.dashboard'))
        ->post(route('school.grade-levels.transfers.store'), [
            'grade_levels' => [$gradeLevel->id],
        ])
        ->assertSessionHasErrors([
            'grade_levels' => __('validation.custom.grade_levels.must_not_exist_in_destination_period'),
        ]);
});
