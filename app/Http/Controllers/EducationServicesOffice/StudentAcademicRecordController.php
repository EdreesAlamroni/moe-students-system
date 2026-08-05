<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationServicesOffice\StudentResource;
use App\Models\Student;
use App\Services\AcademicRecordService;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StudentAcademicRecordController extends Controller
{
    public function show(Student $student): Response
    {
        Gate::authorize('viewAcademicRecord', $student);

        $student->load([
            'nationality',
            'enrollment.gradeLevel',
            'schoolPeriod',
        ]);

        $pageData = app(AcademicRecordService::class)->resolveShowPageData($student);

        return Inertia::render('education-services-office/students/academic-record/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'groupedRecords' => $pageData['groupedRecords'],
            'requiresAcademicRecord' => $pageData['requiresAcademicRecord'],
            'isComplete' => $pageData['isComplete'],
        ]);
    }
}
