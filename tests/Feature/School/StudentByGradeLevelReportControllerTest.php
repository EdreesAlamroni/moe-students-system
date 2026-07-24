<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
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
 * @param  list<string>  $permissions
 * @param  array<string, mixed>  $attributes
 */
function createSchoolStudentByGradeLevelReportManager(School $school, array $permissions = ['report:student-by-grade-level:view', 'student:view'], array $attributes = []): User
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

function createSchoolStudentByGradeLevelReportGradeLevel(School $school, GradeLevelEnum $grade): GradeLevel
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
    PolicyRegistrar::register(Request::create('/school/reports/students-by-grade-level', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('student by grade level report filters by grade level id', function () {
    $school = School::factory()->create();
    $user = createSchoolStudentByGradeLevelReportManager($school);
    $gradeOne = createSchoolStudentByGradeLevelReportGradeLevel($school, GradeLevelEnum::GRADE_1);
    $gradeTwo = createSchoolStudentByGradeLevelReportGradeLevel($school, GradeLevelEnum::GRADE_2);

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
        ->get(route('school.reports.students-by-grade-level.index', [
            'filter' => [
                'grade_level_id' => $gradeOne->id,
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/reports/student-by-grade-level')
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $matchingStudent->uuid)
            ->where('students.data.0.grade_level.id', $gradeOne->id)
        );
});
