<?php

namespace App\Http\Controllers\Administration;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\Student;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('administration/dashboard', [
            'summary' => Inertia::defer(function (): array {
                return $this->summary();
            }, 'summary'),
            'educationMonitorDistribution' => Inertia::defer(function (): Collection {
                return $this->educationMonitorDistribution();
            }, 'education-monitors'),
            'schoolDistribution' => Inertia::defer(function (): Collection {
                return $this->schoolDistribution();
            }, 'schools'),
            'gradeLevelDistribution' => Inertia::defer(function (): Collection {
                return $this->gradeLevelDistribution();
            }, 'grade-levels'),
            'nationalityDistribution' => Inertia::defer(function (): Collection {
                return $this->nationalityDistribution();
            }, 'nationalities'),
        ]);
    }

    /**
     * Headline system-wide counts, using a single aggregate query for students.
     *
     * @return array{
     *     students: int,
     *     males: int,
     *     females: int,
     *     nationalities: int,
     *     education_monitors: int,
     *     education_services_offices: int,
     *     schools: int,
     *     warehouses: int,
     *     classrooms: int,
     * }
     */
    private function summary(): array
    {
        $students = Student::query()
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
            'nationalities' => (int) $students->nationalities,
            'education_monitors' => EducationMonitor::count(),
            'education_services_offices' => EducationServicesOffice::count(),
            'schools' => School::count(),
            'warehouses' => Warehouse::count(),
            'classrooms' => Classroom::forCurrentAcademicYear()->count(),
        ];
    }

    /**
     * Student counts (split by gender) and school counts per education monitor,
     * largest monitor first. Feeds both monitor charts from a single payload.
     *
     * @return Collection<int, array{name: string, males: int, females: int, students: int, schools: int}>
     */
    private function educationMonitorDistribution(): Collection
    {
        $students = Student::query()
            ->assignedToEducationMonitor()
            ->toBase()
            ->select('education_monitor_id')
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS males', [Gender::MALE->value])
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS females', [Gender::FEMALE->value])
            ->selectRaw('COUNT(*) AS students')
            ->groupBy('education_monitor_id')
            ->get()
            ->keyBy('education_monitor_id');

        return EducationMonitor::query()
            ->select(['id', 'name'])
            ->withCount('schools')
            ->ordered()
            ->get()
            ->map(function (EducationMonitor $monitor) use ($students): array {
                $counts = $students->get($monitor->id);

                return [
                    'name' => $monitor->name,
                    'males' => (int) ($counts->males ?? 0),
                    'females' => (int) ($counts->females ?? 0),
                    'students' => (int) ($counts->students ?? 0),
                    'schools' => (int) $monitor->schools_count,
                ];
            })
            ->sortByDesc('students')
            ->values();
    }

    /**
     * Student and classroom counts for the largest schools, feeding both school charts.
     *
     * @return Collection<int, array{name: string, students: int, classrooms: int, monitor: array{name: string}}>
     */
    private function schoolDistribution(): Collection
    {
        /**
         * Maximum number of schools rendered in the school distribution charts.
         */
        $SCHOOL_SEGMENTS = 10;

        return School::query()
            ->select(['id', 'name', 'education_monitor_id'])
            ->with(['monitor:id,name'])
            ->withCount(['students', 'classrooms'])
            ->orderByDesc('students_count')
            ->ordered()
            ->take($SCHOOL_SEGMENTS)
            ->get()
            ->map(fn (School $school): array => [
                'name' => $school->name,
                'students' => (int) $school->students_count,
                'classrooms' => (int) $school->classrooms_count,
                'monitor' => [
                    'name' => $school->monitor->name,
                ],
            ]);
    }

    /**
     * System-wide student counts per grade level for the current academic year, split by gender.
     *
     * @return Collection<int, array{name: string, males: int, females: int, students: int}>
     */
    private function gradeLevelDistribution(): Collection
    {
        return Student::query()
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
     * Student counts per nationality, largest first, with the tail merged into "Other".
     *
     * @return Collection<int, array{name: string, students: int}>
     */
    private function nationalityDistribution(): Collection
    {
        /**
         * Maximum number of segments rendered in the nationality distribution chart,
         * including the merged "Other" segment.
         */
        $NATIONALITY_SEGMENTS = 5;

        $rows = Student::query()
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
