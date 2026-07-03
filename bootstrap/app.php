<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\BindDashboardAuth;
use App\Http\Middleware\EnforceAcademicYearReadOnly;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\EncryptHistoryMiddleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EncryptHistoryMiddleware::class,
            EnforceAcademicYearReadOnly::class,
        ])->alias([
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            'bind.dashboard' => BindDashboardAuth::class,
            'ensure.password.changed' => EnsurePasswordIsChanged::class,
            'academic-year.readonly' => EnforceAcademicYearReadOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419) {
                flash()->warning(__('انتهت صلاحية الصفحة، يرجى المحاولة مرة أخرى.'));

                return back();
            }

            return $response;
        })->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
