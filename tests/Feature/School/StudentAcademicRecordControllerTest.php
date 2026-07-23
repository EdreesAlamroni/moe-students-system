<?php

use App\Enums\AcademicRecordRating;
use App\Enums\AcademicRecordStatus;
use App\Enums\GradeLevelEnum;
use App\Enums\StudentRegistrationStatus;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicRecord;
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
 * @return array{school: School, enrollmentGradeLevel: GradeLevel, user: User, firstGradeLevel: GradeLevel, secondGradeLevel: GradeLevel, firstAcademicYear: AcademicYear, secondAcademicYear: AcademicYear}
 */
function createSchoolAcademicRecordContext(): array
{
    $school = School::factory()->create();

    $firstGradeLevel = GradeLevel::query()->firstOrCreate(
        ['code' => GradeLevelEnum::GRADE_1->value],
        [
            'name' => GradeLevelEnum::GRADE_1->label(),
            'educational_stage' => GradeLevelEnum::GRADE_1->stage(),
            'order' => GradeLevelEnum::GRADE_1->order(),
        ],
    );

    $secondGradeLevel = GradeLevel::query()->firstOrCreate(
        ['code' => GradeLevelEnum::GRADE_2->value],
        [
            'name' => GradeLevelEnum::GRADE_2->label(),
            'educational_stage' => GradeLevelEnum::GRADE_2->stage(),
            'order' => GradeLevelEnum::GRADE_2->order(),
        ],
    );

    $enrollmentGradeLevel = GradeLevel::query()->firstOrCreate(
        ['code' => GradeLevelEnum::GRADE_3->value],
        [
            'name' => GradeLevelEnum::GRADE_3->label(),
            'educational_stage' => GradeLevelEnum::GRADE_3->stage(),
            'order' => GradeLevelEnum::GRADE_3->order(),
        ],
    );

    foreach ([$firstGradeLevel, $secondGradeLevel, $enrollmentGradeLevel] as $gradeLevel) {
        $school->allGradeLevels()->syncWithoutDetaching([
            $gradeLevel->id => ['academic_year_id' => AcademicYear::currentId()],
        ]);
    }

    $firstAcademicYear = AcademicYear::query()->create([
        'name' => '2022-2023',
        'start_date' => now()->subYears(2)->startOfYear(),
        'end_date' => now()->subYears(2)->endOfYear(),
        'is_active' => false,
    ]);

    $secondAcademicYear = AcademicYear::query()->create([
        'name' => '2023-2024',
        'start_date' => now()->subYear()->startOfYear(),
        'end_date' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    foreach (['student:view', 'student:view-academic-record', 'student:create-academic-record'] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-academic-record',
        'student:create-academic-record',
    ]);

    return compact(
        'school',
        'enrollmentGradeLevel',
        'user',
        'firstGradeLevel',
        'secondGradeLevel',
        'firstAcademicYear',
        'secondAcademicYear',
    );
}

function createStudentRequiringAcademicRecords(
    School $school,
    GradeLevel $enrollmentGradeLevel,
    StudentRegistrationStatus $registrationStatus = StudentRegistrationStatus::NEW,
): Student {
    $student = Student::factory()->for($school)->create([
        'registration_status' => $registrationStatus,
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $enrollmentGradeLevel->id,
        'classroom_id' => null,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    return $student;
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

test('authorized users can view the academic record page for enrolled students', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.students.academic-record.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/academic-record/show')
            ->where('student.uuid', $student->uuid)
            ->where('requiresAcademicRecord', true)
            ->where('isComplete', false)
            ->where('can.createAcademicRecord', true)
            ->has('groupedRecords', 2)
        );
});

test('users without permission cannot view academic records', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});

test('academic record show is forbidden for students without enrollment', function () {
    ['school' => $school, 'user' => $user] = createSchoolAcademicRecordContext();
    $student = Student::factory()->for($school)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can visit the academic record create page', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'firstGradeLevel' => $firstGradeLevel,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.students.academic-record.create', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/academic-record/create')
            ->where('currentGradeLevel.id', $firstGradeLevel->id)
            ->where('progress.completed', 0)
            ->where('progress.total', 2)
            ->has('selectableAcademicYears')
            ->has('academicRecordStatuses')
            ->has('academicRecordRatings')
        );
});

test('store creates the first academic record and redirects back to create', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'firstGradeLevel' => $firstGradeLevel,
        'firstAcademicYear' => $firstAcademicYear,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $this->actingAs($user, 'school')
        ->post(route('school.students.academic-record.store', ['student' => $student]), [
            'academic_year_id' => $firstAcademicYear->id,
            'grade_level_id' => $firstGradeLevel->id,
            'status' => AcademicRecordStatus::PASSED->value,
            'rating' => AcademicRecordRating::GOOD->value,
        ])
        ->assertRedirect(route('school.students.academic-record.create', ['student' => $student]));

    $record = AcademicRecord::query()
        ->where('student_id', $student->id)
        ->where('grade_level_id', $firstGradeLevel->id)
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->status)->toBe(AcademicRecordStatus::PASSED)
        ->and($record->rating)->toBe(AcademicRecordRating::GOOD)
        ->and($student->fresh()->registration_status)->toBe(StudentRegistrationStatus::NEW);
});

