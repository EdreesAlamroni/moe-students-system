<?php

namespace App\Http\Resources\Administration;

use App\Models\Municipal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MunicipalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Municipal $municipal */
        $municipal = $this->resource;

        return [
            'id' => $municipal->id,
            'uuid' => $municipal->uuid,
            'name' => $municipal->name,
            'schools_count' => $municipal->schools_count,
        ];
    }
}
