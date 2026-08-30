<?php

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createSchoolAdminUserForDefaultUserTests(): User
{
    $user = User::factory()->create();

    foreach (['school:view-any', 'school:view', 'school:create', 'school:update', 'school:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
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

function createGradeLevelForDefaultUserTests(): GradeLevel
{
    return GradeLevel::factory()->create([
        'educational_stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION,
    ]);
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

    Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);
});

test('creating a single period school creates one default user', function () {
    $admin = createSchoolAdminUserForDefaultUserTests();
    $monitor = EducationMonitor::factory()->create();
    $gradeLevel = createGradeLevelForDefaultUserTests();

    $this->actingAs($admin, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
            'education_services_office_id' => null,
            'type' => SchoolType::PUBLIC->value,
            'academic_period' => SchoolAcademicPeriod::MORNING->value,
            'name' => 'مدرسة الوحدة',
            'students_gender' => SchoolStudentsGender::MIXED->value,
            'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'grade_levels' => [$gradeLevel->id],
        ])
        ->assertRedirect();

    $school = School::query()->firstOrFail();
    $period = $school->periods()->firstOrFail();

    expect(User::query()->where('scope', UserScope::SCHOOL)->count())->toBe(1);

    $defaultUser = User::query()->where('organization_id', $period->id)->firstOrFail();

    expect($defaultUser->organization_type)->toBe(SchoolPeriod::class)
        ->and($defaultUser->hasInitialPassword())->toBeTrue()
        ->and($defaultUser->schoolPeriods)->toHaveCount(1);
});

test('creating a dual period same school creates separate default users per period', function () {
    $admin = createSchoolAdminUserForDefaultUserTests();
    $monitor = EducationMonitor::factory()->create();
    $primaryGradeLevel = GradeLevel::factory()->create([
        'educational_stage' => SchoolEducationalStageEnum::PRIMARY_EDUCATION,
    ]);
    $secondaryGradeLevel = GradeLevel::factory()->create([
        'educational_stage' => SchoolEducationalStageEnum::SECONDARY_EDUCATION,
    ]);

    $this->actingAs($admin, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
            'education_services_office_id' => null,
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
        ])
        ->assertRedirect();

    $school = School::query()->firstOrFail();
    $periods = $school->periods()->orderedByAcademicPeriod()->get();

    expect($periods)->toHaveCount(2)
        ->and(User::query()->where('scope', UserScope::SCHOOL)->count())->toBe(2);

    $passwords = User::query()
        ->where('scope', UserScope::SCHOOL)
        ->get()
        ->map(fn (User $user): string => (string) $user->initial_password)
        ->all();

    expect($passwords[0])->not->toBe($passwords[1]);
});

test('updating a school does not regenerate default user passwords', function () {
    $admin = createSchoolAdminUserForDefaultUserTests();
    $monitor = EducationMonitor::factory()->create();
    $gradeLevel = createGradeLevelForDefaultUserTests();

    $this->actingAs($admin, 'administration')
        ->post(route('administration.schools.store'), [
            'education_monitor_id' => $monitor->id,
            'education_services_office_id' => null,
            'type' => SchoolType::PUBLIC->value,
            'academic_period' => SchoolAcademicPeriod::MORNING->value,
            'name' => 'مدرسة الوحدة',
            'students_gender' => SchoolStudentsGender::MIXED->value,
            'educational_stages' => [SchoolEducationalStageEnum::PRIMARY_EDUCATION->value],
            'grade_levels' => [$gradeLevel->id],
        ]);

    $school = School::query()->firstOrFail();
    $defaultUser = User::query()->where('scope', UserScope::SCHOOL)->firstOrFail();
    $originalPasswordHash = $defaultUser->password;
    $originalInitialPassword = $defaultUser->initial_password;

    $this->actingAs($admin, 'administration')
        ->put(route('administration.schools.update', ['school' => $school]), [
            'education_monitor_id' => $monitor->id,
            'education_services_office_id' => null,
            'name' => 'مدرسة الوحدة المحدثة',
        ])
        ->assertRedirect();

    $defaultUser->refresh();

    expect($defaultUser->password)->toBe($originalPasswordHash)
        ->and($defaultUser->initial_password)->toBe($originalInitialPassword)
        ->and(User::query()->where('scope', UserScope::SCHOOL)->count())->toBe(1);
});
