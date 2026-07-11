<?php

namespace App\Http\Resources\Administration;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
                return $monitors->only(['id', 'uuid', 'name'])->all();
            }),
            'monitors_count' => $this->whenCounted('monitors', $warehouse->monitors_count, 0),
            'schools_count' => $this->whenCounted('schools', $warehouse->schools_count, 0),
        ];
    }
}
