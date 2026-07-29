<?php

namespace App\Http\Controllers\EducationMonitor;

use App\Http\Controllers\Controller;
use App\Http\Resources\EducationMonitor\StudentPsychosocialCardResource;
use App\Http\Resources\EducationMonitor\StudentResource;
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
            'psychosocialCard' => function (HasOne $relation) {
                $relation->with(['guardianNationality', 'motherNationality']);
            },
        ]);

        return Inertia::render('education-monitor/students/psychosocial-cards/show', [
            'student' => ResourcePayloadBuilder::make(
                StudentResource::make($student),
            ),
            'psychosocialCard' => ResourcePayloadBuilder::make(
                StudentPsychosocialCardResource::make($student->psychosocialCard),
            ),
        ]);
    }
}
