<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\Support\Auth\DashboardAuth;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function execute(DashboardAuth $dashboard, string $username, string $password, bool $remember, string $ip): void
    {
        $this->ensureIsNotRateLimited($username, $ip);

        $user = User::query()
            ->where('scope', '=', $dashboard->scope)
            ->where('username', '=', $username)
            ->first();

        if ($user === null) {
            $this->recordFailedAttempt($username, $ip);

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        if ($user->isNotActivated()) {
            $this->recordFailedAttempt($username, $ip);

            throw ValidationException::withMessages([
                'username' => __('auth.deactivated'),
            ]);
        }

        if ($user->isNotApproved()) {
            $this->recordFailedAttempt($username, $ip);

            throw ValidationException::withMessages([
                'username' => __('auth.not_approved'),
            ]);
        }

        if (! Auth::guard($dashboard->guard)->attempt([
            'username' => $username,
            'password' => $password,
        ], $remember)) {
            $this->recordFailedAttempt($username, $ip);

            throw ValidationException::withMessages([
                'username' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($username, $ip));
    }

    public function ensureIsNotRateLimited(string $username, string $ip): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($username, $ip), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey($username, $ip));

        throw ValidationException::withMessages([
            'username' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(string $username, string $ip): string
    {
        return Str::transliterate(Str::lower(sprintf('%s|%s', $username, $ip)));
    }

    private function recordFailedAttempt(string $username, string $ip): void
    {
        RateLimiter::hit($this->throttleKey($username, $ip));
    }
}
