<?php

namespace App\Http\Resources\EducationMonitor;

use App\Http\Resources\DirectModelCollection;
use App\Models\Student;
use Illuminate\Http\Request;

class TransferableStudentCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (Student $student): array => [
            'id' => $student->id,
            'uuid' => $student->uuid,
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
            'date_of_birth' => $student->date_of_birth->toDateString(),
            'national_id' => $student->national_id,
            'family_registration_number' => $student->family_registration_number,
            'is_libyan' => $student->is_libyan,
            'nationality' => $student->relationLoaded('nationality')
                ? $student->nationality->only(['id', 'name'])
                : null,
            'grade_level' => $student->relationLoaded('enrollment.gradeLevel')
                ? $student->enrollment->gradeLevel->only(['id', 'name'])
                : null,
        ])->all();
    }
}
