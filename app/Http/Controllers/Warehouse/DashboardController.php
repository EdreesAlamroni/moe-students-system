<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('warehouse/dashboard', [
            'summary' => Inertia::defer(function (): array {
                return $this->summary();
            }, 'summary'),
            'educationMonitorDistribution' => Inertia::defer(function (): Collection {
                return $this->educationMonitorDistribution();
            }, 'education-monitors'),
            'schoolDistribution' => Inertia::defer(function (): Collection {
                return $this->schoolDistribution();
            }, 'schools'),
            'recentActivities' => Inertia::defer(function () {
                return $this->recentActivities();
            }, 'recent'),
        ]);
    }

    /**
     * Headline warehouse counts for the current academic year.
     */
    private function summary(): array
    {
        $studentsReceived = $this->studentsReceivedCount();
        $studentsPending = $this->studentsPendingCount();
        $eligible = $studentsReceived + $studentsPending;

        return [
            'education_monitors' => EducationMonitor::forCurrentWarehouse()->count(),
            'schools' => School::forCurrentWarehouse()->count(),
            'students' => $this->warehouseStudentsQuery()->count(),
            'book_distributions' => BookDistribution::query()
                ->forCurrentWarehouse()
                ->forCurrentAcademicYear()
                ->count(),
            'students_received' => $studentsReceived,
            'students_pending' => $studentsPending,
            'completion_rate' => $eligible > 0
                ? round(($studentsReceived / $eligible) * 100, 1)
                : 0.0,
        ];
    }

    /**
     * Student and book-distribution progress per education monitor, largest student count first.
     */
    private function educationMonitorDistribution(): Collection
    {
        $students = $this->warehouseStudentsQuery()
            ->toBase()
            ->select('education_monitor_id')
            ->selectRaw('COUNT(*) AS students')
            ->groupBy('education_monitor_id')
            ->pluck('students', 'education_monitor_id');

        $distributions = $this->bookDistributionsByMonitor();
        $received = $this->studentsReceivedByMonitor();
        $pending = $this->studentsPendingByMonitor();

        return EducationMonitor::query()
            ->select(['id', 'name'])
            ->forCurrentWarehouse()
            ->withCount('schools')
            ->ordered()
            ->get()
            ->map(function (EducationMonitor $monitor) use ($students, $distributions, $received, $pending): array {
                $studentsReceived = (int) ($received[$monitor->id] ?? 0);
                $studentsPending = (int) ($pending[$monitor->id] ?? 0);
                $eligible = $studentsReceived + $studentsPending;

                return [
                    'name' => $monitor->name,
                    'students' => (int) ($students[$monitor->id] ?? 0),
                    'schools' => (int) $monitor->schools_count,
                    'book_distributions' => (int) ($distributions[$monitor->id] ?? 0),
                    'students_received' => $studentsReceived,
                    'students_pending' => $studentsPending,
                    'completion_rate' => $eligible > 0
                        ? round(($studentsReceived / $eligible) * 100, 1)
                        : 0.0,
                ];
            })
            ->sortByDesc('students')
            ->values();
    }

    /**
     * Student and book-distribution progress for the largest schools under this warehouse.
     */
    private function schoolDistribution(): Collection
    {
        /**
         * Maximum number of schools rendered in the school distribution charts.
         */
        $SCHOOL_SEGMENTS = 10;

        $distributions = $this->bookDistributionsBySchoolPeriod();
        $received = $this->studentsReceivedBySchoolPeriod();
        $pending = $this->studentsPendingBySchoolPeriod();

        return SchoolPeriod::query()
            ->select(['id', 'name', 'education_monitor_id'])
            ->forCurrentWarehouse()
            ->with(['monitor:id,name'])
            ->withCount('students')
            ->orderByDesc('students_count')
            ->ordered()
            ->take($SCHOOL_SEGMENTS)
            ->get()
            ->map(function (SchoolPeriod $schoolPeriod) use ($distributions, $received, $pending): array {
                $studentsReceived = (int) ($received[$schoolPeriod->id] ?? 0);
                $studentsPending = (int) ($pending[$schoolPeriod->id] ?? 0);
                $eligible = $studentsReceived + $studentsPending;

                return [
                    'name' => $schoolPeriod->name,
                    'students' => (int) $schoolPeriod->students_count,
                    'book_distributions' => (int) ($distributions[$schoolPeriod->id] ?? 0),
                    'students_received' => $studentsReceived,
                    'students_pending' => $studentsPending,
                    'completion_rate' => $eligible > 0
                        ? round(($studentsReceived / $eligible) * 100, 1)
                        : 0.0,
                    'monitor' => [
                        'name' => $schoolPeriod->monitor->name,
                    ],
                ];
            });
    }

    /**
     * Latest warehouse book-distribution confirmations.
     */
    private function recentActivities(): Collection
    {
        /**
         * Maximum number of recent distribution activities shown on the dashboard.
         */
        $RECENT_ACTIVITIES = 8;

        return BookDistribution::query()
            ->select([
                'id',
                'distributed_at',
                'school_period_id',
                'grade_level_id',
                'education_monitor_id',
            ])
            ->forCurrentWarehouse()
            ->forCurrentAcademicYear()
            ->with([
                'schoolPeriod:id,name',
                'gradeLevel:id,name',
                'monitor:id,name',
            ])
            ->latest('distributed_at')
            ->latest('id')
            ->take($RECENT_ACTIVITIES)
            ->get()
            ->map(fn (BookDistribution $distribution): array => [
                'id' => $distribution->id,
                'distributed_at' => $distribution->distributed_at->toDateTimeString(),
                'school' => $distribution->schoolPeriod->name,
                'grade_level' => $distribution->gradeLevel->name,
                'monitor' => $distribution->monitor->name,
            ])
            ->values();
    }

    private function warehouseStudentsQuery(): Builder
    {
        return Student::query()->whereIn('education_monitor_id', $this->warehouseMonitorIdsQuery());
    }

    private function warehouseMonitorIdsQuery(): Builder
    {
        return EducationMonitor::query()
            ->forCurrentWarehouse()
            ->select('id');
    }

    private function warehouseId(): ?int
    {
        return auth('warehouse')->user()?->organization_id;
    }

    private function studentsReceivedCount(): int
    {
        $warehouseId = $this->warehouseId();
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($warehouseId) || is_null($currentAcademicYearId)) {
            return 0;
        }

        return (int) BookDistributionItem::query()
            ->toBase()
            ->join('book_distributions', 'book_distributions.id', '=', 'book_distribution_items.book_distribution_id')
            ->where('book_distributions.warehouse_id', '=', $warehouseId)
            ->where('book_distribution_items.academic_year_id', '=', $currentAcademicYearId)
            ->selectRaw('COUNT(DISTINCT book_distribution_items.student_id) AS aggregate')
            ->value('aggregate');
    }

    private function studentsPendingCount(): int
    {
        $warehouseId = $this->warehouseId();
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($warehouseId) || is_null($currentAcademicYearId)) {
            return 0;
        }

        return (int) $this->pendingEnrollmentsQuery($warehouseId, $currentAcademicYearId)
            ->selectRaw('COUNT(DISTINCT student_enrollments.student_id) AS aggregate')
            ->value('aggregate');
    }

    private function bookDistributionsByMonitor(): Collection
    {
        return BookDistribution::query()
            ->forCurrentWarehouse()
            ->forCurrentAcademicYear()
            ->toBase()
            ->select('education_monitor_id')
            ->selectRaw('COUNT(*) AS aggregate')
            ->groupBy('education_monitor_id')
            ->pluck('aggregate', 'education_monitor_id');
    }

    private function bookDistributionsBySchoolPeriod(): Collection
    {
        return BookDistribution::query()
            ->forCurrentWarehouse()
            ->forCurrentAcademicYear()
            ->toBase()
            ->select('school_period_id')
            ->selectRaw('COUNT(*) AS aggregate')
            ->groupBy('school_period_id')
            ->pluck('aggregate', 'school_period_id');
    }

    private function studentsReceivedByMonitor(): Collection
    {
        $warehouseId = $this->warehouseId();
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($warehouseId) || is_null($currentAcademicYearId)) {
            return collect();
        }

        return BookDistributionItem::query()
            ->toBase()
            ->join('book_distributions', 'book_distributions.id', '=', 'book_distribution_items.book_distribution_id')
            ->select('book_distributions.education_monitor_id')
            ->selectRaw('COUNT(DISTINCT book_distribution_items.student_id) AS aggregate')
            ->where('book_distributions.warehouse_id', '=', $warehouseId)
            ->where('book_distribution_items.academic_year_id', '=', $currentAcademicYearId)
            ->groupBy('book_distributions.education_monitor_id')
            ->pluck('aggregate', 'education_monitor_id');
    }

    private function studentsReceivedBySchoolPeriod(): Collection
    {
        $warehouseId = $this->warehouseId();
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($warehouseId) || is_null($currentAcademicYearId)) {
            return collect();
        }

        return BookDistributionItem::query()
            ->toBase()
            ->join('book_distributions', 'book_distributions.id', '=', 'book_distribution_items.book_distribution_id')
            ->select('book_distribution_items.school_period_id')
            ->selectRaw('COUNT(DISTINCT book_distribution_items.student_id) AS aggregate')
            ->where('book_distributions.warehouse_id', '=', $warehouseId)
            ->where('book_distribution_items.academic_year_id', '=', $currentAcademicYearId)
            ->groupBy('book_distribution_items.school_period_id')
            ->pluck('aggregate', 'school_period_id');
    }

    private function studentsPendingByMonitor(): Collection
    {
        $warehouseId = $this->warehouseId();
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($warehouseId) || is_null($currentAcademicYearId)) {
            return collect();
        }

        return $this->pendingEnrollmentsQuery($warehouseId, $currentAcademicYearId)
            ->select('book_distributions.education_monitor_id')
            ->selectRaw('COUNT(DISTINCT student_enrollments.student_id) AS aggregate')
            ->groupBy('book_distributions.education_monitor_id')
            ->pluck('aggregate', 'education_monitor_id');
    }

    private function studentsPendingBySchoolPeriod(): Collection
    {
        $warehouseId = $this->warehouseId();
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($warehouseId) || is_null($currentAcademicYearId)) {
            return collect();
        }

        return $this->pendingEnrollmentsQuery($warehouseId, $currentAcademicYearId)
            ->select('student_enrollments.school_period_id')
            ->selectRaw('COUNT(DISTINCT student_enrollments.student_id) AS aggregate')
            ->groupBy('student_enrollments.school_period_id')
            ->pluck('aggregate', 'school_period_id');
    }

    /**
     * Enrollments in warehouse-confirmed grade levels that still lack a student book item.
     */
    private function pendingEnrollmentsQuery(int $warehouseId, int $currentAcademicYearId): QueryBuilder
    {
        return StudentEnrollment::query()
            ->toBase()
            ->join('book_distributions', function (JoinClause $join) use ($warehouseId, $currentAcademicYearId): void {
                $join->on('book_distributions.school_period_id', '=', 'student_enrollments.school_period_id')
                    ->on('book_distributions.grade_level_id', '=', 'student_enrollments.grade_level_id')
                    ->where('book_distributions.academic_year_id', '=', $currentAcademicYearId)
                    ->where('book_distributions.warehouse_id', '=', $warehouseId);
            })
            ->leftJoin('book_distribution_items', function (JoinClause $join) use ($currentAcademicYearId): void {
                $join->on('book_distribution_items.student_id', '=', 'student_enrollments.student_id')
                    ->where('book_distribution_items.academic_year_id', '=', $currentAcademicYearId);
            })
            ->where('student_enrollments.academic_year_id', '=', $currentAcademicYearId)
            ->whereNull('book_distribution_items.id');
    }
}
