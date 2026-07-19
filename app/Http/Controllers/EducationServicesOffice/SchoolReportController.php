<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Authorization\EducationServicesOffice\SchoolReport;
use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Resources\EducationServicesOffice\SchoolCollection;
use App\Models\AcademicYear;
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

        return Inertia::render('education-services-office/reports/schools', [
            'schools' => ResourcePayloadBuilder::paginate(
                $schools,
                SchoolCollection::make($schools),
                $request,
            ),
            'types' => SchoolType::optionsArray(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(SchoolReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', SchoolReport::class);

        $schools = $this->query()->get();

        return view('print.education-services-office.reports.schools', [
            'schools' => $schools,
            'academicYearName' => AcademicYear::currentName(),
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
                'schools.education_services_office_id',
                'schools.name',
                'schools.serial_number',
                'schools.type',
                'schools.academic_period',
                'schools.created_at',
                'schools.deleted_at',
            ])
            ->forCurrentEducationServicesOffice()
            ->withCount(['students'])
            ->allowedFilters(
                AllowedFilter::exact('type'),
                AllowedFilter::partial('name', 'schools.name'),
            )
            ->ordered();
    }
}
