<?php

use App\Enums\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

if (! function_exists('flash_success')) {
    /**
     * Flash a translated success message with replacements.
     */
    function flash_success(string $key, array $replacements = []): void
    {
        $key = sprintf('alerts.messages.%s', $key);

        $message = Lang::get($key, $replacements);

        flash()->success($message);
    }
}

if (! function_exists('flash_error')) {
    /**
     * Flash a translated error message with replacements.
     */
    function flash_error(string $key, array $replacements = []): void
    {
        $key = sprintf('alerts.messages.%s', $key);

        $message = Lang::get($key, $replacements);

        flash()->error($message);
    }
}

if (! function_exists('flash_warning')) {
    /**
     * Flash a translated warning message with replacements.
     */
    function flash_warning(string $key, array $replacements = []): void
    {
        $key = sprintf('alerts.messages.%s', $key);

        $message = Lang::get($key, $replacements);

        flash()->warning($message);
    }
}

if (! function_exists('classroom_names')) {
    /**
     * Get the classroom names.
     */
    function classroom_names(): array
    {
        return collect(
            array_map('strval', range(1, 12))
        )->map(function (string $name): array {
            return [
                'key' => $name,
                'id' => $name,
                'name' => $name,
            ];
        })->all();
    }
}

if (! function_exists('grouped_roles')) {
    /**
     * Get the grouped roles.
     */
    function get_grouped_roles(UserScope|string $scope, Collection|array $ids = []): Collection
    {
        $ids = $ids instanceof Collection ? $ids : collect($ids);

        $scope = $scope instanceof UserScope ? $scope->value : $scope;

        return Role::query()
            ->select(['id', 'name', 'guard_name'])
            ->where('guard_name', '=', $scope)
            ->when($ids->isNotEmpty(), function (Builder $query) use ($ids) {
                $query->whereIn('id', $ids->all());
            })
            ->oldest()
            ->get()
            ->groupBy(function (Role $role): string {
                return Str::before($role->name, ':');
            })->mapWithKeys(function (Collection $roles, string $group): array {
                return [
                    $group => [
                        'label' => __("roles.{$group}.label"),
                        'roles' => $roles->map(function (Role $role) use ($group): array {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                                'label' => __("roles.{$group}.values.{$role->name}"),
                            ];
                        })->values(),
                    ],
                ];
            })->values();
    }
}
