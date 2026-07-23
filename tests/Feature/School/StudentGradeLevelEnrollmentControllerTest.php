<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @return array{school: School, gradeLevel: GradeLevel, user: User}
 */
function createSchoolGradeLevelEnrollmentContext(): array
{
    $school = School::factory()->create();
    $gradeLevel = GradeLevel::query()->firstOrCreate(
        ['code' => GradeLevelEnum::GRADE_1->value],
        [
            'name' => GradeLevelEnum::GRADE_1->label(),
            'educational_stage' => GradeLevelEnum::GRADE_1->stage(),
            'order' => GradeLevelEnum::GRADE_1->order(),
        ],
    );

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    foreach (['student:view-any', 'student:view', 'student:enroll-in-grade-level'] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'student:view-any',
        'student:view',
        'student:enroll-in-grade-level',
    ]);

    return compact('school', 'gradeLevel', 'user');
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('authorized users can enroll a student in a grade level', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolGradeLevelEnrollmentContext();

    $student = Student::factory()->for($school)->create();

    $this->actingAs($user, 'school')
        ->post(route('school.students.grade-level-enrollments.store', ['student' => $student]), [
            'grade_level_id' => $gradeLevel->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]));

    expect($student->fresh()->enrollment)
        ->not->toBeNull()
        ->grade_level_id->toBe($gradeLevel->id)
        ->school_id->toBe($school->id)
        ->classroom_id->toBeNull();
});

test('users without enroll permission cannot enroll a student in a grade level', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel] = createSchoolGradeLevelEnrollmentContext();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $student = Student::factory()->for($school)->create();

    $this->actingAs($user, 'school')
        ->post(route('school.students.grade-level-enrollments.store', ['student' => $student]), [
            'grade_level_id' => $gradeLevel->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->enrollment)->toBeNull();
});

test('enrolling a student in a grade level validates required fields', function () {
    ['school' => $school, 'user' => $user] = createSchoolGradeLevelEnrollmentContext();
    $student = Student::factory()->for($school)->create();

    $this->actingAs($user, 'school')
        ->from(route('school.students.show', ['student' => $student]))
        ->post(route('school.students.grade-level-enrollments.store', ['student' => $student]), [])
        ->assertRedirect(route('school.students.show', ['student' => $student]))
        ->assertSessionHasErrors(['grade_level_id']);
});

test('enrolling a student rejects grade levels that are not assigned to the school', function () {
    ['school' => $school, 'user' => $user] = createSchoolGradeLevelEnrollmentContext();
    $student = Student::factory()->for($school)->create();
    $foreignGradeLevel = GradeLevel::factory()->create();

    $this->actingAs($user, 'school')
        ->from(route('school.students.show', ['student' => $student]))
        ->post(route('school.students.grade-level-enrollments.store', ['student' => $student]), [
            'grade_level_id' => $foreignGradeLevel->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]))
        ->assertSessionHasErrors(['grade_level_id']);

    expect($student->fresh()->enrollment)->toBeNull();
});

test('enrolling a student who already has an enrollment is forbidden', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolGradeLevelEnrollmentContext();

    $otherGradeLevel = GradeLevel::factory()->create();
    $school->allGradeLevels()->attach($otherGradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.students.grade-level-enrollments.store', ['student' => $student]), [
            'grade_level_id' => $otherGradeLevel->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->enrollment?->grade_level_id)->toBe($gradeLevel->id);
});

test('grade level enrollment is blocked when the selected academic year is inactive', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolGradeLevelEnrollmentContext();
    $student = Student::factory()->for($school)->create();

    $inactiveYear = AcademicYear::factory()->create([
        'name' => '2023-2024',
        'start_date' => now()->subYear()->startOfYear(),
        'end_date' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    AcademicYear::clearCachedCurrent();

    $this->actingAs($user, 'school')
        ->withSession([
            sprintf('selected_academic_year_id.%d', $user->id) => $inactiveYear->id,
        ])
        ->post(route('school.students.grade-level-enrollments.store', ['student' => $student]), [
            'grade_level_id' => $gradeLevel->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->enrollment)->toBeNull();
});

test('student show page exposes grade level enrollment ability when authorized', function () {
    ['school' => $school, 'user' => $user] = createSchoolGradeLevelEnrollmentContext();
    $student = Student::factory()->for($school)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/show')
            ->where('can.enrollInGradeLevel', true)
            ->where('can.enrollInClassroom', false)
        );
});
