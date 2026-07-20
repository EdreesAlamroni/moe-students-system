<?php

use App\Enums\DayOfWeek;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createSchoolClassScheduleViewer(School $school, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ], $attributes));

    foreach ([
        'class-schedule:view',
        'class-schedule:update',
        'class-schedule:print',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
    }

    $user->givePermissionTo([
        'class-schedule:view',
        'class-schedule:update',
        'class-schedule:print',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/classrooms', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('authenticated school users can view a class schedule with empty grid cells', function () {
    $school = School::factory()->create(['academic_period' => SchoolAcademicPeriod::EVENING]);
    $user = createSchoolClassScheduleViewer($school);
    $gradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $classroom = Classroom::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $period = ClassPeriod::factory()->create([
        'academic_year_id' => $academicYearId,
        'academic_period' => SchoolAcademicPeriod::EVENING,
        'order' => 1,
        'is_break' => false,
    ]);

    $breakPeriod = ClassPeriod::factory()->asBreak()->create([
        'academic_year_id' => $academicYearId,
        'academic_period' => SchoolAcademicPeriod::EVENING,
        'order' => 2,
    ]);

    $subject = Subject::factory()->create(['grade_level_id' => $gradeLevel->id]);

    ClassSchedule::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => $academicYearId,
        'classroom_id' => $classroom->id,
        'class_period_id' => $period->id,
        'subject_id' => $subject->id,
        'day_of_week' => DayOfWeek::SUNDAY,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.classrooms.class-schedules.show', $classroom))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/class-schedules/show')
            ->has('schedule.grid')
            ->where("schedule.grid.{$period->id}.".DayOfWeek::SUNDAY->value.'.uuid', fn ($uuid) => $uuid !== null)
            ->where("schedule.grid.{$period->id}.".DayOfWeek::MONDAY->value, null)
            ->where("schedule.grid.{$breakPeriod->id}.".DayOfWeek::SUNDAY->value, 'break')
        );
});
