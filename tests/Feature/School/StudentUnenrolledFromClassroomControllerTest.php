<?php

use App\Enums\GradeLevelEnum;
use App\Enums\StudentRegistrationStatus;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  list<string>  $permissions
 * @param  array<string, mixed>  $attributes
 */
function createSchoolUnenrolledFromClassroomManager(School $school, array $permissions = ['student:view-any', 'student:view'], array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ], $attributes));

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

function createSchoolUnenrolledFromClassroomGradeLevel(School $school, GradeLevelEnum $grade): GradeLevel
{
    $gradeLevel = GradeLevel::query()->firstOrCreate(
        ['code' => $grade->value],
        [
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ],
    );

    $school->allGradeLevels()->syncWithoutDetaching([
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
    $school = School::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.students.unenrolled-from-classroom.index'))
        ->assertForbidden();
});

test('unenrolled from classroom index lists only current school students enrolled in grade level without classroom', function () {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($school);
    $gradeLevel = createSchoolUnenrolledFromClassroomGradeLevel($school, GradeLevelEnum::GRADE_1);
    $classroom = Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $unenrolledStudents = Student::factory()->count(2)->for($school)->create();
    foreach ($unenrolledStudents as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'school_id' => $school->id,
            'grade_level_id' => $gradeLevel->id,
            'classroom_id' => null,
        ]);
    }

    $enrolledInClassroom = Student::factory()->for($school)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $enrolledInClassroom->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => $classroom->id,
    ]);

    Student::factory()->for($school)->create();

    $otherSchoolStudent = Student::factory()->for($otherSchool)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $otherSchoolStudent->id,
        'school_id' => $otherSchool->id,
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
    $school = School::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($school);
    $gradeOne = createSchoolUnenrolledFromClassroomGradeLevel($school, GradeLevelEnum::GRADE_1);
    $gradeTwo = createSchoolUnenrolledFromClassroomGradeLevel($school, GradeLevelEnum::GRADE_2);

    $matchingStudent = Student::factory()->for($school)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $matchingStudent->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeOne->id,
        'classroom_id' => null,
    ]);

    $otherStudent = Student::factory()->for($school)->create();
    StudentEnrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_id' => $school->id,
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
    $school = School::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($school);
    $gradeLevel = createSchoolUnenrolledFromClassroomGradeLevel($school, GradeLevelEnum::GRADE_1);
    $nationality = Nationality::factory()->create();

    $matchingStudent = Student::factory()->for($school)->create([
        'nationality_id' => $nationality->id,
        'registration_status' => StudentRegistrationStatus::NEW,
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $matchingStudent->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
    ]);

    $otherStudent = Student::factory()->for($school)->create([
        'registration_status' => StudentRegistrationStatus::REPEATER,
    ]);
    StudentEnrollment::factory()->create([
        'student_id' => $otherStudent->id,
        'school_id' => $school->id,
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
    $school = School::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($school);
    $gradeLevel = createSchoolUnenrolledFromClassroomGradeLevel($school, GradeLevelEnum::GRADE_1);
    $student = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_id' => $school->id,
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
    $school = School::factory()->create();
    $user = createSchoolUnenrolledFromClassroomManager($school);
    $otherSchool = School::factory()->create();
    $student = Student::factory()->for($otherSchool)->create();

    $this->actingAs($user, 'school')
        ->get(route('school.students.show', ['student' => $student]))
        ->assertForbidden();
});
