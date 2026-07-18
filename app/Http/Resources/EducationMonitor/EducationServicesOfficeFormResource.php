<?php

namespace App\Http\Resources\EducationMonitor;

use App\Models\EducationServicesOffice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EducationServicesOfficeFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var EducationServicesOffice $office */
        $office = $this->resource;

        return [
            'id' => $office->id,
            'uuid' => $office->uuid,
            'name' => $office->name,
            'phone_number' => $office->phone_number,
            'whatsapp_phone_number' => $office->whatsapp_phone_number,
            'address' => $office->address,
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
            'add_location_to_map' => $office->hasCoordinates(),
        ];
    }
}
