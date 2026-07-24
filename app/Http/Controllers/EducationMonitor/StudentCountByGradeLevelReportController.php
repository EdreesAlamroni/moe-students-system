<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Authorization\EducationMonitor\StudentCountByGradeLevelReport;
use App\Enums\SchoolEducationalStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\EducationMonitor\StudentCountByGradeLevelCollection;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentCountByGradeLevelReportController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view', StudentCountByGradeLevelReport::class);

        $gradeLevels = $this->query()->get();

        return Inertia::render('education-monitor/reports/student-count-by-grade-level', [
            'gradeLevels' => ResourcePayloadBuilder::make(
                StudentCountByGradeLevelCollection::make($gradeLevels),
            ),
            'educationalStages' => SchoolEducationalStageEnum::optionsArray(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(StudentCountByGradeLevelReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', StudentCountByGradeLevelReport::class);

        $gradeLevels = $this->query()->get();

        return view('print.education-monitor.reports.student-count-by-grade-level', [
            'gradeLevels' => $gradeLevels,
            'academicYearName' => AcademicYear::currentName(),
        ]);
    }

    /**
     * @return Builder<GradeLevel>|QueryBuilder<GradeLevel>
     */
    private function query(): Builder|QueryBuilder
    {
        return QueryBuilder::for(GradeLevel::class)
            ->select([
                'grade_levels.id',
                'grade_levels.uuid',
                'grade_levels.name',
                'grade_levels.educational_stage',
                'grade_levels.order',
                'grade_levels.created_at',
                'grade_levels.deleted_at',
            ])
            ->forCurrentEducationMonitor()
            ->withCount([
                'students' => function ($query): void {
                    $query->forCurrentEducationMonitor();
                },
            ])
            ->allowedFilters(
                AllowedFilter::exact('educational_stage'),
            )
            ->ordered();
    }
}
