<?php

namespace App\Http\Resources\Administration;

use App\Http\Resources\DirectModelCollection;
use App\Models\Municipal;
use Illuminate\Http\Request;

class MunicipalCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Municipal $municipal): array => [
            'id' => $municipal->id,
            'uuid' => $municipal->uuid,
            'name' => $municipal->name,
            'schools_count' => $municipal->schools_count,
        ])->all();
    }
}
