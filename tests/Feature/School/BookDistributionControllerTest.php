<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
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
 */
function createSchoolBookDistributionUser(School $school, array $permissions = ['book-distribution:view', 'book-distribution:distribute']): User
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

function attachGradeLevelToSchool(School $school, GradeLevel $gradeLevel): void
{
    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);
}

function confirmBookDistributionForSchool(School $school, GradeLevel $gradeLevel): BookDistribution
{
    return BookDistribution::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);
}

function enrollStudentInGradeLevel(School $school, GradeLevel $gradeLevel, Student $student): void
{
    StudentEnrollment::factory()->create([
        'academic_year_id' => AcademicYear::currentId(),
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $student->id,
    ]);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/book-distributions', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the school book distribution page', function () {
    $this->get(route('school.book-distributions.index'))
        ->assertRedirect(route('school.login'));
});

test('users without book distribution permissions cannot view the page', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.book-distributions.index'))
        ->assertForbidden();
});

test('authenticated school users can visit the book distribution page', function () {
    $school = School::factory()->create();
    $user = createSchoolBookDistributionUser($school);
    $gradeLevel = GradeLevel::factory()->create();
    attachGradeLevelToSchool($school, $gradeLevel);

    $this->actingAs($user, 'school')
        ->get(route('school.book-distributions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/book-distributions/index')
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.id', $gradeLevel->id)
            ->has('students', 0)
            ->where('warehouseConfirmed', false)
            ->where('selected.grade_level_id', null)
        );
});

test('selecting a grade level loads its students with distribution status', function () {
    $school = School::factory()->create();
    $user = createSchoolBookDistributionUser($school);
    $gradeLevel = GradeLevel::factory()->create();
    attachGradeLevelToSchool($school, $gradeLevel);
    confirmBookDistributionForSchool($school, $gradeLevel);

    $distributedStudent = Student::factory()->for($school)->create();
    $pendingStudent = Student::factory()->for($school)->create();
    enrollStudentInGradeLevel($school, $gradeLevel, $distributedStudent);
    enrollStudentInGradeLevel($school, $gradeLevel, $pendingStudent);

    BookDistributionItem::factory()->create([
        'school_id' => $school->id,
        'student_id' => $distributedStudent->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $response = $this->actingAs($user, 'school')
        ->get(route('school.book-distributions.index', [
            'grade_level_id' => $gradeLevel->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/book-distributions/index')
            ->where('warehouseConfirmed', true)
            ->has('students', 2)
            ->where('selected.grade_level_id', $gradeLevel->id)
        );

    $students = collect($response->original->getData()['page']['props']['students']);

    expect($students->firstWhere('uuid', $distributedStudent->uuid)['already_distributed'])->toBeTrue()
        ->and($students->firstWhere('uuid', $pendingStudent->uuid)['already_distributed'])->toBeFalse();
});

test('the page filters students by name', function () {
    $school = School::factory()->create();
    $user = createSchoolBookDistributionUser($school);
    $gradeLevel = GradeLevel::factory()->create();
    attachGradeLevelToSchool($school, $gradeLevel);
    confirmBookDistributionForSchool($school, $gradeLevel);

    $matchingStudent = Student::factory()->for($school)->create([
        'first_name' => 'أحمد',
        'father_name' => 'محمد',
        'grandfather_name' => 'علي',
        'surname' => 'السالم',
    ]);
    $otherStudent = Student::factory()->for($school)->create([
        'first_name' => 'خالد',
        'father_name' => 'سالم',
        'grandfather_name' => 'عمر',
        'surname' => 'الجبل',
    ]);
    enrollStudentInGradeLevel($school, $gradeLevel, $matchingStudent);
    enrollStudentInGradeLevel($school, $gradeLevel, $otherStudent);

    $this->actingAs($user, 'school')
        ->get(route('school.book-distributions.index', [
            'grade_level_id' => $gradeLevel->id,
            'filter' => ['name' => 'أحمد'],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students', 1)
            ->where('students.0.uuid', $matchingStudent->uuid)
        );
});

test('the page filters students by distribution status', function () {
    $school = School::factory()->create();
    $user = createSchoolBookDistributionUser($school);
    $gradeLevel = GradeLevel::factory()->create();
    attachGradeLevelToSchool($school, $gradeLevel);
    confirmBookDistributionForSchool($school, $gradeLevel);

    $distributedStudent = Student::factory()->for($school)->create();
    $pendingStudent = Student::factory()->for($school)->create();
    enrollStudentInGradeLevel($school, $gradeLevel, $distributedStudent);
    enrollStudentInGradeLevel($school, $gradeLevel, $pendingStudent);

    BookDistributionItem::factory()->create([
        'school_id' => $school->id,
        'student_id' => $distributedStudent->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.book-distributions.index', [
            'grade_level_id' => $gradeLevel->id,
            'filter' => ['distribution_status' => 'pending'],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students', 1)
            ->where('students.0.uuid', $pendingStudent->uuid)
        );

    $this->actingAs($user, 'school')
        ->get(route('school.book-distributions.index', [
            'grade_level_id' => $gradeLevel->id,
            'filter' => ['distribution_status' => 'distributed'],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students', 1)
            ->where('students.0.uuid', $distributedStudent->uuid)
        );
});

test('school users can distribute books to selected students', function () {
    $school = School::factory()->create();
    $user = createSchoolBookDistributionUser($school);
    $gradeLevel = GradeLevel::factory()->create();
    attachGradeLevelToSchool($school, $gradeLevel);
    confirmBookDistributionForSchool($school, $gradeLevel);

    $student = Student::factory()->for($school)->create();
    enrollStudentInGradeLevel($school, $gradeLevel, $student);

    $this->actingAs($user, 'school')
        ->post(route('school.book-distributions.store'), [
            'grade_level_id' => $gradeLevel->id,
            'student_ids' => [$student->id],
        ])
        ->assertRedirect();

    expect(BookDistributionItem::query()
        ->where('student_id', $student->id)
        ->where('academic_year_id', AcademicYear::currentId())
        ->exists())->toBeTrue();
});
