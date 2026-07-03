<?php

namespace App\Support\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

class RegistersDashboardAuthRoutes
{
    public static function registerGuestRoutes(DashboardAuth $dashboard): void
    {
        Route::middleware(DashboardAuth::guestMiddleware())->group(function () use ($dashboard): void {
            Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('login', [AuthenticatedSessionController::class, 'store']);

            if ($dashboard->supportsPasswordReset) {
                Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
                Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
                Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
                Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
            }
        });
    }

    public static function registerAuthenticatedRoutes(DashboardAuth $dashboard): void
    {
        Route::withoutMiddleware(['password.confirm:'.$dashboard->routeName('password.confirm')])->group(function (): void {
            Route::get('change-password', [ChangePasswordController::class, 'create'])->name('password.change');
            Route::post('change-password', [ChangePasswordController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('password.change.store');

            Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
            Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('password.confirm.store');

            Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        });
    }
}
