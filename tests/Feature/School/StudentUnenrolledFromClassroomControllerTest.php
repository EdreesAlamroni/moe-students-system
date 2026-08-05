<?php

use App\Enums\GradeLevelEnum;
use App\Enums\StudentRegistrationStatus;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
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

function createSchoolUnenrolledFromClassroomManager(SchoolPeriod $schoolPeriod, array $permissions = ['student:view-any', 'student:view'], array $attributes = []): User
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

function createSchoolUnenrolledFromClassroomGradeLevel(SchoolPeriod $schoolPeriod, GradeLevelEnum $grade): GradeLevel
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
    PolicyRegistrar::register(Request::create('/school/students/unenrolled-from-classroom', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access unenrolled from classroom students page', function () {
    $this->get(route('school.students.unenrolled-from-classroom.index'))
        ->assertRedirect(route('school.login'));
});

test('users without permission cannot access unenrolled from classroom students page', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-classroom.index'))
        ->assertForbidden();
});

test('unenrolled from classroom index lists only current school students enrolled in grade level without classroom', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($schoolPeriod);
    $gradeLevel = createSchoolUnenrolledFromClassroomGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $unenrolledStudents = Student::factory()->count(2)->for($schoolPeriod)->create();
    foreach ($unenrolledStudents as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'school_period_id' => $schoolPeriod->id,
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => null,
        ]);
    }

    $enrolledInClassroom = Student::factory()->for($schoolPeriod)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $enrolledInClassroom->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
    ]);

    Student::factory()->for($schoolPeriod)->create();

    $otherSchoolStudent = Student::factory()->for($otherSchoolPeriod)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $otherSchoolStudent->id,
        'school_period_id' => $otherSchoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-classroom.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/students/unenrolled-from-classroom/index')
            ->has('nationalities')
            ->has('registrationStatuses')
            ->has('gradeLevels')
            ->has('students.data', 2)
            ->where('students.total', 2)
            ->where('students.data', fn ($data) => collect($data)->pluck('uuid')->sort()->values()->all()
                === $unenrolledStudents->pluck('uuid')->sort()->values()->all())
            ->where('students.data.0.can.view', true)
            ->where('students.data.0.grade_level.id', $gradeLevel->id)
            ->where('students.data.0.grade_level.name', $gradeLevel->name)
            ->where('filter', [])
        );
});

test('unenrolled from classroom index filters by grade level', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($schoolPeriod);
    $gradeOne = createSchoolUnenrolledFromClassroomGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);
    $gradeTwo = createSchoolUnenrolledFromClassroomGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_2);

    $matchingStudent = Student::factory()->for($schoolPeriod)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $matchingStudent->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeOne->id,
        'classroom_id' => null,
    ]);

    $otherStudent = Student::factory()->for($schoolPeriod)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeTwo->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-classroom.index', [
            'filter' => [
                'grade_level_id' => $gradeOne->id,
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $matchingStudent->uuid)
            ->where('students.data.0.grade_level.id', $gradeOne->id)
        );
});

test('unenrolled from classroom index filters by registration status and nationality', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($schoolPeriod);
    $gradeLevel = createSchoolUnenrolledFromClassroomGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->for($schoolPeriod)->create([
        'nationality_id' => $nationality->id,
        'registration_status' => StudentRegistrationStatus::NEW,
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $matchingStudent->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $otherStudent = Student::factory()->for($schoolPeriod)->create([
        'registration_status' => StudentRegistrationStatus::REPEATER,
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-classroom.index', [
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
    $user = createSchoolUnenrolledFromClassroomManager($schoolPeriod);
    $gradeLevel = createSchoolUnenrolledFromClassroomGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);
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
        );
});

test('school users cannot view unenrolled classroom students belonging to another school', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($schoolPeriod);
    $otherSchoolPeriod = SchoolPeriod::factory()->create();
    $student = Student::factory()->for($otherSchoolPeriod)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertForbidden();
});
