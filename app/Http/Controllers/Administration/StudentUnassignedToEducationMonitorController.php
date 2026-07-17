<?php

namespace App\Http\Controllers\Administration;

use App\Enums\StudentRegistrationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Administration\StudentCollection;
use App\Models\Nationality;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StudentUnassignedToEducationMonitorController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Student::class);

        $students = QueryBuilder::for(Student::class)
            ->select([
                'students.id',
                'students.uuid',
                'students.education_monitor_id',
                'students.school_id',
                'students.nationality_id',
                'students.number',
                'students.registration_status',
                'students.first_name',
                'students.father_name',
                'students.grandfather_name',
                'students.surname',
                'students.gender',
                'students.national_id',
                'students.family_registration_number',
                'students.passport_number',
                'students.created_at',
                'students.deleted_at',
            ])
            ->unassignedToEducationMonitor()
            ->unassignedToSchool()
            ->with(['nationality'])
            ->allowedFilters(
                AllowedFilter::scope('name', 'byFullName'),
                AllowedFilter::exact('registration_status'),
                AllowedFilter::exact('nationality_id'),
                'national_id',
                'family_registration_number',
                'passport_number',
            )
            ->orderByFullName()
            ->paginate()
            ->withQueryString()
            ->appends($request->query())
            ->onEachSide(0);

        return Inertia::render('administration/students/unassigned-to-education-monitor/index', [
            'students' => ResourcePayloadBuilder::paginateWithAbilities(
                $students,
                StudentCollection::make($students),
                ['view'],
                $request,
            ),
            'nationalities' => Nationality::list(),
            'registrationStatuses' => StudentRegistrationStatus::optionsArray(),
            'filter' => $request->input('filter', []),
        ]);
    }
}
