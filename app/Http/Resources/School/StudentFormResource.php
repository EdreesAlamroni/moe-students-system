<?php

namespace App\Http\Resources\School;

use App\Models\Nationality;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Student $student */
        $student = $this->resource;

        return [
            'id' => $student->id,
            'uuid' => $student->uuid,
            'nationality_id' => $student->nationality_id,
            'nationality' => $this->whenLoaded('nationality', function (Nationality $nationality) {
                return $nationality->only(['name']);
            }),
            'number' => $student->number,
            'registration_status' => $student->registration_status->toArray(),
            'full_name' => $student->full_name,
            'first_name' => $student->first_name,
            'father_name' => $student->father_name,
            'grandfather_name' => $student->grandfather_name,
            'surname' => $student->surname,
            'mother_name' => $student->mother_name,
            'passport_number' => $student->passport_number,
            'gender' => $student->gender->toArray(),
            'date_of_birth' => $student->date_of_birth?->toDateString(),
            'national_id' => $student->national_id,
            'family_registration_number' => $student->family_registration_number,
            'is_libyan' => $student->is_libyan,
        ];
    }
}
