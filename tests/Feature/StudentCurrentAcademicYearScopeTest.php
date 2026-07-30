<?php

use App\Enums\Gender;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;

beforeEach(function () {
    AcademicYear::clearCachedCurrent();
});

test('current constraint id returns the current academic year id when one is selected', function () {
    $year = AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);

    expect(AcademicYear::currentConstraintId())->toBe($year->id);
});

test('current constraint id returns zero when no academic year is selected', function () {
    expect(AcademicYear::currentId())->toBeNull()
        ->and(AcademicYear::currentConstraintId())->toBe(0);
});

test('with current grade level returns an empty result without sql errors when no academic year exists', function () {
    $school = School::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();
    $student = Student::factory()->create([
        'school_id' => $school->id,
        'gender' => Gender::MALE,
    ]);

    $academicYear = AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => false,
    ]);

    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYear->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    AcademicYear::clearCachedCurrent();

    expect(AcademicYear::currentId())->toBeNull();

    $rows = Student::query()
        ->withCurrentGradeLevel()
        ->orderByGradeLevel()
        ->groupBy(['grade_levels.id', 'grade_levels.name', 'grade_levels.educational_stage', 'grade_levels.order'])
        ->toBase()
        ->selectRaw('grade_levels.name AS name')
        ->selectRaw('COUNT(*) AS students')
        ->get();

    expect($rows)->toHaveCount(0);
});

test('with current classroom returns an empty result without sql errors when no academic year exists', function () {
    expect(AcademicYear::currentId())->toBeNull();

    $rows = Student::query()
        ->withCurrentClassroom()
        ->groupBy(['classrooms.id', 'classrooms.name'])
        ->toBase()
        ->selectRaw('classrooms.name AS name')
        ->selectRaw('COUNT(*) AS students')
        ->get();

    expect($rows)->toHaveCount(0);
});
