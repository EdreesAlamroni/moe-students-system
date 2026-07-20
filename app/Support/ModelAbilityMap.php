<?php

namespace App\Support;

use App\Authorization\Contracts\AuthorizationResource;
use App\Support\Auth\DashboardAuth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class ModelAbilityMap
{
    private function __construct() {}

    /**
     * Evaluate each ability against the given subject.
     *
     * @param  Model|AuthorizationResource|class-string<Model>|class-string<AuthorizationResource>|array<int, mixed>  $subject
     * @param  list<string>  $abilities
     * @return array<string, bool>
     */
    public static function can(Model|AuthorizationResource|string|array $subject, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        $guard ??= DashboardAuth::resolve()?->guard;

        $user ??= auth($guard)->user();

        return collect($abilities)
            ->mapWithKeys(fn (string $ability): array => [
                $ability => (bool) ($user?->can($ability, $subject) ?? false),
            ])
            ->all();
    }

    /**
     * Determine whether any of the given abilities are authorized.
     *
     * @param  Model|AuthorizationResource|class-string<Model>|class-string<AuthorizationResource>|array<int, mixed>  $subject
     * @param  list<string>  $abilities
     */
    public static function canAny(Model|AuthorizationResource|string|array $subject, array $abilities, ?Authenticatable $user = null, ?string $guard = null): bool
    {
        $can = self::can($subject, $abilities, $user, $guard);

        return collect($can)->contains(true);
    }

    /**
     * Build an authorization payload for Inertia responses.
     *
     * @param  Model|AuthorizationResource|class-string<Model>|class-string<AuthorizationResource>|array<int, mixed>  $subject
     * @param  list<string>  $abilities
     * @return array{canAny: bool, can: array<string, bool>}
     */
    public static function make(Model|AuthorizationResource|string|array $subject, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        $can = self::can($subject, $abilities, $user, $guard);

        return [
            'canAny' => collect($can)->contains(true),
            'can' => $can,
        ];
    }
}
