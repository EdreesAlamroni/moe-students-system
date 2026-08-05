<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createSchoolClassroomEnrollmentContext(): array
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

    foreach ([
        'student:view-any',
        'student:view',
        'student:enroll-in-classroom',
        'student:transfer-classroom',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'student:view-any',
        'student:view',
        'student:enroll-in-classroom',
        'student:transfer-classroom',
    ]);

    return compact('schoolPeriod', 'gradeLevel', 'user');
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

test('authorized users can enroll a student in a classroom', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.students.classroom-enrollments.store', ['student' => $student]), [
            'classroom_id' => $classroom->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]));

    expect($student->fresh()->enrollment)
        ->not->toBeNull()
        ->classroom_id->toBe($classroom->id)
        ->grade_level_id->toBe($gradeLevel->id);
});

test('authorized users can transfer a student to another classroom in the same grade level', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();
    $currentClassroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);
    $targetClassroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '2',
    ]);

    $enrollment = StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $currentClassroom->id,
    ]);

    $this->actingAs($user, 'school')
        ->put(route('school.students.classroom-enrollments.update', ['student' => $student]), [
            'classroom_id' => $targetClassroom->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]));

    expect($student->fresh()->enrollment)
        ->id->toBe($enrollment->id)
        ->classroom_id->toBe($targetClassroom->id)
        ->grade_level_id->toBe($gradeLevel->id);
});

test('users without transfer permission cannot move a student to another classroom', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel] = createSchoolClassroomEnrollmentContext();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    Permission::findOrCreate('student:enroll-in-classroom', UserScope::SCHOOL->value);
    $user->givePermissionTo('student:enroll-in-classroom');

    $student = Student::factory()->for($schoolPeriod)->create();
    $currentClassroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);
    $targetClassroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '2',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $currentClassroom->id,
    ]);

    $this->actingAs($user, 'school')
        ->put(route('school.students.classroom-enrollments.update', ['student' => $student]), [
            'classroom_id' => $targetClassroom->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->enrollment?->classroom_id)->toBe($currentClassroom->id);
});

test('transferring a student to the same classroom is rejected', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.show', ['student' => $student]))
        ->put(route('school.students.classroom-enrollments.update', ['student' => $student]), [
            'classroom_id' => $classroom->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]))
        ->assertSessionHasErrors(['classroom_id']);
});

test('transferring a student to a classroom in another grade level is rejected', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $otherGradeLevel = GradeLevel::factory()->create();
    $schoolPeriod->allGradeLevels()->attach($otherGradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $student = Student::factory()->for($schoolPeriod)->create();
    $currentClassroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);
    $otherClassroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $otherGradeLevel->id,
        'name' => '2',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $currentClassroom->id,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.show', ['student' => $student]))
        ->put(route('school.students.classroom-enrollments.update', ['student' => $student]), [
            'classroom_id' => $otherClassroom->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]))
        ->assertSessionHasErrors(['classroom_id']);

    expect($student->fresh()->enrollment?->classroom_id)->toBe($currentClassroom->id);
});

test('guests cannot enroll or transfer students between classrooms', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    $this->post(route('school.students.classroom-enrollments.store', ['student' => $student]), [
        'classroom_id' => $classroom->id,
    ])->assertRedirect(route('school.login'));

    $this->put(route('school.students.classroom-enrollments.update', ['student' => $student]), [
        'classroom_id' => $classroom->id,
    ])->assertRedirect(route('school.login'));
});

test('users without enroll permission cannot assign a student to a classroom', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel] = createSchoolClassroomEnrollmentContext();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    $student = Student::factory()->for($schoolPeriod)->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.students.classroom-enrollments.store', ['student' => $student]), [
            'classroom_id' => $classroom->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->enrollment?->classroom_id)->toBeNull();
});

test('classroom enrollment validates required fields', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.show', ['student' => $student]))
        ->post(route('school.students.classroom-enrollments.store', ['student' => $student]), [])
        ->assertRedirect(route('school.students.show', ['student' => $student]))
        ->assertSessionHasErrors(['classroom_id']);
});

test('classroom enrollment rejects classrooms from another school', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();
    $otherSchoolPeriod = SchoolPeriod::factory()->create();

    $student = Student::factory()->for($schoolPeriod)->create();
    $foreignClassroom = Classroom::factory()->create([
        'school_period_id' => $otherSchoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '9',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.show', ['student' => $student]))
        ->post(route('school.students.classroom-enrollments.store', ['student' => $student]), [
            'classroom_id' => $foreignClassroom->id,
        ])
        ->assertRedirect(route('school.students.show', ['student' => $student]))
        ->assertSessionHasErrors(['classroom_id']);

    expect($student->fresh()->enrollment?->classroom_id)->toBeNull();
});

test('classroom enrollment is blocked when the selected academic year is inactive', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

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
        ->post(route('school.students.classroom-enrollments.store', ['student' => $student]), [
            'classroom_id' => $classroom->id,
        ])
        ->assertForbidden();

    expect($student->fresh()->enrollment?->classroom_id)->toBeNull();
});

test('student show page exposes classroom enrollment ability when authorized', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/show')
            ->where('can.enrollInClassroom', true)
            ->where('can.transferClassroom', false)
        );
});

test('student show page exposes classroom transfer ability when authorized', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolClassroomEnrollmentContext();

    $student = Student::factory()->for($schoolPeriod)->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/show')
            ->where('can.transferClassroom', true)
            ->where('can.enrollInClassroom', false)
        );
});
