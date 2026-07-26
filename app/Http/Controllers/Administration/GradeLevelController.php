<?php

namespace App\Http\Controllers\Administration;

use App\Enums\SchoolEducationalStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\GradeLevelCollection;
use App\Models\GradeLevel;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GradeLevelController extends Controller
{
    public function index(Request $request): Response
    {
        $gradeLevels = QueryBuilder::for(GradeLevel::class)
            ->select([
                'id',
                'uuid',
                'name',
                'educational_stage',
                'order',
                'created_at',
                'deleted_at',
            ])
            ->withCount(['students'])
            ->allowedFilters(
                'name',
                AllowedFilter::exact('educational_stage'),
            )
            ->orderBy('order')
            ->get();

        return Inertia::render('administration/grade-levels/index', [
            'gradeLevels' => ResourcePayloadBuilder::make(
                GradeLevelCollection::make($gradeLevels),
            ),
            'filter' => $request->input('filter', []),
            'educationalStages' => SchoolEducationalStageEnum::optionsArray(),
        ]);
    }
}
