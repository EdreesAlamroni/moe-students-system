<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  list<string>  $permissions
 * @param  array<string, mixed>  $attributes
 */
function createSchoolAttendanceReportManager(School $school, array $permissions = ['report:attendance:view', 'report:attendance:print'], array $attributes = []): User
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

function createSchoolAttendanceReportGradeLevel(School $school, GradeLevelEnum $grade): GradeLevel
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
    PolicyRegistrar::register(Request::create('/school/reports/attendance', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('attendance report index lists classrooms as grade level name slash classroom name', function () {
    $school = School::factory()->create();
    $user = createSchoolAttendanceReportManager($school);
    $gradeLevel = createSchoolAttendanceReportGradeLevel($school, GradeLevelEnum::GRADE_1);

    $classroom = Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'أ',
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.reports.attendance.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/reports/attendance')
            ->has('classrooms', 1)
            ->where('classrooms.0.id', $classroom->id)
            ->where('classrooms.0.name', sprintf('%s / %s', $gradeLevel->name, $classroom->name))
        );
});

test('attendance report index only lists classrooms for the current school', function () {
    $school = School::factory()->create();
    $otherSchool = School::factory()->create();
    $user = createSchoolAttendanceReportManager($school);
    $gradeLevel = createSchoolAttendanceReportGradeLevel($school, GradeLevelEnum::GRADE_1);
    $otherGradeLevel = createSchoolAttendanceReportGradeLevel($otherSchool, GradeLevelEnum::GRADE_2);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => 'أ',
    ]);

    Classroom::factory()->create([
        'school_id' => $otherSchool->id,
        'grade_level_id' => $otherGradeLevel->id,
        'name' => 'ب',
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.reports.attendance.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/reports/attendance')
            ->has('classrooms', 1)
        );
});
