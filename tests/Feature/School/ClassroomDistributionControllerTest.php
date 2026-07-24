<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomDistributionCompletion;
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
 * @param  array<int, string>  $permissions
 * @return array{school: School, gradeLevel: GradeLevel, user: User}
 */
function createClassroomDistributionContext(array $permissions = [
    'classroom-distribution:view',
    'classroom-distribution:distribute',
    'classroom-distribution:finalize',
]): array
{
    $school = School::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

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

    return compact('school', 'gradeLevel', 'user');
}

function enrollStudentInGradeLevelWithoutClassroom(
    School $school,
    GradeLevel $gradeLevel,
    ?Student $student = null,
): Student {
    $student ??= Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    return $student;
}

function createClassroomForGradeLevel(School $school, GradeLevel $gradeLevel, array $attributes = []): Classroom
{
    return Classroom::factory()->create(array_merge([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => AcademicYear::currentId(),
    ], $attributes));
}

function completeClassroomDistributionForSchool(School $school): ClassroomDistributionCompletion
{
    return ClassroomDistributionCompletion::query()->create([
        'school_id' => $school->id,
        'academic_year_id' => AcademicYear::currentId(),
        'completed_at' => now(),
    ]);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/classroom-distribution', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the classroom distribution page', function () {
    $this->get(route('school.classroom-distribution.index'))
        ->assertRedirect(route('school.login'));
});

test('users without classroom distribution permissions cannot view the page', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.index'))
        ->assertForbidden();
});

test('authenticated school users can visit the classroom distribution page', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);
    createClassroomForGradeLevel($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/classroom-distribution/index')
            ->has('methods', 2)
            ->where('isDistributionCompleted', false)
            ->where('enrollmentSummary.totalCount', 1)
            ->where('enrollmentSummary.eligibleCount', 1)
            ->where('enrollmentSummary.withoutGradeLevelCount', 0)
            ->where('enrollmentSummary.withoutClassroomCount', 1)
            ->where('schoolWideUnassignedCount', 1)
            ->where('can.distribute', true)
            ->where('can.finalize', true)
        );
});

test('the index page reflects enrollment summary counts accurately', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    $classroom = createClassroomForGradeLevel($school, $gradeLevel);

    enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);
    enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);

    $assignedStudent = Student::factory()->for($school)->create();
    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
        'student_id' => $assignedStudent->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('enrollmentSummary.totalCount', 3)
            ->where('enrollmentSummary.eligibleCount', 3)
            ->where('enrollmentSummary.withoutGradeLevelCount', 0)
            ->where('enrollmentSummary.withoutClassroomCount', 2)
            ->where('schoolWideUnassignedCount', 2)
        );
});

test('the index page shows distribution as completed when finalized', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);
    completeClassroomDistributionForSchool($school);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('isDistributionCompleted', true)
            ->where('can.finalize', false)
        );
});

test('the index page respects granular permissions', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext([
        'classroom-distribution:view',
    ]);

    enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('can.distribute', false)
            ->where('can.finalize', false)
        );
});

test('guests cannot finalize classroom distribution', function () {
    $this->post(route('school.classroom-distribution.finalize'))
        ->assertRedirect(route('school.login'));
});

test('users without finalize permission cannot finalize classroom distribution', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext([
        'classroom-distribution:view',
        'classroom-distribution:distribute',
    ]);

    $classroom = createClassroomForGradeLevel($school, $gradeLevel);
    $student = enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);

    StudentEnrollment::query()
        ->where('student_id', $student->id)
        ->update(['classroom_id' => $classroom->id]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.finalize'))
        ->assertForbidden();
});

test('school users can finalize classroom distribution when all eligible students are assigned', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    $classroom = createClassroomForGradeLevel($school, $gradeLevel);
    $student = enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);

    StudentEnrollment::query()
        ->where('student_id', $student->id)
        ->update(['classroom_id' => $classroom->id]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.finalize'))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    expect(ClassroomDistributionCompletion::query()
        ->where('school_id', $school->id)
        ->where('academic_year_id', AcademicYear::currentId())
        ->whereNotNull('completed_at')
        ->exists())->toBeTrue();
});

test('finalize redirects with an error when students remain unassigned', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);
    createClassroomForGradeLevel($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.finalize'))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'error');

    expect(ClassroomDistributionCompletion::query()
        ->where('school_id', $school->id)
        ->exists())->toBeFalse();
});

test('finalize redirects with an error when there are no enrollments', function () {
    ['user' => $user] = createClassroomDistributionContext();

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.finalize'))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'error');
});

test('finalize redirects with an error when distribution is already finalized', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    $classroom = createClassroomForGradeLevel($school, $gradeLevel);
    $student = enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);

    StudentEnrollment::query()
        ->where('student_id', $student->id)
        ->update(['classroom_id' => $classroom->id]);

    completeClassroomDistributionForSchool($school);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.finalize'))
        ->assertForbidden();
});

test('the complete classroom distribution workflow assigns students and finalizes successfully', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionContext();

    $classroomA = createClassroomForGradeLevel($school, $gradeLevel, ['name' => 'A', 'capacity' => 30]);
    $classroomB = createClassroomForGradeLevel($school, $gradeLevel, ['name' => 'B', 'capacity' => 30]);

    $manualStudent = enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);
    $randomStudent = enrollStudentInGradeLevelWithoutClassroom($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('enrollmentSummary.withoutClassroomCount', 2)
        );

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => $classroomA->id,
            'student_ids' => [$manualStudent->id],
        ])
        ->assertRedirect(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    expect(StudentEnrollment::query()
        ->where('student_id', $manualStudent->id)
        ->value('classroom_id'))->toBe($classroomA->id);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'random']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_ids' => [$classroomA->id, $classroomB->id],
        ])
        ->assertRedirect(route('school.classroom-distribution.create', ['method' => 'random']))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    $randomEnrollment = StudentEnrollment::query()
        ->where('student_id', $randomStudent->id)
        ->first();

    expect($randomEnrollment->classroom_id)->toBeIn([$classroomA->id, $classroomB->id]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.finalize'))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    expect(ClassroomDistributionCompletion::isCompleteForSchoolAndYear(
        $school->id,
        AcademicYear::currentId(),
    ))->toBeTrue();

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'error');
});
