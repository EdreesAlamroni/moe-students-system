<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Authorization\EducationMonitor\EducationServicesOfficeReport;
use App\Http\Controllers\Controller;
use App\Http\Resources\EducationMonitor\EducationServicesOfficeCollection;
use App\Models\AcademicYear;
use App\Models\EducationServicesOffice;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\QueryBuilder;

class EducationServicesOfficeReportController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view', EducationServicesOfficeReport::class);

        $offices = $this->query()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('education-monitor/reports/education-services-offices', [
            'offices' => ResourcePayloadBuilder::paginate(
                $offices,
                EducationServicesOfficeCollection::make($offices),
                $request,
            ),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(EducationServicesOfficeReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', EducationServicesOfficeReport::class);

        $offices = $this->query()->get();

        return view('print.education-monitor.reports.education-services-offices', [
            'offices' => $offices,
            'academicYearName' => AcademicYear::currentName(),
        ]);
    }

    /**
     * @return Builder<EducationServicesOffice>|QueryBuilder<EducationServicesOffice>
     */
    private function query(): Builder|QueryBuilder
    {
        return QueryBuilder::for(EducationServicesOffice::class)
            ->select([
                'id',
                'uuid',
                'education_monitor_id',
                'name',
                'created_at',
                'deleted_at',
            ])
            ->forCurrentEducationMonitor()
            ->withCount(['schools', 'students'])
            ->allowedFilters(
                'name',
            )
            ->ordered();
    }
}
