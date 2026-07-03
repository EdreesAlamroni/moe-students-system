<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Support\AcademicYearReadOnlyExemptions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceAcademicYearReadOnly
{
    /**
     * Prevent mutating requests when the selected academic year is not active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $method = strtoupper($request->getMethod());

        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        if (AcademicYearReadOnlyExemptions::matches($request)) {
            return $next($request);
        }

        $currentAcademicYear = AcademicYear::current();

        if ($currentAcademicYear && ! $currentAcademicYear->isActive()) {
            abort(403, __('Modifications are not allowed for previous academic years.'));
        }

        return $next($request);
    }
}
