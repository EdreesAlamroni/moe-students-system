<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentPsychosocialCard;
use App\Models\User;
use App\Support\Helpers\FakeDataGenerator;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @return array{school: School, gradeLevel: GradeLevel, user: User}
 */
function createSchoolPsychosocialCardContext(): array
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

    foreach ([
        'student:view',
        'student:view-psychosocial-card',
        'student:update-psychosocial-card',
        'student:print-psychosocial-card',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-psychosocial-card',
        'student:update-psychosocial-card',
        'student:print-psychosocial-card',
    ]);

    return compact('school', 'gradeLevel', 'user');
}

/**
 * @return array{student: Student, enrollment: StudentEnrollment}
 */
function createEnrolledStudentForPsychosocialCard(School $school, GradeLevel $gradeLevel): array
{
    $student = Student::factory()->for($school)->create();

    $enrollment = StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    return compact('student', 'enrollment');
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

test('authorized users can view a student psychosocial card', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.students.psychosocial-card.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/psychosocial-cards/show')
            ->where('student.uuid', $student->uuid)
            ->where('can.updatePsychosocialCard', true)
            ->where('can.printPsychosocialCard', true)
        );
});

test('users without view permission cannot access the psychosocial card show page', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});

test('school users cannot view psychosocial cards for students from another school', function () {
    ['gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    $otherSchool = School::factory()->create();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($otherSchool, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can visit the psychosocial card edit page', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.students.psychosocial-card.edit', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/psychosocial-cards/edit')
            ->where('student.uuid', $student->uuid)
            ->where('isFromPreviousYear', false)
            ->has('studentLivingSituations')
            ->has('behavioralProblems')
            ->has('nationalities')
        );
});

test('authorized users can update a student psychosocial card', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    ['student' => $student, 'enrollment' => $enrollment] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->put(route('school.students.psychosocial-card.update', ['student' => $student]), [
            'guardian_name' => 'Guardian Name',
            'guardian_phone_number' => FakeDataGenerator::libyanMobile(fake()),
            'number_of_family_members' => 5,
        ])
        ->assertRedirect(route('school.students.psychosocial-card.show', ['student' => $student]));

    $card = StudentPsychosocialCard::query()
        ->where('student_id', $student->id)
        ->where('academic_year_id', AcademicYear::currentId())
        ->first();

    expect($card)->not->toBeNull()
        ->and($card->student_enrollment_id)->toBe($enrollment->id)
        ->and($card->guardian_name)->toBe('Guardian Name')
        ->and($card->number_of_family_members)->toBe(5);
});

test('updating a psychosocial card without enrollment is forbidden', function () {
    ['school' => $school, 'user' => $user] = createSchoolPsychosocialCardContext();
    $student = Student::factory()->for($school)->create();

    $this->actingAs($user, 'school')
        ->put(route('school.students.psychosocial-card.update', ['student' => $student]), [
            'guardian_name' => 'Guardian Name',
        ])
        ->assertForbidden();

    expect(StudentPsychosocialCard::query()->where('student_id', $student->id)->exists())->toBeFalse();
});

test('updating a psychosocial card validates representative phone numbers', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->from(route('school.students.psychosocial-card.edit', ['student' => $student]))
        ->put(route('school.students.psychosocial-card.update', ['student' => $student]), [
            'guardian_representative_phone_number' => 'invalid-phone',
        ])
        ->assertRedirect(route('school.students.psychosocial-card.edit', ['student' => $student]))
        ->assertSessionHasErrors(['guardian_representative_phone_number']);
});

test('authorized users can print a student psychosocial card', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    StudentPsychosocialCard::factory()->create([
        'student_id' => $student->id,
        'student_enrollment_id' => $student->enrollment->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.psychosocial-card.print', ['student' => $student]))
        ->assertOk()
        ->assertViewIs('print.school.students.psychosocial-card')
        ->assertViewHas('student', fn ($viewStudent) => $viewStudent->is($student->fresh()))
        ->assertViewHas('psychosocialCard')
        ->assertViewHas('school', fn ($viewSchool) => $viewSchool->is($school));
});

test('users without print permission cannot print a psychosocial card', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

    Permission::findOrCreate('student:view-psychosocial-card', UserScope::SCHOOL->value);

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);
    $user->givePermissionTo('student:view-psychosocial-card');

    $this->actingAs($user, 'school')
        ->get(route('school.students.psychosocial-card.print', ['student' => $student]))
        ->assertForbidden();
});

test('psychosocial card update is blocked when the selected academic year is inactive', function () {
    ['school' => $school, 'gradeLevel' => $gradeLevel, 'user' => $user] = createSchoolPsychosocialCardContext();
    ['student' => $student] = createEnrolledStudentForPsychosocialCard($school, $gradeLevel);

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
        ->put(route('school.students.psychosocial-card.update', ['student' => $student]), [
            'guardian_name' => 'Guardian Name',
        ])
        ->assertForbidden();
});
