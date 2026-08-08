<?php

use App\Enums\GradeLevelEnum;
use App\Enums\StudentExamEnrollmentStatus;
use App\Enums\StudentRegistrationStatus;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\Helpers\FakeDataGenerator;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createSchoolStudentManager(SchoolPeriod $schoolPeriod, array $permissions, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ], $attributes));

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

function seedLibyanNationality(): Nationality
{
    return Nationality::query()->firstOrCreate(
        ['code' => Nationality::LIBYA_CODE],
        ['name' => 'ليبي'],
    );
}

function createSchoolGradeLevel(SchoolPeriod $schoolPeriod, GradeLevelEnum $grade): GradeLevel
{
    $gradeLevel = GradeLevel::query()->firstOrCreate(
        ['code' => $grade->value],
        [
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ],
    );

    $schoolPeriod->allGradeLevels()->syncWithoutDetaching([
        $gradeLevel->id => ['academic_year_id' => AcademicYear::currentId()],
    ]);

    return $gradeLevel;
}

function createSchoolStudentContext(): array
{
    $schoolPeriod = SchoolPeriod::factory()->create();
    $gradeLevel = createSchoolGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);
    $user = createSchoolStudentManager($schoolPeriod, [
        'student:view-any',
        'student:view',
        'student:create',
        'student:update',
        'student:enroll-in-grade-level',
        'student:enroll-in-classroom',
        'student:transfer-classroom',
        'student:transfer-student-out-of-school',
        'student:view-psychosocial-card',
        'student:view-academic-record',
    ]);

    return compact('schoolPeriod', 'gradeLevel', 'user');
}

function schoolStudentStorePayload(GradeLevel $gradeLevel, array $overrides = []): array
{
    $gender = 'male';
    $dateOfBirth = '2018-06-15';

    return array_merge([
        'grade_level_id' => $gradeLevel->id,
        'nationality_id' => seedLibyanNationality()->id,
        'registration_status' => StudentRegistrationStatus::NEW->value,
        'student_first_name' => 'Ahmed',
        'student_father_name' => 'Mohamed',
        'student_grandfather_name' => 'Ali',
        'student_surname' => 'Hassan',
        'mother_name' => 'Fatima Mohamed Ali Hassan',
        'gender' => $gender,
        'date_of_birth' => $dateOfBirth,
        'national_id' => FakeDataGenerator::libyanNationalId(fake(), $gender, '2018'),
        'family_registration_number' => '12345678',
    ], $overrides);
}

function schoolStudentUpdatePayload(Student $student, array $overrides = []): array
{
    return array_merge([
        'nationality_id' => $student->nationality_id,
        'student_first_name' => 'Updated',
        'student_father_name' => $student->father_name,
        'student_grandfather_name' => $student->grandfather_name,
        'student_surname' => $student->surname,
        'mother_name' => $student->mother_name,
        'gender' => $student->gender->value,
        'date_of_birth' => $student->date_of_birth->format('Y-m-d'),
        'national_id' => $student->national_id,
        'family_registration_number' => (string) $student->family_registration_number,
    ], $overrides);
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

    seedLibyanNationality();
});

test('guests are redirected from school student pages', function () {
    $student = Student::factory()->create();

    $this->get(route('school.students.index'))
        ->assertRedirect(route('school.login'));

    $this->get(route('school.students.create'))
        ->assertRedirect(route('school.login'));

    $this->get(route('school.students.show', ['student' => $student]))
        ->assertRedirect(route('school.login'));
});

test('users without permission cannot access school student pages', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);
    $student = Student::factory()->for($schoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.index'))
        ->assertForbidden();

    $this->actingAs($user, 'school')
        ->get(route('school.students.create'))
        ->assertForbidden();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertForbidden();
});

test('authenticated school users can visit the students index', function () {
    ['schoolPeriod' => $schoolPeriod, 'user' => $user] = createSchoolStudentContext();
    $otherSchoolPeriod = SchoolPeriod::factory()->create();

    $students = Student::factory()->count(2)->for($schoolPeriod)->create();
    Student::factory()->for($otherSchoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/index')
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $students->pluck('uuid')->sort()->values()->all())
            ->has('registrationStatuses')
            ->has('nationalities')
            ->where('can.create', true)
            ->where('filter', [])
        );
});

