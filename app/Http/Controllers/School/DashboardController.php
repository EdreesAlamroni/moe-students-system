<?php

namespace App\Http\Controllers\School;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Student;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('school/dashboard', [
            'summary' => Inertia::defer(function (): array {
                return $this->summary();
            }, 'summary'),
            'gradeLevelDistribution' => Inertia::defer(function (): Collection {
                return $this->gradeLevelDistribution();
            }, 'grade-levels'),
            'classroomOccupancy' => Inertia::defer(function (): Collection {
                return $this->classroomOccupancy();
            }, 'classrooms'),
            'nationalityDistribution' => Inertia::defer(function (): Collection {
                return $this->nationalityDistribution();
            }, 'nationalities'),
        ]);
    }

    /**
     * Headline counts for the current school, using a single aggregate query for students.
     */
    private function summary(): array
    {
        $students = Student::query()
            ->forCurrentSchool()
            ->toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS males', [Gender::MALE->value])
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS females', [Gender::FEMALE->value])
            ->selectRaw('COUNT(DISTINCT nationality_id) AS nationalities')
            ->first();

        return [
            'students' => (int) $students->total,
            'males' => (int) $students->males,
            'females' => (int) $students->females,
            'grade_levels' => GradeLevel::query()->forCurrentSchoolAndAcademicYear()->count(),
            'classrooms' => Classroom::query()->forCurrentSchoolAndAcademicYear()->count(),
            'nationalities' => (int) $students->nationalities,
        ];
    }

    /**
     * Student counts per grade level for the current academic year, split by gender.
     */
    private function gradeLevelDistribution(): Collection
    {
        return Student::query()
            ->forCurrentSchool()
            ->withCurrentGradeLevel()
            ->orderByGradeLevel()
            ->groupBy(['grade_levels.id', 'grade_levels.name', 'grade_levels.educational_stage', 'grade_levels.order'])
            ->toBase()
            ->selectRaw('grade_levels.name AS name')
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS males', [Gender::MALE->value])
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS females', [Gender::FEMALE->value])
            ->selectRaw('COUNT(*) AS students')
            ->get()
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'males' => (int) $row->males,
                'females' => (int) $row->females,
                'students' => (int) $row->students,
            ]);
    }

    /**
     * Enrolled student counts and capacity per classroom for the current academic year.
     */
    private function classroomOccupancy(): Collection
    {
        return Classroom::query()
            ->select(['classrooms.id', 'classrooms.name', 'classrooms.capacity', 'grade_levels.name as grade_level_name'])
            ->forCurrentSchoolAndAcademicYear()
            ->withCount('students')
            ->ordered()
            ->get()
            ->map(fn (Classroom $classroom): array => [
                'name' => $classroom->name,
                'grade_level' => (string) $classroom->getAttribute('grade_level_name'),
                'students' => (int) $classroom->students_count,
                'capacity' => (int) $classroom->capacity,
            ]);
    }

    /**
     * Student counts per nationality, largest first, with the tail merged into "Other".
     */
    private function nationalityDistribution(): Collection
    {
        /**
         * Maximum number of segments rendered in the nationality distribution chart,
         * including the merged "Other" segment.
         */
        $NATIONALITY_SEGMENTS = 5;

        $rows = Student::query()
            ->forCurrentSchool()
            ->toBase()
            ->join('nationalities', 'nationalities.id', '=', 'students.nationality_id')
            ->selectRaw('nationalities.name as name, count(*) as students')
            ->groupBy(['nationalities.id', 'nationalities.name'])
            ->orderByDesc('students')
            ->orderBy('nationalities.name')
            ->get()
            ->map(fn (object $row): array => [
                'name' => (string) $row->name,
                'students' => (int) $row->students,
            ]);

        if ($rows->count() <= $NATIONALITY_SEGMENTS) {
            return $rows->values();
        }

        return $rows
            ->take($NATIONALITY_SEGMENTS - 1)
            ->push([
                'name' => 'أخرى',
                'students' => $rows->skip($NATIONALITY_SEGMENTS - 1)->sum('students'),
            ])
            ->values();
    }
}