test('store completes required academic records and redirects to show', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'firstGradeLevel' => $firstGradeLevel,
        'secondGradeLevel' => $secondGradeLevel,
        'firstAcademicYear' => $firstAcademicYear,
        'secondAcademicYear' => $secondAcademicYear,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    AcademicRecord::factory()->passed()->create([
        'student_id' => $student->id,
        'grade_level_id' => $firstGradeLevel->id,
        'academic_year_id' => $firstAcademicYear->id,
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.students.academic-record.store', ['student' => $student]), [
            'academic_year_id' => $secondAcademicYear->id,
            'grade_level_id' => $secondGradeLevel->id,
            'status' => AcademicRecordStatus::PASSED->value,
            'rating' => AcademicRecordRating::VERY_GOOD->value,
        ])
        ->assertRedirect(route('school.students.academic-record.show', ['student' => $student]));

    expect($student->fresh()->registration_status)->toBe(StudentRegistrationStatus::NEW);
});

test('store failed academic records do not update registration status', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'firstGradeLevel' => $firstGradeLevel,
        'firstAcademicYear' => $firstAcademicYear,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel, StudentRegistrationStatus::REPEATER);

    $this->actingAs($user, 'school')
        ->post(route('school.students.academic-record.store', ['student' => $student]), [
            'academic_year_id' => $firstAcademicYear->id,
            'grade_level_id' => $firstGradeLevel->id,
            'status' => AcademicRecordStatus::FAILED->value,
        ])
        ->assertRedirect(route('school.students.academic-record.create', ['student' => $student]));

    $record = AcademicRecord::query()
        ->where('student_id', $student->id)
        ->where('grade_level_id', $firstGradeLevel->id)
        ->first();

    expect($record)->not->toBeNull()
        ->and($record->status)->toBe(AcademicRecordStatus::FAILED)
        ->and($record->rating)->toBeNull()
        ->and($student->fresh()->registration_status)->toBe(StudentRegistrationStatus::REPEATER);
});

test('store validates rating is required when status is passed', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'firstGradeLevel' => $firstGradeLevel,
        'firstAcademicYear' => $firstAcademicYear,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $this->actingAs($user, 'school')
        ->from(route('school.students.academic-record.create', ['student' => $student]))
        ->post(route('school.students.academic-record.store', ['student' => $student]), [
            'academic_year_id' => $firstAcademicYear->id,
            'grade_level_id' => $firstGradeLevel->id,
            'status' => AcademicRecordStatus::PASSED->value,
        ])
        ->assertRedirect(route('school.students.academic-record.create', ['student' => $student]))
        ->assertSessionHasErrors(['rating']);
});

test('store rejects duplicate academic year records', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'firstGradeLevel' => $firstGradeLevel,
        'firstAcademicYear' => $firstAcademicYear,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    AcademicRecord::factory()->failed()->create([
        'student_id' => $student->id,
        'grade_level_id' => $firstGradeLevel->id,
        'academic_year_id' => $firstAcademicYear->id,
    ]);

    $this->actingAs($user, 'school')
        ->from(route('school.students.academic-record.create', ['student' => $student]))
        ->post(route('school.students.academic-record.store', ['student' => $student]), [
            'academic_year_id' => $firstAcademicYear->id,
            'grade_level_id' => $firstGradeLevel->id,
            'status' => AcademicRecordStatus::PASSED->value,
            'rating' => AcademicRecordRating::GOOD->value,
        ])
        ->assertRedirect(route('school.students.academic-record.create', ['student' => $student]))
        ->assertSessionHasErrors(['academic_year_id']);
});

test('store rejects academic records for grade levels that are not current', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
        'secondGradeLevel' => $secondGradeLevel,
        'firstAcademicYear' => $firstAcademicYear,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $this->actingAs($user, 'school')
        ->from(route('school.students.academic-record.create', ['student' => $student]))
        ->post(route('school.students.academic-record.store', ['student' => $student]), [
            'academic_year_id' => $firstAcademicYear->id,
            'grade_level_id' => $secondGradeLevel->id,
            'status' => AcademicRecordStatus::PASSED->value,
            'rating' => AcademicRecordRating::GOOD->value,
        ])
        ->assertRedirect(route('school.students.academic-record.create', ['student' => $student]))
        ->assertSessionHasErrors(['grade_level_id']);
});

test('academic record pages are blocked when the selected academic year is inactive', function () {
    [
        'school' => $school,
        'enrollmentGradeLevel' => $enrollmentGradeLevel,
        'user' => $user,
    ] = createSchoolAcademicRecordContext();

    $student = createStudentRequiringAcademicRecords($school, $enrollmentGradeLevel);

    $inactiveYear = AcademicYear::factory()->create([
        'name' => '2021-2022',
        'start_date' => now()->subYears(3)->startOfYear(),
        'end_date' => now()->subYears(3)->endOfYear(),
        'is_active' => false,
    ]);

    AcademicYear::clearCachedCurrent();

    $this->actingAs($user, 'school')
        ->withSession([
            sprintf('selected_academic_year_id.%d', $user->id) => $inactiveYear->id,
        ])
        ->get(route('school.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});
