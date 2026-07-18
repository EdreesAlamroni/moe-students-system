<?php

namespace App\Http\Controllers\Administration;

use App\Authorization\Administration\EducationServicesOfficeReport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\EducationServicesOfficeCollection;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
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

        $monitors = EducationMonitor::list();

        return Inertia::render('administration/reports/education-services-offices', [
            'offices' => ResourcePayloadBuilder::paginate(
                $offices,
                EducationServicesOfficeCollection::make($offices),
                $request,
            ),
            'monitors' => $monitors,
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(EducationServicesOfficeReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', EducationServicesOfficeReport::class);

        $offices = $this->query()->get();

        return view('print.administration.reports.education-services-offices', [
            'offices' => $offices,
        ]);
    }

    /**
     * @return Builder<EducationServicesOffice>|QueryBuilder<EducationServicesOffice>
     */
    private function query(): Builder|QueryBuilder
    {
        return QueryBuilder::for(EducationServicesOffice::class)
            ->select([
                'education_services_offices.id',
                'education_services_offices.uuid',
                'education_services_offices.education_monitor_id',
                'education_services_offices.name',
                'education_services_offices.created_at',
                'education_services_offices.deleted_at',
            ])
            ->with(['monitor:id,name'])
            ->withCount(['schools', 'students'])
            ->allowedFilters(
                AllowedFilter::exact('education_monitor_id'),
                'name',
            )
            ->orderedByMonitor();
    }
}
