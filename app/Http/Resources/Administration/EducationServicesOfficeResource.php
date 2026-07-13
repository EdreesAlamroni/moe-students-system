<?php

namespace App\Http\Resources\Administration;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class EducationServicesOfficeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var EducationServicesOffice $office */
        $office = $this->resource;

        return [
            'id' => $office->id,
            'uuid' => $office->uuid,
            'education_monitor_id' => $office->education_monitor_id,
            'name' => $office->name,
            'monitor' => $this->whenLoaded('monitor', function (EducationMonitor $monitor): array {
                return $monitor->only(['id', 'uuid', 'name']);
            }),
            'phone_number' => $office->phone_number,
            'whatsapp_phone_number' => $office->whatsapp_phone_number,
            'formatted_whatsapp_phone_number' => $office->formatted_whatsapp_phone_number,
            'address' => $office->address,
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
            'has_coordinates' => $office->hasCoordinates(),
            'schools' => $this->whenLoaded('schools', function (Collection $schools): array {
                return $schools->map->only(['id', 'uuid', 'name'])->all();
            }),
            'schools_count' => $this->whenHas('schools_count', (int) ($office->schools_count ?? 0), 0),
            'students_count' => $this->whenHas('students_count', (int) ($office->students_count ?? 0), 0),
        ];
    }
}
