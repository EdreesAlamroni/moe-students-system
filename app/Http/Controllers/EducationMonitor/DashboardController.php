<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Enums\Gender;
use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('education-monitor/dashboard', [
            'summary' => Inertia::defer(function (): array {
                return $this->summary();
            }, 'summary'),
            'officeDistribution' => Inertia::defer(function (): Collection {
                return $this->officeDistribution();
            }, 'offices'),
            'schoolDistribution' => Inertia::defer(function (): Collection {
                return $this->schoolDistribution();
            }, 'schools'),
            'gradeLevelDistribution' => Inertia::defer(function (): Collection {
                return $this->gradeLevelDistribution();
            }, 'grade-levels'),
            'nationalityDistribution' => Inertia::defer(function (): Collection {
                return $this->nationalityDistribution();
            }, 'nationalities'),
            'schoolTypeDistribution' => Inertia::defer(function (): array {
                return $this->schoolTypeDistribution();
            }, 'school-types'),
        ]);
    }

    /**
     * Headline counts for the current education monitor.
     */
    private function summary(): array
    {
        $students = Student::query()
            ->forCurrentEducationMonitor()
            ->toBase()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS males', [Gender::MALE->value])
            ->selectRaw('SUM(CASE WHEN gender = ? THEN 1 ELSE 0 END) AS females', [Gender::FEMALE->value])
            ->selectRaw('COUNT(DISTINCT nationality_id) AS nationalities')
            ->selectRaw('SUM(CASE WHEN school_id IS NULL THEN 1 ELSE 0 END) AS unassigned_to_school')
            ->first();

        return [
            'students' => (int) $students->total,
            'males' => (int) $students->males,
            'females' => (int) $students->females,
            'nationalities' => (int) $students->nationalities,
            'education_services_offices' => EducationServicesOffice::query()->forCurrentEducationMonitor()->count(),
            'schools' => School::query()->forCurrentEducationMonitor()->count(),
            'grade_levels' => GradeLevel::query()->forCurrentEducationMonitor()->count(),
            'classrooms' => Classroom::query()
                ->forCurrentAcademicYear()
                ->whereIn('school_id', School::query()->forCurrentEducationMonitor()->select('id'))
                ->count(),
            'students_unassigned_to_school' => (int) $students->unassigned_to_school,
        ];
    }

    /**
     * Student counts (split by gender) and school counts per education services office,
     * largest office first. Feeds both office charts from a single payload.
     */
    private function officeDistribution(): Collection
    {
        $monitorId = auth('education_monitor')->user()?->organization_id;

        $students = Student::query()
            ->toBase()
            ->join('schools', 'schools.id', '=', 'students.school_id')
            ->where('students.education_monitor_id', '=', $monitorId)
            ->whereNotNull('students.school_id')
            ->whereNotNull('schools.education_services_office_id')
            ->whereNull('students.deleted_at')
            ->whereNull('schools.deleted_at')
            ->select('schools.education_services_office_id')
            ->selectRaw('SUM(CASE WHEN students.gender = ? THEN 1 ELSE 0 END) AS males', [Gender::MALE->value])
            ->selectRaw('SUM(CASE WHEN students.gender = ? THEN 1 ELSE 0 END) AS females', [Gender::FEMALE->value])
            ->selectRaw('COUNT(*) AS students')
            ->groupBy('schools.education_services_office_id')
            ->get()
            ->keyBy('education_services_office_id');

        return EducationServicesOffice::query()
            ->select(['id', 'name'])
            ->forCurrentEducationMonitor()
            ->withCount('schools')
            ->ordered()
            ->get()
            ->map(function (EducationServicesOffice $office) use ($students): array {
                $counts = $students->get($office->id);

                return [
                    'name' => $office->name,
                    'males' => (int) ($counts->males ?? 0),
                    'females' => (int) ($counts->females ?? 0),
                    'students' => (int) ($counts->students ?? 0),
                    'schools' => (int) $office->schools_count,
                ];
            })
            ->sortByDesc('students')
            ->values();
    }

    /**
     * Student and classroom counts for the largest schools under this monitor.
     */
    private function schoolDistribution(): Collection
    {
        /**
         * Maximum number of schools rendered in the school distribution charts.
         */
        $SCHOOL_SEGMENTS = 10;

        return School::query()
            ->select(['id', 'name', 'education_services_office_id'])
            ->forCurrentEducationMonitor()
            ->with(['office:id,name'])
            ->withCount(['students', 'classrooms'])
            ->orderByDesc('students_count')
            ->ordered()
            ->take($SCHOOL_SEGMENTS)
            ->get()
            ->map(fn (School $school): array => [
                'name' => $school->name,
                'students' => (int) $school->students_count,
                'classrooms' => (int) $school->classrooms_count,
                'office' => $school->office
                    ? ['name' => $school->office->name]
                    : null,
            ]);
    }

    /**
     * Student counts per grade level for the current academic year, split by gender.
     */
    private function gradeLevelDistribution(): Collection
    {
        return Student::query()
            ->forCurrentEducationMonitor()
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
     * Public vs private school and student counts for the current education monitor.
     */
    private function schoolTypeDistribution(): array
    {
        $monitorId = auth('education_monitor')->user()?->organization_id;

        $schools = School::query()
            ->forCurrentEducationMonitor()
            ->toBase()
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) AS public_schools', [SchoolType::PUBLIC->value])
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) AS private_schools', [SchoolType::PRIVATE->value])
            ->first();

        $students = Student::query()
            ->toBase()
            ->join('schools', 'schools.id', '=', 'students.school_id')
            ->where('students.education_monitor_id', '=', $monitorId)
            ->whereNotNull('students.school_id')
            ->whereNull('students.deleted_at')
            ->whereNull('schools.deleted_at')
            ->selectRaw('SUM(CASE WHEN schools.type = ? THEN 1 ELSE 0 END) AS public_students', [SchoolType::PUBLIC->value])
            ->selectRaw('SUM(CASE WHEN schools.type = ? THEN 1 ELSE 0 END) AS private_students', [SchoolType::PRIVATE->value])
            ->first();

        return [
            'public_schools' => (int) ($schools->public_schools ?? 0),
            'private_schools' => (int) ($schools->private_schools ?? 0),
            'public_students' => (int) ($students->public_students ?? 0),
            'private_students' => (int) ($students->private_students ?? 0),
        ];
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
            ->forCurrentEducationMonitor()
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
