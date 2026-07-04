<?php

namespace App\Http\Resources\Administration;

use App\Models\Municipal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MunicipalCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(function (JsonResource $resource): array {
            /** @var Municipal $municipal */
            $municipal = $resource->resource;

            return [
                'id' => $municipal->id,
                'name' => $municipal->name,
            ];
        })->all();
    }
}
