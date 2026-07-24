<?php

namespace App\Http\Controllers\School;

use App\Authorization\School\StudentByClassroomReport;
use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\School\StudentCollection;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\Student;
use App\Support\ModelAbilityMap;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentByClassroomReportController extends Controller
{
    public function index(Request $request)
    {
        $students = $this->query()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('school/reports/student-by-classroom', [
            'students' => ResourcePayloadBuilder::paginateWithAbilities(
                $students,
                StudentCollection::make($students),
                ['view'],
                $request,
            ),
            'gradeLevels' => GradeLevel::listForCurrentSchool(),
            'classroomNames' => classroom_names(),
            'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
            'nationalities' => Nationality::list(),
            'filter' => $request->input('filter', []),
            ...ModelAbilityMap::make(StudentByClassroomReport::class, ['print']),
        ]);
    }

    public function print(): View
    {
        Gate::authorize('print', StudentByClassroomReport::class);

        $students = $this->query()->get();

        return view('print.school.reports.student-by-classroom', [
            'students' => $students,
            'academicYearName' => AcademicYear::currentName(),
        ]);
    }

    /**
     * @return Builder<Student>|QueryBuilder<Student>
     */
    private function query(): Builder|QueryBuilder
    {
        return QueryBuilder::for(Student::class)
            ->select([
                'students.id',
                'students.uuid',
                'students.nationality_id',
                'students.registration_status',
                'students.first_name',
                'students.father_name',
                'students.grandfather_name',
                'students.surname',
                'students.gender',
                'students.national_id',
                'students.family_registration_number',
                'students.passport_number',
                'students.deleted_at',
            ])
            ->forCurrentSchool()
            ->withCurrentClassroom()
            ->with([
                'nationality:id,name,code',
                'enrollment.gradeLevel:id,name,educational_stage,order',
                'enrollment.classroom:id,name',
            ])
            ->allowedFilters(
                AllowedFilter::exact('grade_level_id', 'grade_levels.id'),
                AllowedFilter::exact('classroom_name', 'classrooms.name'),
                AllowedFilter::scope('name', 'byFullName'),
                AllowedFilter::exact('registration_status'),
                AllowedFilter::exact('nationality_id'),
                'national_id',
                'family_registration_number',
                'passport_number',
            )
            ->orderByGradeLevel()
            ->orderByFullName();
    }
}