test('student index filters students by registration status and nationality', function () {
    ['schoolPeriod' => $schoolPeriod, 'user' => $user] = createSchoolStudentContext();
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->for($schoolPeriod)->create([
        'nationality_id' => $nationality->id,
        'registration_status' => StudentRegistrationStatus::NEW,
    ]);

    Student::factory()->for($schoolPeriod)->create([
        'registration_status' => StudentRegistrationStatus::REPEATER,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.index', [
            'filter' => [
                'registration_status' => StudentRegistrationStatus::NEW->value,
                'nationality_id' => $nationality->id,
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $matchingStudent->uuid)
        );
});

test('authenticated school users can visit the create student page', function () {
    ['schoolPeriod' => $schoolPeriod, 'user' => $user] = createSchoolStudentContext();

    $this->actingAs($user, 'school')
        ->get(route('school.students.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/create')
            ->has('gradeLevels', 1)
            ->has('registrationStatuses')
            ->has('nationalities')
            ->where('libyanNationalityId', seedLibyanNationality()->id)
        );
});

test('authenticated school users can store a student with grade level enrollment', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolStudentContext();
    $payload = schoolStudentStorePayload($gradeLevel, [
        'student_first_name' => 'Khaled',
    ]);

    $response = $this->actingAs($user, 'school')
        ->post(route('school.students.store'), $payload);

    $student = Student::query()->where('first_name', 'Khaled')->first();

    expect($student)->not->toBeNull()
        ->and($student->number)->toMatch('/^STU-\d{8}$/')
        ->and($student->exam_enrollment_status)->toBe(StudentExamEnrollmentStatus::REGISTERED)
        ->and($student->school_period_id)->toBe($schoolPeriod->id)
        ->and($student->education_monitor_id)->toBe($schoolPeriod->education_monitor_id)
        ->and($student->enrollment)->not->toBeNull()
        ->and($student->enrollment->grade_level_id)->toBe($gradeLevel->id)
        ->and($student->enrollment->school_period_id)->toBe($schoolPeriod->id);

    $response->assertRedirect(route('school.students.show', ['student' => $student]));
});

test('store validates required fields', function () {
    ['user' => $user] = createSchoolStudentContext();

    $this->actingAs($user, 'school')
        ->post(route('school.students.store'), [])
        ->assertSessionHasErrors([
            'grade_level_id',
            'nationality_id',
            'registration_status',
            'student_first_name',
            'student_father_name',
            'student_grandfather_name',
            'student_surname',
            'mother_name',
            'gender',
            'date_of_birth',
        ]);
});

test('store rejects grade levels that are not assigned to the school', function () {
    ['user' => $user] = createSchoolStudentContext();
    $foreignGradeLevel = GradeLevel::factory()->create();

    $this->actingAs($user, 'school')
        ->from(route('school.students.create'))
        ->post(route('school.students.store'), schoolStudentStorePayload($foreignGradeLevel))
        ->assertRedirect(route('school.students.create'))
        ->assertSessionHasErrors(['grade_level_id']);
});

test('authenticated school users can visit the show student page', function () {
    ['schoolPeriod' => $schoolPeriod, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolStudentContext();

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
            ->where('student.uuid', $student->uuid)
            ->where('can.enrollInGradeLevel', false)
            ->where('can.enrollInClassroom', true)
            ->where('can.update', true)
            ->has('transfers')
        );
});

test('school users cannot view students from another school', function () {
    ['user' => $user] = createSchoolStudentContext();
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $student = Student::factory()->for($otherSchoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertForbidden();
});

test('authenticated school users can visit the edit student page', function () {
    ['schoolPeriod' => $schoolPeriod, 'user' => $user] = createSchoolStudentContext();
    $student = Student::factory()->for($schoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.edit', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/edit')
            ->where('student.uuid', $student->uuid)
            ->has('nationalities')
            ->where('libyanNationalityId', seedLibyanNationality()->id)
        );
});

test('authenticated school users can update a student', function () {
    ['schoolPeriod' => $schoolPeriod, 'user' => $user] = createSchoolStudentContext();
    $student = Student::factory()->for($schoolPeriod)->create([
        'first_name' => 'Old Name',
    ]);

    $this->actingAs($user, 'school')
        ->put(route('school.students.update', ['student' => $student]), schoolStudentUpdatePayload($student))
        ->assertRedirect(route('school.students.show', ['student' => $student]));

    expect($student->fresh()->first_name)->toBe('Updated');
});

test('update validates required fields', function () {
    ['schoolPeriod' => $schoolPeriod, 'user' => $user] = createSchoolStudentContext();
    $student = Student::factory()->for($schoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->put(route('school.students.update', ['student' => $student]), [])
        ->assertSessionHasErrors([
            'nationality_id',
            'student_first_name',
            'student_father_name',
            'student_grandfather_name',
            'student_surname',
            'mother_name',
            'gender',
            'date_of_birth',
        ]);
});

test('school users cannot update students from another school', function () {
    ['user' => $user] = createSchoolStudentContext();
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $student = Student::factory()->for($otherSchoolPeriod)->create([
        'first_name' => 'Protected',
    ]);

    $this->actingAs($user, 'school')
        ->put(route('school.students.update', ['student' => $student]), schoolStudentUpdatePayload($student))
        ->assertForbidden();

    expect($student->fresh()->first_name)->toBe('Protected');
});
