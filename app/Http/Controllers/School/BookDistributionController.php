<?php

namespace App\Http\Controllers\School;

use App\Actions\School\DistributeBooksToStudents;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\BookDistribution\IndexRequest;
use App\Http\Requests\School\BookDistribution\StoreRequest;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class BookDistributionController extends Controller
{
    public function index(IndexRequest $request): Response
    {
        Gate::authorize('view', BookDistribution::class);

        $selectedAttributes = $request->getAttributes();
        $gradeLevelId = $selectedAttributes['grade_level_id'];
        $classroomId = $selectedAttributes['classroom_id'];

        $gradeLevels = GradeLevel::listForCurrentSchool();

        $classrooms = filled($gradeLevelId)
            ? Classroom::listForCurrentSchool($gradeLevelId)
            : collect([]);

        $warehouseConfirmed = filled($gradeLevelId) && BookDistribution::query()
            ->forCurrentSchoolGradeLevel($gradeLevelId)
            ->exists();

        $students = $warehouseConfirmed && filled($gradeLevelId)
            ? $this->students($gradeLevelId, $classroomId)
            : collect([]);

        return Inertia::render('school/book-distributions/index', [
            'gradeLevels' => $gradeLevels,
            'classrooms' => $classrooms,
            'students' => $students,
            'warehouseConfirmed' => $warehouseConfirmed,
            'selected' => $selectedAttributes,
            'filter' => $request->input('filter', []),
            'can' => [
                'distribute' => Gate::allows('distribute', BookDistribution::class),
            ],
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        Gate::authorize('distribute', BookDistribution::class);

        $attributes = $request->getAttributes();

        $count = app(DistributeBooksToStudents::class)->execute(
            schoolId: auth('school')->user()->organization_id,
            gradeLevelId: $attributes['grade_level_id'],
            studentIds: $attributes['student_ids'],
            classroomId: $attributes['classroom_id'],
        );

        if ($count === 0) {
            flash()->warning(__('alerts.messages.book-distribution-no-eligible-students'));
        } else {
            flash()->success(__('alerts.messages.book-distribution-completed', ['count' => $count]));
        }

        return Redirect::route('school.book-distributions.index', array_filter([
            'grade_level_id' => $attributes['grade_level_id'],
            'classroom_id' => $attributes['classroom_id'],
        ]));
    }

    private function students(int $gradeLevelId, ?int $classroomId): Collection
    {
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return collect([]);
        }

        return QueryBuilder::for(Student::class)
            ->select([
                'students.id',
                'students.uuid',
                'students.number',
                'students.first_name',
                'students.father_name',
                'students.grandfather_name',
                'students.surname',
                'students.gender',
            ])
            ->forCurrentSchool()
            ->whereHas('enrollments', function (Builder $query) use ($academicYearId, $gradeLevelId, $classroomId): void {
                $query
                    ->where('academic_year_id', '=', $academicYearId)
                    ->where('grade_level_id', '=', $gradeLevelId)
                    ->when(filled($classroomId), function (Builder $query) use ($classroomId): void {
                        $query->where('classroom_id', '=', $classroomId);
                    });
            })
            ->with([
                'enrollment.classroom:id,name',
            ])
            ->withExists(['bookDistributionItem as already_distributed'])
            ->allowedFilters(
                AllowedFilter::scope('name', 'byFullName'),
                AllowedFilter::callback('distribution_status', function (Builder $query, string $value): void {
                    if ($value === 'distributed') {
                        $query->whereHas('bookDistributionItem');
                    } elseif ($value === 'pending') {
                        $query->whereDoesntHave('bookDistributionItem');
                    }
                }),
            )
            ->orderByFullName()
            ->get()
            ->map(function (Student $student): array {
                return [
                    'id' => $student->id,
                    'uuid' => $student->uuid,
                    'number' => $student->number,
                    'full_name' => $student->full_name,
                    'gender' => $student->gender->toArray(),
                    'classroomName' => $student->enrollment?->classroom?->name,
                    'already_distributed' => $student->already_distributed,
                ];
            })
            ->sortBy('already_distributed')
            ->values();
    }
}
