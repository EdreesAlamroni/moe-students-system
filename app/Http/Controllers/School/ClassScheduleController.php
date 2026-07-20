<?php

namespace App\Http\Controllers\School;

use App\Actions\School\SyncClassSchedule;
use App\Enums\DayOfWeek;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\ClassSchedule\BulkUpdateRequest;
use App\Http\Resources\School\ClassScheduleResource;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Support\ModelAbilityMap;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ClassScheduleController extends Controller
{
    public function show(Request $request, Classroom $classroom): Response
    {
        Gate::authorize('view', [ClassSchedule::class, $classroom]);

        $classroom->load(['school', 'gradeLevel']);

        $classroomName = sprintf(
            '%s / %s',
            $classroom->gradeLevel->name,
            $classroom->name,
        );

        $scheduleGrid = $this->getScheduleGrid($request, $classroom);
        $subjects = $this->getSubjects($classroom);

        return Inertia::render('school/class-schedules/show', [
            'classroomName' => $classroomName,
            'schedule' => $scheduleGrid,
            'subjects' => $subjects,
            'days' => DayOfWeek::buildDays(),
            ...ModelAbilityMap::make([ClassSchedule::class, $classroom], ['update', 'print']),
        ]);
    }

    public function edit(Request $request, Classroom $classroom): Response
    {
        Gate::authorize('update', [ClassSchedule::class, $classroom]);

        $classroom->load(['gradeLevel']);

        $scheduleGrid = $this->getScheduleGrid($request, $classroom);

        return Inertia::render('school/class-schedules/edit', [
            'schedule' => $scheduleGrid,
            'subjects' => Inertia::defer(fn () => $this->getSubjects($classroom)),
            'days' => DayOfWeek::buildDays(),
        ]);
    }

    public function update(BulkUpdateRequest $request, Classroom $classroom): RedirectResponse
    {
        Gate::authorize('update', [ClassSchedule::class, $classroom]);

        $items = $request->getScheduleItems();

        app(SyncClassSchedule::class)->execute($classroom, $items);

        flash_success('class-schedule-updated');

        return Redirect::route('school.classrooms.class-schedules.show', ['classroom' => $classroom]);
    }

    public function print(Request $request, Classroom $classroom): View
    {
        Gate::authorize('print', [ClassSchedule::class, $classroom]);

        $classroom->load(['gradeLevel', 'school']);

        $scheduleGrid = $this->getScheduleGrid($request, $classroom);

        return view('print.school.class-schedule', [
            'classroom' => $classroom,
            'schedule' => $scheduleGrid,
            'days' => DayOfWeek::schoolDays(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getScheduleGrid(Request $request, Classroom $classroom): array
    {
        $periods = ClassPeriod::query()
            ->select([
                'id',
                'uuid',
                'academic_period',
                'name',
                'start_time',
                'end_time',
                'order',
                'is_break',
            ])
            ->forCurrentAcademicYear()
            ->forAcademicPeriod($classroom->school->academic_period)
            ->ordered()
            ->get();

        $schedules = ClassSchedule::query()
            ->forClassroom($classroom)
            ->with(['classPeriod', 'subject'])
            ->get();

        $resource = new ClassScheduleResource($classroom);

        return $resource->withContext($periods, $schedules)->toArray($request);
    }

    /**
     * @return Collection<int, Subject>
     */
    private function getSubjects(Classroom $classroom): Collection
    {
        return Subject::query()
            ->select(['id', 'name', 'code'])
            ->where('grade_level_id', '=', $classroom->grade_level_id)
            ->orderBy('name')
            ->get();
    }
}
