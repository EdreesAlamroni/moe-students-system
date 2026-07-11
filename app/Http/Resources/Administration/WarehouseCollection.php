<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Warehouse $warehouse): array => [
            'id' => $warehouse->id,
            'uuid' => $warehouse->uuid,
            'name' => $warehouse->name,
            'monitors_count' => $warehouse->monitors_count,
            'schools_count' => $warehouse->schools_count,
        ])->all();
    }
}
