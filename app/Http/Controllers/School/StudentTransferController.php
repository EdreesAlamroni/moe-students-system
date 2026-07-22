<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Http\Requests\School\Student\StoreTransferRequest;
use App\Http\Resources\School\TransferableStudentCollection;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentTransfer;
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
            /** @var School $school */
            $school = auth('school')->user()->organization;

            $students = QueryBuilder::for(Student::class)
                ->eligibleForSchoolTransfer($school)
                ->awaitingSchoolTransfer()
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

        return Inertia::render('school/students/transfers/create', [
            'students' => ResourcePayloadBuilder::make(
                TransferableStudentCollection::make($students),
            ),
            'filter' => $request->input('filter', []),
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        Gate::authorize('addTransferredStudent', Student::class);

        // TODO: Handle the case where a student does not have an enrollment but needs to be transferred to a school.

        DB::transaction(function () use ($request): void {
            /** @var School $school */
            $school = auth('school')->user()->organization;

            /** @var array<int, int> $studentIds */
            $studentIds = $request->validated('student_ids', []);

            $currentAcademicYearId = AcademicYear::currentId();

            Student::query()
                ->whereIn('id', $studentIds)
                ->update([
                    'education_monitor_id' => $school->education_monitor_id,
                    'school_id' => $school->id,
                ]);

            StudentEnrollment::query()
                ->whereIn('student_id', $studentIds)
                ->where('academic_year_id', '=', $currentAcademicYearId)
                ->update([
                    'school_id' => $school->id,
                ]);

            StudentTransfer::query()
                ->whereIn('student_id', $studentIds)
                ->whereNotNull([
                    'left_academic_year_id',
                    'from_school_id',
                    'left_school_at',
                ])
                ->whereNull([
                    'joined_academic_year_id',
                    'to_school_id',
                    'joined_school_at',
                ])
                ->update([
                    'joined_academic_year_id' => $currentAcademicYearId,
                    'to_school_id' => $school->id,
                    'joined_school_at' => now(),
                ]);
        });

        flash_success('add-transferred-student');

        return Redirect::route('school.students.index');
    }

    public function destroy(Student $student): RedirectResponse
    {
        Gate::authorize('transferStudentOut', $student);

        DB::transaction(function () use ($student) {
            $student->update([
                'school_id' => null,
            ]);

            if ($student->enrollment()->exists()) {
                $student->enrollment()->update([
                    'school_id' => null,
                    'classroom_id' => null,
                ]);
            }

            $student->transfers()->create([
                'left_academic_year_id' => AcademicYear::currentId(),
                'from_school_id' => auth('school')->user()->organization_id,
                'left_school_at' => now(),
            ]);
        });

        flash_success('student-transfer-out-of-school');

        return Redirect::route('school.students.index');
    }
}
