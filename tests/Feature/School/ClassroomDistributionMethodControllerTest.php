<?php

use App\Enums\ClassroomDistributionMethod;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomDistributionCompletion;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createClassroomDistributionMethodContext(array $permissions = [
    'classroom-distribution:view',
    'classroom-distribution:distribute',
    'classroom-distribution:finalize',
]): array
{
    $schoolPeriod = SchoolPeriod::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo($permissions);

    return compact('schoolPeriod', 'gradeLevel', 'user');
}

function enrollStudentWithoutClassroomForMethodTest(
    SchoolPeriod $schoolPeriod,
    GradeLevel $gradeLevel,
    ?Student $student = null,
): Student {
    $student ??= Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    return $student;
}

function createMethodTestClassroom(SchoolPeriod $schoolPeriod, GradeLevel $gradeLevel, array $attributes = []): Classroom
{
    return Classroom::factory()->create(array_merge([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => AcademicYear::currentId(),
    ], $attributes));
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

test('guests are redirected from the manual distribution page', function () {
    $this->get(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertRedirect(route('school.login'));
});

test('users without distribute permission cannot access distribution methods', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext([
        'classroom-distribution:view',
    ]);

    $student = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertForbidden();

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => $classroom->id,
            'student_ids' => [$student->id],
        ])
        ->assertForbidden();
});

test('authorized users can visit the manual distribution page', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    createMethodTestClassroom($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/classroom-distribution/methods/manual')
            ->has('gradeLevels', 1)
            ->where('selectedGradeLevelId', null)
            ->where('isDistributionCompleted', false)
            ->where('can.distribute', true)
        );
});

test('authorized users can visit the random distribution page', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    createMethodTestClassroom($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', ['method' => 'random']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/classroom-distribution/methods/random')
            ->has('gradeLevels', 1)
            ->where('selectedGradeLevelId', null)
            ->where('isDistributionCompleted', false)
        );
});

test('selecting a grade level loads classrooms and unassigned students on the manual page', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $student = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel, ['name' => '1-A']);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', [
            'method' => 'manual',
            'grade_level_id' => $gradeLevel->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selectedGradeLevelId', $gradeLevel->id)
            ->has('classrooms', 1)
            ->where('classrooms.0.id', $classroom->id)
            ->has('unassignedStudents', 1)
            ->where('unassignedStudents.0.uuid', $student->uuid)
        );
});

test('selecting a grade level loads classrooms and pending count on the random page', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    createMethodTestClassroom($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', [
            'method' => 'random',
            'grade_level_id' => $gradeLevel->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('selectedGradeLevelId', $gradeLevel->id)
            ->has('classrooms', 1)
            ->where('pendingStudentCount', 2)
        );
});

test('distribution method pages redirect when distribution is already finalized', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);

    ClassroomDistributionCompletion::query()->create([
        'school_period_id' => $schoolPeriod->id,
        'academic_year_id' => AcademicYear::currentId(),
        'completed_at' => now(),
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'error');
});

test('distribution method pages redirect when there are no enrollments', function () {
    ['user' => $user] = createClassroomDistributionMethodContext();

    $this->actingAs($user, 'school')
        ->get(route('school.classroom-distribution.create', ['method' => 'random']))
        ->assertRedirect(route('school.classroom-distribution.index'))
        ->assertSessionHas('laravel_flash_message.level', 'error');
});

test('school users can manually assign students to a classroom', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $student = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => $classroom->id,
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    expect(StudentEnrollment::query()
        ->where('student_id', $student->id)
        ->where('academic_year_id', AcademicYear::currentId())
        ->value('classroom_id'))->toBe($classroom->id);
});

test('manual distribution validates required fields', function () {
    ['user' => $user] = createClassroomDistributionMethodContext();

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [])
        ->assertSessionHasErrors(['grade_level_id', 'classroom_id', 'student_ids']);
});

test('manual distribution rejects students who already have a classroom', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel);
    $otherClassroom = createMethodTestClassroom($schoolPeriod, $gradeLevel, ['name' => 'B']);
    $student = Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => $otherClassroom->id,
            'student_ids' => [$student->id],
        ])
        ->assertSessionHasErrors(['student_ids']);
});

test('manual distribution rejects classrooms from another grade level', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $otherGradeLevel = GradeLevel::factory()->create();
    $schoolPeriod->allGradeLevels()->attach($otherGradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $student = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    $foreignClassroom = createMethodTestClassroom($schoolPeriod, $otherGradeLevel);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => $foreignClassroom->id,
            'student_ids' => [$student->id],
        ])
        ->assertSessionHasErrors(['classroom_id']);
});

test('school users can randomly assign unassigned students to selected classrooms', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $firstStudent = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    $secondStudent = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);

    $classroomA = createMethodTestClassroom($schoolPeriod, $gradeLevel, ['name' => 'A', 'capacity' => 30]);
    $classroomB = createMethodTestClassroom($schoolPeriod, $gradeLevel, ['name' => 'B', 'capacity' => 30]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'random']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_ids' => [$classroomA->id, $classroomB->id],
        ])
        ->assertRedirect(route('school.classroom-distribution.create', ['method' => 'random']))
        ->assertSessionHas('laravel_flash_message.level', 'success');

    foreach ([$firstStudent, $secondStudent] as $student) {
        $classroomId = StudentEnrollment::query()
            ->where('student_id', $student->id)
            ->value('classroom_id');

        expect($classroomId)->toBeIn([$classroomA->id, $classroomB->id]);
    }
});

test('random distribution validates required fields', function () {
    ['user' => $user] = createClassroomDistributionMethodContext();

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'random']), [])
        ->assertSessionHasErrors(['grade_level_id', 'classroom_ids']);
});

test('random distribution fails when no students are waiting for assignment', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel);
    $student = Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'random']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_ids' => [$classroom->id],
        ])
        ->assertSessionHasErrors(['students']);
});

test('distribution store redirects when distribution is already finalized', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $student = enrollStudentWithoutClassroomForMethodTest($schoolPeriod, $gradeLevel);
    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel);

    ClassroomDistributionCompletion::query()->create([
        'school_period_id' => $schoolPeriod->id,
        'academic_year_id' => AcademicYear::currentId(),
        'completed_at' => now(),
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'manual']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => $classroom->id,
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('school.classroom-distribution.create', ['method' => 'manual']))
        ->assertSessionHas('laravel_flash_message.level', 'error');

    expect(StudentEnrollment::query()
        ->where('student_id', $student->id)
        ->value('classroom_id'))->toBeNull();
});

test('distribution store redirects when enrollment guards fail', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createClassroomDistributionMethodContext();

    $classroom = createMethodTestClassroom($schoolPeriod, $gradeLevel);

    $this->actingAs($user, 'school')
        ->post(route('school.classroom-distribution.store', ['method' => 'random']), [
            'grade_level_id' => $gradeLevel->id,
            'classroom_ids' => [$classroom->id],
        ])
        ->assertRedirect(route('school.classroom-distribution.create', ['method' => 'random']))
        ->assertSessionHas('laravel_flash_message.level', 'error');
});

test('the method registry resolves the correct views for each distribution method', function () {
    expect(ClassroomDistributionMethod::RANDOM->route())
        ->toBe(route('school.classroom-distribution.create', ['method' => 'random']))
        ->and(ClassroomDistributionMethod::MANUAL->route())
        ->toBe(route('school.classroom-distribution.create', ['method' => 'manual']));
});
