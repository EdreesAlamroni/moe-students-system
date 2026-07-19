<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Http\Controllers\Controller;
use App\Http\Requests\EducationMonitor\Student\StoreTransferRequest;
use App\Http\Resources\EducationMonitor\StudentTransferCollection;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentTransferController extends Controller
{
    public function create(Request $request): Response
    {
        Gate::authorize('addTransferredStudent', Student::class);

        $students = [];

        if ($request->anyFilled([
            'filter.name',
            'filter.passport_number',
            'filter.national_id',
            'filter.family_registration_number',
        ])) {
            $students = QueryBuilder::for(Student::class)
                ->whereNull('students.education_monitor_id')
                ->whereNull('students.school_id')
                // ->awaitingSchoolTransfer()
                ->with(['nationality', 'enrollment.gradeLevel'])
                ->allowedFilters(
                    AllowedFilter::scope('name', 'byFullName'),
                    AllowedFilter::exact('passport_number'),
                    AllowedFilter::exact('national_id'),
                    AllowedFilter::exact('family_registration_number'),
                )
                ->orderByFullName()
                ->get();
        }

        return Inertia::render('education-monitor/students/transfers/create', [
            'students' => ResourcePayloadBuilder::make(
                StudentTransferCollection::make($students),
            ),
            'filter' => $request->input('filter', []),
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        Gate::authorize('addTransferredStudent', Student::class);

        DB::transaction(function () use ($request): void {
            Student::query()
                ->whereIn('id', $request->validated('student_ids'))
                ->whereNull('education_monitor_id')
                ->whereNull('school_id')
                ->update([
                    'education_monitor_id' => auth('education_monitor')->user()->organization_id,
                ]);
        });

        flash_success('add-transferred-student');

        return Redirect::route('education-monitor.students.index');
    }

    public function destroy(Student $student): RedirectResponse
    {
        Gate::authorize('transferStudentOut', $student);

        DB::transaction(function () use ($student) {
            $student->update([
                'education_monitor_id' => null,
            ]);
        });

        $student->refresh();

        flash_success('student-transfer-out-of-monitor');

        return Redirect::route('education-monitor.students.index');
    }
}
