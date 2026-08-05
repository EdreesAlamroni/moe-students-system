<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Pipelines\School\CreateEducationalStages;
use App\Http\Pipelines\School\CreateGradeLevels;
use App\Http\Pipelines\School\CreateSchoolRecords;
use App\Http\Requests\EducationServicesOffice\School\StoreRequest;
use App\Http\Requests\EducationServicesOffice\School\UpdateRequest;
use App\Http\Resources\EducationServicesOffice\GradeLevelCollection;
use App\Http\Resources\EducationServicesOffice\SchoolCollection;
use App\Http\Resources\EducationServicesOffice\SchoolFormResource;
use App\Http\Resources\EducationServicesOffice\SchoolResource;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SchoolController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', School::class);

        $schools = QueryBuilder::for(School::class)
            ->select([
                'schools.id',
                'schools.uuid',
                'schools.education_monitor_id',
                'schools.education_services_office_id',
                'schools.name',
                'schools.serial_number',
                'schools.type',
                'schools.created_at',
                'schools.deleted_at',
            ])
            ->forCurrentEducationServicesOffice()
            ->with([
                'periods' => function ($query): void {
                    $query
                        ->select(['id', 'uuid', 'school_id', 'name', 'academic_period'])
                        ->withCount(['students']);
                },
            ])
            ->allowedFilters(
                AllowedFilter::exact('type'),
                AllowedFilter::partial('name', 'schools.name'),
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('education-services-office/schools/index', [
            'schools' => ResourcePayloadBuilder::paginateWithAbilities(
                $schools,
                SchoolCollection::make($schools),
                ['view'],
                $request,
            ),
            'types' => SchoolType::optionsArray(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(School::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', School::class);

        $gradeLevels = GradeLevel::query()
            ->select(['id', 'name', 'educational_stage'])
            ->ordered()
            ->get();

        return Inertia::render('education-services-office/schools/create', [
            'types' => SchoolType::optionsArray(),
            'academicPeriods' => SchoolAcademicPeriod::optionsArray(),
            'studentsGender' => SchoolStudentsGender::optionsArray(),
            'branchTypes' => SchoolBranchType::optionsArray(),
            'buildingTypes' => SchoolBuildingType::optionsArray(),
            'educationalStages' => SchoolEducationalStageEnum::optionsArray(),
            'gradeLevels' => ResourcePayloadBuilder::make(
                GradeLevelCollection::make($gradeLevels),
            ),
            'schoolPrivateType' => SchoolType::PRIVATE->value,
            'schoolDualAcademicPeriod' => SchoolAcademicPeriod::DUAL_PERIOD->value,
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', School::class);

        DB::transaction(function () use ($request): void {
            app(Pipeline::class)
                ->send($request)
                ->through([
                    CreateSchoolRecords::class,
                    CreateEducationalStages::class,
                    CreateGradeLevels::class,
                ])
                ->thenReturn();
        });

        flash_success('create');

        return Redirect::route('education-services-office.schools.index');
    }

    public function show(School $school): Response
    {
        Gate::authorize('view', $school);

        $school->load([
            'monitor:id,uuid,name',
            'office:id,uuid,name',
            'periods' => function ($query): void {
                $query
                    ->with(['educationalStages'])
                    ->withCount(['gradeLevels', 'classrooms', 'students'])
                    ->orderedByAcademicPeriod();
            },
        ]);

        $schoolPeriodIds = $school->periods->pluck('id');

        $gradeLevels = GradeLevel::query()
            ->select(['grade_levels.id', 'grade_levels.name', 'grade_levels.educational_stage'])
            ->whereIn('grade_levels.id', function ($query) use ($schoolPeriodIds): void {
                $query
                    ->select('grade_level_id')
                    ->from('grade_level_school_period')
                    ->where('academic_year_id', '=', AcademicYear::currentId())
                    ->whereIn('school_period_id', $schoolPeriodIds);
            })
            ->withCount([
                'students' => function ($query) use ($schoolPeriodIds): void {
                    $query->whereIn('students.school_period_id', $schoolPeriodIds);
                },
            ])
            ->ordered()
            ->get();

        return Inertia::render('education-services-office/schools/show', [
            'school' => ResourcePayloadBuilder::make(
                SchoolResource::make($school),
            ),
            'gradeLevels' => ResourcePayloadBuilder::make(
                GradeLevelCollection::make($gradeLevels),
            ),
            ...ModelAbilityMap::make($school, ['update', 'delete']),
        ]);
    }

    public function edit(School $school): Response
    {
        Gate::authorize('update', $school);

        $school->load(['office:id,uuid,name']);

        return Inertia::render('education-services-office/schools/edit', [
            'school' => ResourcePayloadBuilder::make(
                SchoolFormResource::make($school),
            ),
            'branchTypes' => SchoolBranchType::optionsArray(),
            'buildingTypes' => SchoolBuildingType::optionsArray(),
        ]);
    }

    public function update(UpdateRequest $request, School $school): RedirectResponse
    {
        Gate::authorize('update', $school);

        DB::transaction(function () use ($request, $school): void {
            $school->update($request->getAttributes());
        });

        flash_success('update');

        return Redirect::route('education-services-office.schools.show', ['school' => $school]);
    }

    public function destroy(School $school): RedirectResponse
    {
        Gate::authorize('delete', $school);

        $school->delete();

        flash_success('delete');

        return Redirect::route('education-services-office.schools.index');
    }
}
