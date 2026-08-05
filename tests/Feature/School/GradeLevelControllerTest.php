<?php

use App\Enums\GradeLevelEnum;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
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
            ->has('availableGradeLevels', 0)
            ->where('filter', [])
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
