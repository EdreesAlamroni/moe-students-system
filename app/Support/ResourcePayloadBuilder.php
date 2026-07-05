<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
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
     * Build a resource payload with authorization abilities.
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
            ...ModelAbilityMap::make($model, $abilities),
        ], $request);
    }

    /**
     * Resolve a resource collection into a paginator-shaped payload for Inertia.
     *
     * @return array<string, mixed>
     */
    public static function paginate(Paginator&Arrayable $paginator, ResourceCollection $collection, ?Request $request = null): array
    {
        $request ??= request();

        return [
            'data' => $collection->resolve($request),
            ...Arr::except($paginator->toArray(), ['data']),
        ];
    }

    /**
     * Resolve a resource collection into a paginator-shaped payload with row authorization abilities.
     *
     * @param  list<string>  $abilities
     * @return array<string, mixed>
     */
    public static function paginateWithAbilities(Paginator&Arrayable $paginator, ResourceCollection $collection, array $abilities, ?Request $request = null): array
    {
        $request ??= request();

        $payload = self::paginate($paginator, $collection, $request);

        $payload['data'] = collect($payload['data'])
            ->values()
            ->map(function (array $row, int $index) use ($abilities, $paginator): array {
                $model = $paginator->items()[$index];

                if ($model instanceof JsonResource) {
                    $model = $model->resource;
                }

                return [
                    ...$row,
                    ...ModelAbilityMap::make($model, $abilities),
                ];
            })
            ->all();

        return $payload;
    }
}
