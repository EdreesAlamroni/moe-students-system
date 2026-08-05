<?php

namespace App\Http\Controllers\School;

use App\Enums\SchoolStudentsGender;
use App\Http\Controllers\Controller;
use App\Http\Requests\School\UpdateStudentsGenderRequest;
use App\Models\SchoolPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class SchoolStudentsGenderController extends Controller
{
    public function update(UpdateStudentsGenderRequest $request): RedirectResponse
    {
        /** @var SchoolPeriod $schoolPeriod */
        $schoolPeriod = auth('school')->user()->organization;

        abort_unless(is_null($schoolPeriod->students_gender), 403);

        $schoolPeriod->update([
            'students_gender' => $request->enum('students_gender', SchoolStudentsGender::class),
        ]);

        flash_success('school-students-gender-updated');

        return Redirect::back();
    }
}
