<?php

use App\Enums\DayOfWeek;
use App\Enums\SchoolAcademicPeriod;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Subject;
use Database\Seeders\ClassScheduleSeeder;

beforeEach(function () {
    AcademicYear::clearCachedCurrent();
    AcademicYear::factory()->active()->create();
});

test('class schedule seeder creates schedules for every school classroom weekday and teaching period', function () {
    $academicYearId = AcademicYear::currentId();

    $morningSchool = School::factory()->create(['academic_period' => SchoolAcademicPeriod::MORNING]);
    $eveningSchool = School::factory()->create(['academic_period' => SchoolAcademicPeriod::EVENING]);
    $gradeLevel = GradeLevel::factory()->create();

    Subject::factory()->count(3)->create(['grade_level_id' => $gradeLevel->id]);

    ClassPeriod::factory()->createMany([
        ['academic_year_id' => $academicYearId, 'academic_period' => SchoolAcademicPeriod::MORNING, 'order' => 1, 'is_break' => false],
        ['academic_year_id' => $academicYearId, 'academic_period' => SchoolAcademicPeriod::MORNING, 'order' => 2, 'is_break' => true],
        ['academic_year_id' => $academicYearId, 'academic_period' => SchoolAcademicPeriod::MORNING, 'order' => 3, 'is_break' => false],
        ['academic_year_id' => $academicYearId, 'academic_period' => SchoolAcademicPeriod::EVENING, 'order' => 1, 'is_break' => false],
        ['academic_year_id' => $academicYearId, 'academic_period' => SchoolAcademicPeriod::EVENING, 'order' => 2, 'is_break' => false],
    ]);

    $morningClassrooms = Classroom::factory()->count(2)->create([
        'school_id' => $morningSchool->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $eveningClassrooms = Classroom::factory()->create([
        'school_id' => $eveningSchool->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $this->seed(ClassScheduleSeeder::class);

    $schoolDayCount = DayOfWeek::schoolDays()->count();
    $morningTeachingPeriodCount = 2;
    $eveningTeachingPeriodCount = 2;

    $expectedCount = (
        ($morningClassrooms->count() * $morningTeachingPeriodCount * $schoolDayCount)
        + ($eveningTeachingPeriodCount * $schoolDayCount)
    );

    expect(ClassSchedule::query()->count())->toBe($expectedCount);

    ClassSchedule::query()
        ->with('subject')
        ->get()
        ->each(function (ClassSchedule $schedule) use ($gradeLevel): void {
            expect($schedule->subject->grade_level_id)->toBe($gradeLevel->id);
        });

    expect(ClassSchedule::query()->where('school_id', '=', $morningSchool->id)->count())
        ->toBe($morningClassrooms->count() * $morningTeachingPeriodCount * $schoolDayCount)
        ->and(ClassSchedule::query()->where('school_id', '=', $eveningSchool->id)->count())
        ->toBe($eveningTeachingPeriodCount * $schoolDayCount);
});

test('class schedule seeder does not create schedules for break periods', function () {
    $academicYearId = AcademicYear::currentId();

    $school = School::factory()->create(['academic_period' => SchoolAcademicPeriod::MORNING]);
    $gradeLevel = GradeLevel::factory()->create();

    Subject::factory()->create(['grade_level_id' => $gradeLevel->id]);

    $breakPeriod = ClassPeriod::factory()->create([
        'academic_year_id' => $academicYearId,
        'academic_period' => SchoolAcademicPeriod::MORNING,
        'order' => 1,
        'is_break' => true,
    ]);

    ClassPeriod::factory()->create([
        'academic_year_id' => $academicYearId,
        'academic_period' => SchoolAcademicPeriod::MORNING,
        'order' => 2,
        'is_break' => false,
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $this->seed(ClassScheduleSeeder::class);

    expect(ClassSchedule::query()->where('class_period_id', '=', $breakPeriod->id)->exists())->toBeFalse();
});

test('class schedule seeder is idempotent for classrooms that already have schedules', function () {
    $academicYearId = AcademicYear::currentId();

    $school = School::factory()->create(['academic_period' => SchoolAcademicPeriod::MORNING]);
    $gradeLevel = GradeLevel::factory()->create();

    Subject::factory()->create(['grade_level_id' => $gradeLevel->id]);

    ClassPeriod::factory()->create([
        'academic_year_id' => $academicYearId,
        'academic_period' => SchoolAcademicPeriod::MORNING,
        'order' => 1,
        'is_break' => false,
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $this->seed(ClassScheduleSeeder::class);

    $countAfterFirstRun = ClassSchedule::query()->count();

    $this->seed(ClassScheduleSeeder::class);

    expect(ClassSchedule::query()->count())->toBe($countAfterFirstRun);
});

test('class schedule seeder skips classrooms without subjects for their grade level', function () {
    $academicYearId = AcademicYear::currentId();

    $school = School::factory()->create(['academic_period' => SchoolAcademicPeriod::MORNING]);
    $gradeLevelWithSubjects = GradeLevel::factory()->create();
    $gradeLevelWithoutSubjects = GradeLevel::factory()->create();

    Subject::factory()->create(['grade_level_id' => $gradeLevelWithSubjects->id]);

    ClassPeriod::factory()->create([
        'academic_year_id' => $academicYearId,
        'academic_period' => SchoolAcademicPeriod::MORNING,
        'order' => 1,
        'is_break' => false,
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevelWithSubjects->id,
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'academic_year_id' => $academicYearId,
        'grade_level_id' => $gradeLevelWithoutSubjects->id,
    ]);

    $this->seed(ClassScheduleSeeder::class);

    expect(ClassSchedule::query()->count())->toBe(DayOfWeek::schoolDays()->count());
});
