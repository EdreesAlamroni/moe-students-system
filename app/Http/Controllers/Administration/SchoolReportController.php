<?php

namespace App\Http\Controllers\Administration;

use App\Authorization\Administration\SchoolReport;
use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\SchoolCollection;
use App\Models\EducationMonitor;
use App\Models\School;
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

class SchoolReportController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view', SchoolReport::class);

        $schools = $this->query()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        $monitors = EducationMonitor::list();
        $types = SchoolType::options();

        return Inertia::render('administration/reports/schools', [
            'schools' => ResourcePayloadBuilder::paginate(
                $schools,
                SchoolCollection::make($schools),
                $request,
            ),
            'monitors' => $monitors,
            'types' => $types,
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(SchoolReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', SchoolReport::class);

        $schools = $this->query()->get();

        return view('print.administration.reports.schools', [
            'schools' => $schools,
            'count' => $schools->count(),
            'printedBy' => auth('administration')->user()->name,
        ]);
    }

    /**
     * @return Builder<School>|QueryBuilder<School>
     */
    private function query(): Builder|QueryBuilder
    {
        return QueryBuilder::for(School::class)
            ->select([
                'schools.id',
                'schools.uuid',
                'schools.education_monitor_id',
                'schools.name',
                'schools.serial_number',
                'schools.type',
                'schools.academic_period',
                'schools.created_at',
                'schools.deleted_at',
            ])
            ->with(['monitor:id,name'])
            ->withCount(['students'])
            ->allowedFilters(
                AllowedFilter::exact('education_monitor_id'),
                AllowedFilter::exact('type'),
                'name',
            )
            ->orderedByMonitor();
    }
}
