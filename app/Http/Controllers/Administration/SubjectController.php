<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Administration\Subject\StoreRequest;
use App\Http\Requests\Administration\Subject\UpdateRequest;
use App\Http\Resources\Administration\SubjectCollection;
use App\Http\Resources\Administration\SubjectResource;
use App\Models\GradeLevel;
use App\Models\Subject;
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

class SubjectController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Subject::class);

        $subjects = QueryBuilder::for(Subject::class)
            ->select([
                'id',
                'uuid',
                'grade_level_id',
                'name',
                'code',
                'created_at',
                'deleted_at',
            ])
            ->with(['gradeLevel:id,name'])
            ->allowedFilters(
                AllowedFilter::exact('grade_level_id'),
                AllowedFilter::partial('name'),
            )
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/subjects/index', [
            'subjects' => ResourcePayloadBuilder::paginateWithAbilities(
                $subjects,
                SubjectCollection::make($subjects),
                ['view'],
            ),
            'gradeLevels' => GradeLevel::list(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(Subject::class, ['create']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Subject::class);

        return Inertia::render('administration/subjects/create', [
            'gradeLevels' => GradeLevel::list(),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('create', Subject::class);

        $subject = Subject::create($request->getAttributes());

        flash_success('create');

        return Redirect::route('administration.subjects.show', ['subject' => $subject]);
    }

    public function show(Subject $subject): Response
    {
        Gate::authorize('view', $subject);

        $subject->load(['gradeLevel:id,name']);

        return Inertia::render('administration/subjects/show', [
            'subject' => ResourcePayloadBuilder::make(
                SubjectResource::make($subject),
            ),
            ...ModelAbilityMap::make($subject, ['update', 'delete']),
        ]);
    }

    public function edit(Subject $subject): Response
    {
        Gate::authorize('update', $subject);

        $subject->load(['gradeLevel:id,name']);

        return Inertia::render('administration/subjects/edit', [
            'subject' => ResourcePayloadBuilder::make(
                SubjectResource::make($subject),
            ),
            'gradeLevels' => GradeLevel::list(),
        ]);
    }

    public function update(UpdateRequest $request, Subject $subject): RedirectResponse
    {
        Gate::authorize('update', $subject);

        $subject->update($request->getAttributes());

        flash_success('update');

        return Redirect::route('administration.subjects.show', ['subject' => $subject]);
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        Gate::authorize('delete', $subject);

        $subject->delete();

        flash_success('delete');

        return Redirect::route('administration.subjects.index');
    }
}
