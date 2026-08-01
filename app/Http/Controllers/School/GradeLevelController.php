<?php

namespace App\Http\Controllers\School;

use App\Actions\School\CreateGradeLevels;
use App\Enums\SchoolEducationalStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\GradeLevel\StoreRequest;
use App\Http\Resources\School\GradeLevelCollection;
use App\Http\Resources\School\GradeLevelResource;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchool;
use App\Models\School;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GradeLevelController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', GradeLevel::class);

        /** @var School $school */
        $school = auth('school')->user()->organization;

        $gradeLevels = QueryBuilder::for(GradeLevel::class)
            ->select([
                'grade_levels.id',
                'grade_levels.uuid',
                'grade_levels.name',
                'grade_levels.educational_stage',
                'grade_levels.order',
                'grade_levels.created_at',
                'grade_levels.deleted_at',
            ])
            ->with(['schools'])
            ->withCount([
                'students',
            ])
            ->forCurrentSchoolAndAcademicYear()
            ->allowedFilters(
                'name',
                AllowedFilter::exact('educational_stage'),
            )
            ->ordered()
            ->get();

        $availableGradeLevels = Gate::allows('create', GradeLevel::class)
                ? $school->availableGradeLevels()
                : collect([]);

        return Inertia::render('school/grade-levels/index', [
            'gradeLevels' => ResourcePayloadBuilder::collectionWithAbilities(
                GradeLevelCollection::make($gradeLevels),
                ['view'],
            ),
            'educationalStages' => SchoolEducationalStageEnum::optionsArray(),
            'availableGradeLevels' => $availableGradeLevels,
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(GradeLevel::class, ['create']),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', GradeLevel::class);

        /** @var School $school */
        $school = auth('school')->user()->organization;

        $gradeLevels = $request->gradeLevelsToAttach();

        app(CreateGradeLevels::class)->execute($school, $gradeLevels);

        flash_success('grade-levels-added', ['count' => count($gradeLevels)]);

        return Redirect::back();
    }

    public function show(GradeLevel $gradeLevel): Response
    {
        Gate::authorize('view', $gradeLevel);

        $gradeLevel->loadCount([
            'classrooms',
            'students',
        ]);

        return Inertia::render('school/grade-levels/show', [
            'gradeLevel' => ResourcePayloadBuilder::make(
                GradeLevelResource::make($gradeLevel),
            ),
            ...ModelAbilityMap::make($gradeLevel, ['delete']),
        ]);
    }

    public function destroy(GradeLevel $gradeLevel): RedirectResponse
    {
        Gate::authorize('delete', $gradeLevel);

        GradeLevelSchool::query()
            ->where('grade_level_id', '=', $gradeLevel->id)
            ->where('school_id', '=', auth('school')->user()->organization_id)
            ->where('academic_year_id', '=', AcademicYear::currentId())
            ->delete();

        flash_success('delete');

        return Redirect::route('school.grade-levels.index');
    }
}
