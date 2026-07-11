<?php

namespace App\Http\Resources\Administration;

use App\Models\EducationMonitor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationMonitorFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var EducationMonitor $monitor */
        $monitor = $this->resource;

        return [
            'id' => $monitor->id,
            'uuid' => $monitor->uuid,
            'name' => $monitor->name,
            'municipal_id' => $monitor->municipal_id,
            'phone_number' => $monitor->phone_number,
            'whatsapp_phone_number' => $monitor->whatsapp_phone_number,
            'address' => $monitor->address,
            'latitude' => $monitor->latitude,
            'longitude' => $monitor->longitude,
            'add_location_to_map' => $monitor->hasCoordinates(),
        ];
    }
}
