<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidSchoolPeriodContext
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user('school');

        if ($user !== null) {
            $user->ensureActiveSchoolPeriodIsValid();
        }

        return $next($request);
    }
}
