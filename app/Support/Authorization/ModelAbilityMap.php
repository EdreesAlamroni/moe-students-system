<?php

namespace App\Support\Authorization;

use App\Support\Auth\DashboardAuth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class ModelAbilityMap
{
    private function __construct() {}

    /**
     * Map each ability to whether the user is authorized for the model.
     *
     * @param  list<string>  $abilities
     * @return array<string, bool>
     */
    public static function make(Model $model, array $abilities, ?Authenticatable $user = null, ?string $guard = null): array
    {
        if ($guard === null) {
            $dashboardAuth = DashboardAuth::resolve();
            $guard = $dashboardAuth !== null
                ? $dashboardAuth->guard
                : null;
        }

        $user ??= auth($guard)->user();

        $result = [];

        foreach ($abilities as $ability) {
            $result[$ability] = boolval(($user?->can($ability, $model) ?? false));
        }

        return $result;
    }
}
