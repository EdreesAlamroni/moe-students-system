<?php

namespace App\Http\Resources\EducationMonitor;

use App\Models\EducationMonitor;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Student $student */
        $student = $this->resource;

        return [
            'id' => $student->id,
            'uuid' => $student->uuid,
            'number' => $student->number,
            'registration_status' => $student->registration_status->toArray(),
            'exam_enrollment_status' => $student->exam_enrollment_status->toArray(),
            'first_name' => $student->first_name,
            'father_name' => $student->father_name,
            'grandfather_name' => $student->grandfather_name,
            'surname' => $student->surname,
            'mother_name' => $student->mother_name,
            'gender' => $student->gender->toArray(),
            'date_of_birth' => $student->date_of_birth->toDateString(),
            'national_id' => $student->national_id,
            'family_registration_number' => $student->family_registration_number,
            'passport_number' => $student->passport_number,
            'nationality' => $this->whenLoaded('nationality', function (Nationality $nationality): array {
                return $nationality->only(['id', 'uuid', 'name', 'code']);
            }),
            'monitor' => $this->whenLoaded('monitor', function (EducationMonitor $monitor): array {
                return $monitor->only(['id', 'uuid', 'name']);
            }),
            'school' => $this->whenLoaded('school', function (School $school): array {
                return $school->only(['id', 'uuid', 'name']);
            }),
        ];
    }
}
