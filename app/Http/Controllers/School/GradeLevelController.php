<?php

namespace App\Http\Controllers\School;

use App\Enums\SchoolEducationalStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\School\GradeLevelCollection;
use App\Models\GradeLevel;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GradeLevelController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', GradeLevel::class);

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

        return Inertia::render('school/grade-levels/index', [
            'gradeLevels' => ResourcePayloadBuilder::make(
                GradeLevelCollection::make($gradeLevels),
            ),
            'educationalStages' => SchoolEducationalStageEnum::optionsArray(),
            'filter' => $request->input('filter', []),
        ]);
    }
}
