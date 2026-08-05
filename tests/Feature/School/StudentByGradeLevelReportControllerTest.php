<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createSchoolStudentByGradeLevelReportManager(SchoolPeriod $schoolPeriod, array $permissions = ['report:student-by-grade-level:view', 'student:view'], array $attributes = []): User
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

function createSchoolStudentByGradeLevelReportGradeLevel(SchoolPeriod $schoolPeriod, GradeLevelEnum $grade): GradeLevel
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
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolStudentByGradeLevelReportManager($schoolPeriod);
    $gradeOne = createSchoolStudentByGradeLevelReportGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_1);
    $gradeTwo = createSchoolStudentByGradeLevelReportGradeLevel($schoolPeriod, GradeLevelEnum::GRADE_2);

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
