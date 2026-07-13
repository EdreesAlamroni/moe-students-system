<?php

namespace App\Http\Resources\Administration;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class WarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Warehouse $warehouse */
        $warehouse = $this->resource;

        return [
            'id' => $warehouse->id,
            'uuid' => $warehouse->uuid,
            'name' => $warehouse->name,
            'address' => $warehouse->address,
            'latitude' => $warehouse->latitude,
            'longitude' => $warehouse->longitude,
            'has_coordinates' => $warehouse->hasCoordinates(),
            'monitors' => $this->whenLoaded('monitors', function (Collection $monitors) {
                return $monitors->map->only(['id', 'uuid', 'name'])->all();
            }),
            'monitors_count' => $this->whenCounted('monitors', $warehouse->monitors_count, 0),
            'schools_count' => $this->whenCounted('schools', $warehouse->schools_count, 0),
        ];
    }
}
