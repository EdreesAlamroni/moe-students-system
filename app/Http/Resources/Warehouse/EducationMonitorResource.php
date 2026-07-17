<?php

namespace App\Http\Resources\Warehouse;

use App\Models\EducationMonitor;
use App\Models\Municipal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationMonitorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var EducationMonitor $monitor */
        $monitor = $this->resource;

        return [
            'id' => $monitor->id,
            'uuid' => $monitor->uuid,
            'name' => $monitor->name,
            'municipal' => $this->whenLoaded('municipal', function (Municipal $municipal): array {
                return $municipal->only(['id', 'uuid', 'name']);
            }),
            'phone_number' => $monitor->phone_number,
            'whatsapp_phone_number' => $monitor->whatsapp_phone_number,
            'formatted_whatsapp_phone_number' => $monitor->formatted_whatsapp_phone_number,
            'address' => $monitor->address,
            'latitude' => $monitor->latitude,
            'longitude' => $monitor->longitude,
            'has_coordinates' => $monitor->hasCoordinates(),
            'offices_count' => (int) ($monitor->offices_count ?? 0),
            'schools_count' => (int) ($monitor->schools_count ?? 0),
            'students_count' => (int) ($monitor->students_count ?? 0),
        ];
    }
}
