<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Enums\Gender;
use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('education-services-office/dashboard', [
            'summary' => Inertia::defer(function (): array {
                return $this->summary();
            }, 'summary'),
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
     * Headline counts for the current education services office.
     */
    private function summary(): array
    {
        $students = Student::query()
            ->forCurrentEducationServicesOffice()
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
            'schools' => School::query()->forCurrentEducationServicesOffice()->count(),
            'grade_levels' => GradeLevel::query()->forCurrentEducationServicesOffice()->count(),
            'classrooms' => Classroom::query()
                ->forCurrentAcademicYear()
                ->whereIn('school_period_id', SchoolPeriod::query()->forCurrentEducationServicesOffice()->select('id'))
                ->count(),
            'students_unenrolled_in_grade_level' => Student::query()
                ->forCurrentEducationServicesOffice()
                ->unenrolledFromGradeLevel()
                ->count(),
        ];
    }

    /**
     * Student and classroom counts for the largest schools under this office.
     */
    private function schoolDistribution(): Collection
    {
        /**
         * Maximum number of schools rendered in the school distribution charts.
         */
        $SCHOOL_SEGMENTS = 10;

        return SchoolPeriod::query()
            ->select(['id', 'name'])
            ->forCurrentEducationServicesOffice()
            ->withCount(['students', 'classrooms'])
            ->orderByDesc('students_count')
            ->ordered()
            ->take($SCHOOL_SEGMENTS)
            ->get()
            ->map(fn (SchoolPeriod $schoolPeriod): array => [
                'name' => $schoolPeriod->name,
                'students' => (int) $schoolPeriod->students_count,
                'classrooms' => (int) $schoolPeriod->classrooms_count,
            ]);
    }

    /**
     * Student counts per grade level for the current academic year, split by gender.
     */
    private function gradeLevelDistribution(): Collection
    {
        return Student::query()
            ->forCurrentEducationServicesOffice()
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
     */
    private function nationalityDistribution(): Collection
    {
        /**
         * Maximum number of segments rendered in the nationality distribution chart,
         * including the merged "Other" segment.
         */
        $NATIONALITY_SEGMENTS = 5;

        $rows = Student::query()
            ->forCurrentEducationServicesOffice()
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

    /**
     * Public vs private school and student counts for the current office,
     * plus the largest school of each type.
     */
    private function schoolTypeDistribution(): array
    {
        $officeId = auth('education_services_office')->user()?->organization_id;

        $schools = School::query()
            ->forCurrentEducationServicesOffice()
            ->toBase()
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) AS public_schools', [SchoolType::PUBLIC->value])
            ->selectRaw('SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) AS private_schools', [SchoolType::PRIVATE->value])
            ->first();

        $students = Student::query()
            ->toBase()
            ->join('school_periods', 'school_periods.id', '=', 'students.school_period_id')
            ->join('schools', 'schools.id', '=', 'school_periods.school_id')
            ->where('school_periods.education_services_office_id', '=', $officeId)
            ->whereNull('students.deleted_at')
            ->whereNull('school_periods.deleted_at')
            ->whereNull('schools.deleted_at')
            ->selectRaw('SUM(CASE WHEN schools.type = ? THEN 1 ELSE 0 END) AS public_students', [SchoolType::PUBLIC->value])
            ->selectRaw('SUM(CASE WHEN schools.type = ? THEN 1 ELSE 0 END) AS private_students', [SchoolType::PRIVATE->value])
            ->first();

        return [
            'public_schools' => (int) ($schools->public_schools ?? 0),
            'private_schools' => (int) ($schools->private_schools ?? 0),
            'public_students' => (int) ($students->public_students ?? 0),
            'private_students' => (int) ($students->private_students ?? 0),
            'largest_public_school' => $this->largestSchoolOfType(SchoolType::PUBLIC),
            'largest_private_school' => $this->largestSchoolOfType(SchoolType::PRIVATE),
        ];
    }

    /**
     * The school of the given type with the most students, or null when
     * no school of that type has any students.
     *
     * @return array{name: string, students: int}|null
     */
    private function largestSchoolOfType(SchoolType $type): ?array
    {
        $schoolPeriod = SchoolPeriod::query()
            ->select(['id', 'name'])
            ->forCurrentEducationServicesOffice()
            ->whereHas('school', function (Builder $query) use ($type): void {
                $query->where('type', '=', $type);
            })
            ->withCount(['students'])
            ->orderByDesc('students_count')
            ->first();

        if (is_null($schoolPeriod) || (int) $schoolPeriod->students_count === 0) {
            return null;
        }

        return [
            'name' => $schoolPeriod->name,
            'students' => (int) $schoolPeriod->students_count,
        ];
    }
}
