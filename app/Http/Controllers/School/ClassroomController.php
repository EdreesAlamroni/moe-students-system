<?php

namespace App\Http\Controllers\School;

use App\Enums\SchoolEducationalStageEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\Classroom\StoreRequest;
use App\Http\Requests\School\Classroom\UpdateRequest;
use App\Http\Resources\School\ClassroomCollection;
use App\Http\Resources\School\ClassroomResource;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\GradeLevel;
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

class ClassroomController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Classroom::class);

        $classrooms = QueryBuilder::for(Classroom::class)
            ->select([
                'classrooms.id',
                'classrooms.uuid',
                'classrooms.school_id',
                'classrooms.academic_year_id',
                'classrooms.grade_level_id',
                'classrooms.name',
            ])
            ->forCurrentSchoolAndAcademicYear()
            ->with(['gradeLevel'])
            ->withCount(['students'])
            ->allowedFilters(
                AllowedFilter::exact('grade_level_id'),
                AllowedFilter::exact('name'),
            )
            ->ordered()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('school/classrooms/index', [
            'classrooms' => ResourcePayloadBuilder::paginateWithAbilities(
                $classrooms,
                ClassroomCollection::make($classrooms),
                ['view'],
                $request,
            ),
            'gradeLevels' => $this->gradeLevels(),
            'classroomNames' => $this->classroomNames(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(Classroom::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Classroom::class);

        return Inertia::render('school/classrooms/create', [
            'educationalStages' => $this->educationalStages(),
            'gradeLevels' => $this->gradeLevels(),
            'classroomNames' => $this->classroomNames(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Classroom::class);

        [$attributes, $values] = $request->getAttributes();

        $classroom = Classroom::firstOrCreate($attributes, $values);

        flash_success('create');

        return Redirect::route('school.classrooms.show', ['classroom' => $classroom]);
    }

    public function show(Classroom $classroom): Response
    {
        Gate::authorize('view', $classroom);

        $classroom->load([
            'gradeLevel',
        ])->loadCount([
            'students',
            'schedules',
        ]);

        return Inertia::render('school/classrooms/show', [
            'classroom' => ResourcePayloadBuilder::make(
                ClassroomResource::make($classroom),
            ),
            ...[
                ...ModelAbilityMap::make($classroom, ['update']),
                'canViewSchedule' => Gate::allows('view', [ClassSchedule::class, $classroom]),
            ],
        ]);
    }

    public function edit(Classroom $classroom): Response
    {
        Gate::authorize('update', $classroom);

        $classroom->load(['gradeLevel']);

        return Inertia::render('school/classrooms/edit', [
            'classroom' => ResourcePayloadBuilder::make(
                ClassroomResource::make($classroom),
            ),
        ]);
    }

    public function update(UpdateRequest $request, Classroom $classroom): RedirectResponse
    {
        Gate::authorize('update', $classroom);

        $classroom->update($request->getAttributes());

        flash_success('update');

        return Redirect::route('school.classrooms.show', ['classroom' => $classroom]);
    }

    private function gradeLevels(): array
    {
        return auth('school')->user()
            ->organization
            ->gradeLevels()
            ->select([
                'grade_levels.id',
                'grade_levels.name',
                'grade_levels.educational_stage',
                'grade_levels.order',
            ])
            ->ordered()
            ->get()
            ->map(function (GradeLevel $gradeLevel): array {
                return [
                    'id' => $gradeLevel->id,
                    'name' => $gradeLevel->name,
                    'educational_stage' => $gradeLevel->educational_stage->toArray(),
                ];
            })
            ->values()
            ->all();
    }

    private function educationalStages(): array
    {
        $stageIds = collect($this->gradeLevels())
            ->pluck('educational_stage.id')
            ->unique()
            ->values();

        return collect(SchoolEducationalStageEnum::optionsArray())
            ->filter(function (array $stage) use ($stageIds): bool {
                return $stageIds->contains($stage['id']);
            })
            ->values()
            ->all();
    }

    private function classroomNames(): array
    {
        return collect(
            array_map('strval', range(1, 12))
        )->map(function (string $name): array {
            return [
                'id' => $name,
                'name' => $name,
            ];
        })->all();
    }
}
