<?php

use App\Enums\SchoolEducationalStageEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createSchoolClassroomManager(School $school, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ], $attributes));

    foreach ([
        'classroom:view-any',
        'classroom:view',
        'classroom:create',
        'classroom:update',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'classroom:view-any',
        'classroom:view',
        'classroom:create',
        'classroom:update',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/classrooms', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access school classrooms index', function () {
    $this->get(route('school.classrooms.index'))
        ->assertRedirect(route('school.login'));
});

test('users without permission cannot access school classrooms index', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.classrooms.index'))
        ->assertForbidden();
});

test('authenticated school users can visit the classrooms index', function () {
    $school = School::factory()->create();
    $user = createSchoolClassroomManager($school);
    $gradeLevel = GradeLevel::factory()->create([
        'code' => 'kg_1',
        'name' => 'روضة - المستوى الأول',
        'educational_stage' => SchoolEducationalStageEnum::KINDERGARTEN,
        'order' => 1,
    ]);
    $otherSchool = School::factory()->create();
    $otherGradeLevel = GradeLevel::factory()->create([
        'code' => 'grade_1',
        'name' => 'الصف الأول',
        'educational_stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION,
        'order' => 3,
    ]);

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    SchoolEducationalStage::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => AcademicYear::currentId(),
        'stage' => SchoolEducationalStageEnum::KINDERGARTEN,
    ]);

    $classroom = Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    Classroom::factory()->create([
        'school_id' => $otherSchool->id,
        'grade_level_id' => $otherGradeLevel->id,
        'name' => '2',
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.classrooms.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/classrooms/index')
            ->has('classrooms.data', 1)
            ->where('classrooms.data.0.uuid', $classroom->uuid)
            ->where('classrooms.data.0.name', '1')
            ->has('gradeLevels')
            ->has('classroomNames', 12)
            ->where('filter', [])
        );
});

test('authenticated school users can create a classroom', function () {
    $school = School::factory()->create();
    $user = createSchoolClassroomManager($school);
    $gradeLevel = GradeLevel::factory()->create([
        'code' => 'kg_1',
        'name' => 'روضة - المستوى الأول',
        'educational_stage' => SchoolEducationalStageEnum::KINDERGARTEN,
        'order' => 1,
    ]);

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    SchoolEducationalStage::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => AcademicYear::currentId(),
        'stage' => SchoolEducationalStageEnum::KINDERGARTEN,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.classrooms.store'), [
            'educational_stage' => SchoolEducationalStageEnum::KINDERGARTEN->value,
            'grade_level_id' => $gradeLevel->id,
            'name' => '1',
            'capacity' => 30,
        ])
        ->assertRedirect(route('school.classrooms.show', Classroom::query()->first()));

    expect(Classroom::query()->count())->toBe(1)
        ->and(Classroom::query()->value('name'))->toBe('1')
        ->and(Classroom::query()->value('capacity'))->toBe(30);
});
