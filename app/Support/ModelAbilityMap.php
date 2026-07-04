<?php

namespace App\Support;

use App\Support\Auth\DashboardAuth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class ModelAbilityMap
{
    private function __construct() {}

    /**
     * Map each ability to whether the user is authorized.
     *
     * @param  Model|class-string<Model>  $model
     * @param  list<string>  $abilities
     * @return array<string, bool>
     */
    public static function can(Model|string $model, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        $guard ??= DashboardAuth::resolve()?->guard;

        $user ??= auth($guard)->user();

        return collect($abilities)
            ->mapWithKeys(fn (string $ability): array => [
                $ability => boolval($user?->can($ability, $model) ?? false),
            ])
            ->all();
    }

    /**
     * Determine whether the user is authorized for any of the given abilities.
     *
     * @param  Model|class-string<Model>  $model
     * @param  list<string>  $abilities
     */
    public static function canAny(Model|string $model, array $abilities, ?Authenticatable $user = null, ?string $guard = null): bool
    {
        return collect(self::can($model, $abilities, $user, $guard))->contains(true);
    }

    /**
     * Return both the ability map and whether any ability is granted.
     *
     * @param  Model|class-string<Model>  $model
     * @param  list<string>  $abilities
     * @return array{canAny: bool, can: array<string, bool>}
     */
    public static function make(Model|string $model, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        $can = self::can($model, $abilities, $user, $guard);

        return [
            'canAny' => collect($can)->contains(true),
            'can' => $can,
        ];
    }
}
