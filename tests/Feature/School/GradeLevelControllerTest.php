<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createSchoolGradeLevelManager(School $school, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ], $attributes));

    foreach (['grade-level:view-any', 'grade-level:view'] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'grade-level:view-any',
        'grade-level:view',
    ]);

    return $user;
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
    $school = School::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.index'))
        ->assertForbidden();
});

test('authenticated school users can visit the grade levels index', function () {
    $school = School::factory()->create();
    $user = createSchoolGradeLevelManager($school);
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $otherSchool = School::factory()->create();
    $otherGradeLevel = GradeLevel::factory()->create(['name' => 'صف مدرسة أخرى']);

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $otherSchool->allGradeLevels()->attach($otherGradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.grade-levels.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/grade-levels/index')
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.uuid', $gradeLevel->uuid)
            ->has('educationalStages')
            ->where('filter', [])
        );
});
