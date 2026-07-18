<?php

namespace App\Http\Resources\EducationMonitor;

use App\Http\Resources\DirectModelCollection;
use App\Models\EducationServicesOffice;
use Illuminate\Http\Request;

class EducationServicesOfficeCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (EducationServicesOffice $office): array => [
            'id' => $office->id,
            'uuid' => $office->uuid,
            'name' => $office->name,
            'schools_count' => (int) ($office->schools_count ?? 0),
            'students_count' => (int) ($office->students_count ?? 0),
        ])->all();
    }
}
