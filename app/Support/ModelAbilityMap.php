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
     * Resolve the user's authorization for each ability against the subject.
     *
     * A class-string subject performs a class-level check through the bound
     * policy, mirroring `$user->can($ability, SomeClass::class)`.
     *
     * @param  Model|AuthorizationResource|class-string<Model>|class-string<AuthorizationResource>  $subject
     * @param  list<string>  $abilities
     * @return array<string, bool> Map of ability to authorization result.
     */
    public static function can(Model|AuthorizationResource|string $subject, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        $guard ??= DashboardAuth::resolve()?->guard;

        $user ??= auth($guard)->user();

        return collect($abilities)
            ->mapWithKeys(fn (string $ability): array => [
                $ability => boolval($user?->can($ability, $subject) ?? false),
            ])
            ->all();
    }

    /**
     * Determine whether the user is authorized for any of the given abilities.
     *
     * @param  Model|AuthorizationResource|class-string<Model>|class-string<AuthorizationResource>  $subject
     * @param  list<string>  $abilities
     */
    public static function canAny(Model|AuthorizationResource|string $subject, array $abilities, ?Authenticatable $user = null, ?string $guard = null): bool
    {
        $can = self::can($subject, $abilities, $user, $guard);

        return collect($can)->contains(true);
    }

    /**
     * Return both the ability map and whether any ability is granted.
     *
     * @param  Model|AuthorizationResource|class-string<Model>|class-string<AuthorizationResource>  $subject
     * @param  list<string>  $abilities
     * @return array{canAny: bool, can: array<string, bool>}
     */
    public static function make(Model|AuthorizationResource|string $subject, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        $can = self::can($subject, $abilities, $user, $guard);

        return [
            'canAny' => collect($can)->contains(true),
            'can' => $can,
        ];
    }
}
