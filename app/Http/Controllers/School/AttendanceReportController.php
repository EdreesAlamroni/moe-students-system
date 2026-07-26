<?php

namespace App\Http\Controllers\School;

use App\Authorization\School\AttendanceReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\Report\AttendanceReportRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Support\ModelAbilityMap;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceReportController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('view', AttendanceReport::class);

        $classrooms = Classroom::list(function ($query) {
            $query->forCurrentSchoolAndAcademicYear();
        });

        return Inertia::render('school/reports/attendance', [
            'classrooms' => $classrooms,
            'months' => $this->getMonthOptions(),
            ...ModelAbilityMap::make(AttendanceReport::class, ['print']),
        ]);
    }

    public function print(AttendanceReportRequest $request): View|RedirectResponse
    {
        Gate::authorize('print', AttendanceReport::class);

        $validated = $request->getAttributes();

        $students = $this->getStudents($validated['classroom']);

        if ($students->isEmpty()) {
            flash_error('classroom-students-not-found');

            return Redirect::route('school.reports.attendance.index');
        }

        return view('print.school.reports.attendance', [
            'classroom' => $validated['classroom'],
            'students' => $students,
            'year' => date('Y'),
            'month' => $validated['month'],
            'days' => $this->daysOfMonth((int) date('Y'), $validated['month']),
            'monthLabel' => $this->getMonthLabel($validated['month']),
            'academicYearName' => AcademicYear::currentName(),
        ]);
    }

    private function getMonthOptions(): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $months[] = [
                'id' => (string) $month,
                'name' => sprintf(
                    '%02d - %s',
                    $month,
                    $this->getMonthLabel($month)
                ),
            ];
        }

        return $months;
    }

    private function getMonthLabel(int $month): string
    {
        return CarbonImmutable::create(intval(date('Y')), $month, 1)
            ->locale('ar')
            ->translatedFormat('F');
    }

    private function daysOfMonth(int $year, int $month): array
    {
        $daysInMonth = CarbonImmutable::create($year, $month, 1)->daysInMonth;

        return range(1, $daysInMonth);
    }

    private function getStudents(Classroom $classroom): Collection
    {
        return Student::query()
            ->select([
                'students.id',
                'students.uuid',
                'students.first_name',
                'students.father_name',
                'students.grandfather_name',
                'students.surname',
            ])
            ->forCurrentSchool()
            ->withCurrentClassroom()
            ->where('student_enrollments.classroom_id', '=', $classroom->id)
            ->orderByFullName()
            ->get();
    }
}
