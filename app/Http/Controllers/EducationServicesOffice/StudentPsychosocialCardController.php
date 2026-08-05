<?php

namespace App\Http\Controllers\EducationServicesOffice;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationServicesOffice\StudentPsychosocialCardResource;
use App\Http\Resources\EducationServicesOffice\StudentResource;
use App\Models\Student;
use App\Support\ResourcePayloadBuilder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StudentPsychosocialCardController extends Controller
{
    public function show(Student $student): Response|RedirectResponse
    {
        Gate::authorize('viewPsychosocialCard', $student);

        $student->load([
            'nationality',
            'schoolPeriod',
            'psychosocialCard' => function (HasOne $relation) {
                $relation->with(['guardianNationality', 'motherNationality']);
            },
        ]);

        return Inertia::render('education-services-office/students/psychosocial-cards/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'psychosocialCard' => ResourcePayloadBuilder::make(
                StudentPsychosocialCardResource::make($student->psychosocialCard),
            ),
        ]);
    }
}
