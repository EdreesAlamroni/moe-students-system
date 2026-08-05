<?php

use App\Enums\GradeLevelEnum;
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
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createSchoolUnenrolledFromGradeLevelManager(SchoolPeriod $schoolPeriod, array $permissions = ['student:view-any', 'student:view'], array $attributes = []): User
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

function createSchoolUnenrolledGradeLevel(SchoolPeriod $schoolPeriod, GradeLevelEnum $grade): GradeLevel
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

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/students/unenrolled-from-grade-level', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access unenrolled from grade level students page', function () {
    $this->get(route('school.students.unenrolled-from-grade-level.index'))
        ->assertRedirect(route('school.login'));
});

test('users without permission cannot access unenrolled from grade level students page', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-grade-level.index'))
        ->assertForbidden();
});

test('unenrolled from grade level index lists only current school students without enrollment', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromGradeLevelManager($schoolPeriod);
    $gradeLevel = createSchoolUnenrolledGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);

    $unenrolledStudents = Student::factory()->count(2)->for($schoolPeriod)->create();

    $enrolledStudent = Student::factory()->for($schoolPeriod)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $enrolledStudent->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    Student::factory()->for($otherSchoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-grade-level.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/unenrolled-from-grade-level/index')
            ->has('nationalities')
            ->has('registrationStatuses')
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $unenrolledStudents->pluck('uuid')->sort()->values()->all())
            ->where('students.data.0.can.view', true)
            ->where('filter', [])
        );
});

test('unenrolled from grade level index filters by registration status and nationality', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromGradeLevelManager($schoolPeriod);
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->for($schoolPeriod)->create([
        'nationality_id' => $nationality->id,
        'registration_status' => StudentRegistrationStatus::NEW,
    ]);

    Student::factory()->for($schoolPeriod)->create([
        'registration_status' => StudentRegistrationStatus::REPEATER,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-grade-level.index', [
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

test('view details navigates to existing school student show page', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromGradeLevelManager($schoolPeriod);
    $student = Student::factory()->for($schoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/show')
            ->where('student.uuid', $student->uuid)
        );
});

test('school users cannot view unenrolled students belonging to another school', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromGradeLevelManager($schoolPeriod);
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $student = Student::factory()->for($otherSchoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertForbidden();
});
