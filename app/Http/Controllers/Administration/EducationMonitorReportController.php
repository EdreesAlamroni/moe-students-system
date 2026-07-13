<?php

namespace App\Http\Controllers\Administration;

use App\Authorization\Administration\EducationMonitorReport;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\EducationMonitorCollection;
use App\Models\EducationMonitor;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EducationMonitorReportController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('view', EducationMonitorReport::class);

        $monitors = $this->query()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/reports/education-monitor', [
            'monitors' => ResourcePayloadBuilder::paginate(
                $monitors,
                EducationMonitorCollection::make($monitors),
                $request,
            ),
            ...ModelAbilityMap::make(EducationMonitorReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', EducationMonitorReport::class);

        $monitors = $this->query()->get();

        return view('print.administration.reports.education-monitors', [
            'monitors' => $monitors,
            'count' => $monitors->count(),
            'printedBy' => auth('administration')->user()->name,
        ]);
    }

    /**
     * @return Builder<EducationMonitor>
     */
    private function query(): Builder
    {
        return EducationMonitor::query()
            ->select([
                'id',
                'uuid',
                'municipal_id',
                'name',
                'created_at',
                'deleted_at',
            ])
            ->withCount(['offices', 'schools', 'students'])
            ->ordered();
    }
}
