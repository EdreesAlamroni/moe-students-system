<?php

namespace App\Support\Http;

use App\Support\Authorization\ModelAbilityMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

final class ResourcePayloadBuilder
{
    private function __construct() {}

    /**
     * Build a resource's array payload, merged with extra attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function make(JsonResource $resource, array $attributes = [], ?Request $request = null): array
    {
        $request ??= request();

        return [
            ...$resource->resolve($request),
            ...$attributes,
        ];
    }

    /**
     * Build a resource payload with a `can` map of authorization abilities.
     *
     * @param  list<string>  $abilities
     * @return array<string, mixed>
     */
    public static function withAbilities(JsonResource $resource, array $abilities, array $attributes = [], ?Request $request = null): array
    {
        $model = $resource->resource;

        if (! $model instanceof Model) {
            throw new InvalidArgumentException('Resource must wrap an Eloquent model.');
        }

        return self::make($resource, [
            ...$attributes,
            'can' => ModelAbilityMap::make($model, $abilities),
        ], $request);
    }
}
