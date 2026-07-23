<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTransfer;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @return array{school: School, gradeLevel: GradeLevel, user: User}
 */
function createSchoolTransferContext(): array
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

    foreach ([
        'student:view-any',
        'student:add-transferred-student',
        'student:transfer-student-out-of-school',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'student:view-any',
        'student:add-transferred-student',
        'student:transfer-student-out-of-school',
    ]);

    return compact('school', 'gradeLevel', 'user');
}

/**
 * @return array{student: Student, fromSchool: School}
 */
function createAwaitingSchoolTransferStudent(School $targetSchool, GradeLevel $gradeLevel): array
{
    $fromSchool = School::factory()->create([
        'education_monitor_id' => $targetSchool->education_monitor_id,
    ]);

    $student = Student::factory()->create([
        'education_monitor_id' => $targetSchool->education_monitor_id,
        'school_id' => null,
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => null,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    StudentTransfer::factory()->create([
        'student_id' => $student->id,
        'left_academic_year_id' => AcademicYear::currentId(),
        'joined_academic_year_id' => null,
        'from_school_id' => $fromSchool->id,
        'to_school_id' => null,
        'left_school_at' => now(),
        'joined_school_at' => null,
    ]);

    return compact('student', 'fromSchool');
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

test('transfer create page returns no students without search filters', function () {
    ['user' => $user] = createSchoolTransferContext();

    $this->actingAs($user, 'school')
        ->get(route('school.students.transfers.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/transfers/create')
            ->where('students', [])
            ->where('filter', [])
        );
});

test('transfer create page lists eligible students when filters are provided', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolTransferContext();
    ['student' => $student] = createAwaitingSchoolTransferStudent($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.students.transfers.create', [
            'filter' => [
                'national_id' => $student->national_id,
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/transfers/create')
            ->has('students', 1)
            ->where('students.0.uuid', $student->uuid)
        );
});

test('authorized users can add transferred students to their school', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolTransferContext();
    ['student' => $student] = createAwaitingSchoolTransferStudent($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->post(route('school.students.transfers.store'), [
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('school.students.index'));

    $student->refresh();
    $transfer = $student->transfer;

    expect($student->school_id)->toBe($school->id)
        ->and($student->education_monitor_id)->toBe($school->education_monitor_id)
        ->and($student->enrollment?->school_id)->toBe($school->id)
        ->and($transfer)->not->toBeNull()
        ->and($transfer->to_school_id)->toBe($school->id)
        ->and($transfer->joined_academic_year_id)->toBe(AcademicYear::currentId())
        ->and($transfer->joined_school_at)->not->toBeNull();
});

test('store transfer validates required student ids', function () {
    ['user' => $user] = createSchoolTransferContext();

    $this->actingAs($user, 'school')
        ->from(route('school.students.transfers.create'))
        ->post(route('school.students.transfers.store'), [])
        ->assertRedirect(route('school.students.transfers.create'))
        ->assertSessionHasErrors(['student_ids']);
});

test('store transfer rejects students who are already assigned to a school', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolTransferContext();
    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.transfers.create'))
        ->post(route('school.students.transfers.store'), [
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('school.students.transfers.create'))
        ->assertSessionHasErrors(['student_ids.0']);
});

test('store transfer rejects students who are not awaiting school transfer', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolTransferContext();

    $student = Student::factory()->create([
        'education_monitor_id' => $school->education_monitor_id,
        'school_id' => null,
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => null,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.transfers.create'))
        ->post(route('school.students.transfers.store'), [
            'student_ids' => [$student->id],
        ])
        ->assertRedirect(route('school.students.transfers.create'))
        ->assertSessionHasErrors(['student_ids.0']);
});

test('authorized users can transfer a student out of their school', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolTransferContext();
    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->delete(route('school.students.transfers.destroy', ['student' => $student]))
        ->assertRedirect(route('school.students.index'));

    $student->refresh();
    $transfer = $student->transfer;

    expect($student->school_id)->toBeNull()
        ->and($student->enrollment?->school_id)->toBeNull()
        ->and($student->enrollment?->classroom_id)->toBeNull()
        ->and($transfer)->not->toBeNull()
        ->and($transfer->from_school_id)->toBe($school->id)
        ->and($transfer->left_academic_year_id)->toBe(AcademicYear::currentId())
        ->and($transfer->left_school_at)->not->toBeNull()
        ->and($transfer->joined_school_id)->toBeNull();
});

test('users without transfer out permission cannot remove a student from the school', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel] = createSchoolTransferContext();
    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    Permission::findOrCreate('student:view-any', UserScope::SCHOOL->value);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);
    $user->givePermissionTo('student:view-any');

    $this->actingAs($user, 'school')
        ->delete(route('school.students.transfers.destroy', ['student' => $student]))
        ->assertForbidden();

    expect($student->fresh()->school_id)->toBe($school->id);
});

test('transferring a student out is blocked when the selected academic year is inactive', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolTransferContext();
    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
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
        ->delete(route('school.students.transfers.destroy', ['student' => $student]))
        ->assertForbidden();

    expect($student->fresh()->school_id)->toBe($school->id);
});
