<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

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

/**
 * @param  list<string>  $permissions
 */
function createSchoolGradeLevelDeleter(School $school, array $permissions = ['grade-level:delete']): User
{
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

test('grade level can be deleted when no students are enrolled for the current school and academic year', function () {
    $school = School::factory()->create();
    $user = createSchoolGradeLevelDeleter($school);
    $gradeLevel = GradeLevel::factory()->create();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    expect($user->can('delete', $gradeLevel))->toBeTrue();
});

test('grade level cannot be deleted when students are enrolled for the current school and academic year', function () {
    $school = School::factory()->create();
    $user = createSchoolGradeLevelDeleter($school);
    $gradeLevel = GradeLevel::factory()->create();
    $student = Student::factory()->for($school)->create();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $student->id,
        'classroom_id' => null,
    ]);

    expect($user->can('delete', $gradeLevel))->toBeFalse();
});

test('grade level can be deleted when enrollments exist only for another school', function () {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $user = createSchoolGradeLevelDeleter($school);
    $gradeLevel = GradeLevel::factory()->create();
    $student = Student::factory()->for($otherSchool)->create();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $otherSchool->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_id' => $otherSchool->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $student->id,
        'classroom_id' => null,
    ]);

    expect($user->can('delete', $gradeLevel))->toBeTrue();
});

test('grade level can be deleted when enrollments exist only for a previous academic year', function () {
    $school = School::factory()->create();
    $user = createSchoolGradeLevelDeleter($school);
    $gradeLevel = GradeLevel::factory()->create();
    $student = Student::factory()->for($school)->create();

    $previousAcademicYear = AcademicYear::query()->create([
        'name' => '2023-2024',
        'start_date' => now()->subYear()->startOfYear(),
        'end_date' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    StudentEnrollment::factory()->create([
        'academic_year_id' => $previousAcademicYear->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $student->id,
        'classroom_id' => null,
    ]);

    expect($user->can('delete', $gradeLevel))->toBeTrue();
});
