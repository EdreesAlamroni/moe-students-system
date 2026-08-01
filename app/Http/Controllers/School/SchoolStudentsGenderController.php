<?php

namespace App\Http\Controllers\School;

use App\Enums\SchoolStudentsGender;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\UpdateStudentsGenderRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class SchoolStudentsGenderController extends Controller
{
    public function update(UpdateStudentsGenderRequest $request): RedirectResponse
    {
        /** @var School $school */
        $school = auth('school')->user()->organization;

        abort_if($school->students_gender !== null, 403);

        $school->update([
            'students_gender' => $request->enum('students_gender', SchoolStudentsGender::class),
        ]);

        flash_success('school-students-gender-updated');

        return Redirect::back();
    }
}
