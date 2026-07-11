<?php

namespace App\Http\Resources\Administration;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseFormResource extends JsonResource
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
            'add_location_to_map' => $warehouse->hasCoordinates(),
            'education_monitor_ids' => $warehouse->monitors->pluck('id')->all(),
        ];
    }
}
