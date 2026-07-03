<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcademicYearSelectionRequest;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class AcademicYearSelectionController extends Controller
{
    public function __invoke(AcademicYearSelectionRequest $request): RedirectResponse
    {
        $academicYearId = $request->validated('academic_year_id');

        $sessionKey = AcademicYear::selectedSessionKey();

        Session::put($sessionKey, $academicYearId);

        AcademicYear::clearCachedCurrent();

        flash()->success(__('تم تغيير العام الدراسي بنجاح.'));

        $previousUrl = URL::previous();

        $redirectPath = $previousUrl
            ? (parse_url($previousUrl, PHP_URL_PATH) ?: '/')
            : $request->path();

        return Redirect::to($redirectPath);
    }
}
