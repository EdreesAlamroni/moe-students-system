<?php

namespace App\Http\Resources\EducationServicesOffice;

use App\Http\Resources\DirectModelCollection;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Student $student): array => [
            'id' => $student->id,
            'uuid' => $student->uuid,
            'number' => $student->number,
            'full_name' => $student->full_name,
            'gender' => $student->gender->toArray(),
            'registration_status' => $student->registration_status->toArray(),
            'nationality' => $student->relationLoaded('nationality')
                ? $student->nationality->only(['id', 'name', 'code'])
                : null,
            'is_libyan' => $student->is_libyan,
            'national_id' => $student->national_id,
            'family_registration_number' => $student->family_registration_number,
            'passport_number' => $student->passport_number,
        ])->all();
    }
}
