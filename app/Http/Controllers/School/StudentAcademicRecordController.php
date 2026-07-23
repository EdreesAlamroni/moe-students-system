<?php

namespace App\Http\Controllers\School;

use App\Enums\AcademicRecordRating;
use App\Enums\AcademicRecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\StoreAcademicRecordRequest;
use App\Http\Resources\School\StudentResource;
use App\Models\AcademicRecord;
use App\Models\Student;
use App\Services\School\AcademicRecordService;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class StudentAcademicRecordController extends Controller
{
    public function __construct(private AcademicRecordService $academicRecordService) {}

    public function show(Student $student): Response
    {
        Gate::authorize('viewAcademicRecord', $student);

        $student->load([
            'nationality',
            'enrollment.gradeLevel',
        ]);

        $pageData = $this->academicRecordService->resolveShowPageData($student);

        return Inertia::render('school/students/academic-record/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'groupedRecords' => $pageData['groupedRecords'],
            'requiresAcademicRecord' => $pageData['requiresAcademicRecord'],
            'isComplete' => $pageData['isComplete'],
            ...ModelAbilityMap::make($student, ['createAcademicRecord']),
        ]);
    }

    public function create(Student $student): Response
    {
        Gate::authorize('createAcademicRecord', $student);

        $student->load([
            'nationality',
            'enrollment.gradeLevel',
        ]);

        $pageData = $this->academicRecordService->resolveCreatePageData($student);

        return Inertia::render('school/students/academic-record/create', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'groupedRecords' => $pageData['groupedRecords'],
            'currentGradeLevel' => $pageData['currentGradeLevel'],
            'selectableAcademicYears' => $pageData['selectableAcademicYears'],
            'academicRecordStatuses' => AcademicRecordStatus::selectable(),
            'academicRecordRatings' => AcademicRecordRating::optionsArray(),
            'progress' => $pageData['progress'],
        ]);
    }

    public function store(StoreAcademicRecordRequest $request, Student $student): RedirectResponse
    {
        Gate::authorize('createAcademicRecord', $student);

        $attributes = $request->getAttributes();

        DB::transaction(function () use ($student, $attributes): void {
            AcademicRecord::query()->create([
                'student_id' => $student->id,
                ...$attributes,
            ]);

            if (! $attributes['status']->isPassing()) {
                return;
            }

            $student->refresh()->load(['enrollment.gradeLevel']);

            if (! $this->academicRecordService->isComplete($student)) {
                return;
            }

            $student->update([
                'registration_status' => $this->academicRecordService->calculateRegistrationStatus($student),
            ]);
        });

        $student->refresh()->load(['enrollment.gradeLevel']);

        if ($this->academicRecordService->isComplete($student)) {
            flash_success('academic-record-created');

            return Redirect::route('school.students.academic-record.show', ['student' => $student]);
        }

        flash_success('academic-record-updated');

        return Redirect::route('school.students.academic-record.create', ['student' => $student]);
    }
}
