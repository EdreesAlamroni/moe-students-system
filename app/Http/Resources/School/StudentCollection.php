<?php

namespace App\Http\Resources\School;

use App\Http\Resources\DirectModelCollection;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Student $student) => [
            'id' => $student->id,
            'uuid' => $student->uuid,
            'nationality' => $student->relationLoaded('nationality')
                ? $student->nationality->only(['name'])
                : null,
            'number' => $student->number,
            'registration_status' => $student->registration_status->toArray(),
            'full_name' => $student->full_name,
            'gender' => $student->gender->toArray(),
            'national_id' => $student->national_id,
            'family_registration_number' => $student->family_registration_number,
            'passport_number' => $student->passport_number,
            'is_libyan' => $student->is_libyan,
        ])->all();
    }
}
