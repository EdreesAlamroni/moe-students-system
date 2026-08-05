<?php

namespace App\Http\Resources\EducationServicesOffice;

use App\Models\EducationMonitor;
use App\Models\Nationality;
use App\Models\SchoolPeriod;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Student $student */
        $student = $this->resource;

        return [
            'id' => $student->id,
            'uuid' => $student->uuid,
            'grade_level' => $this->whenEnrollmentRelationLoaded('gradeLevel', ['id', 'name', 'educational_stage']),
            'classroom' => $this->whenEnrollmentRelationLoaded('classroom', ['id', 'name']),
            'nationality' => $this->whenLoaded('nationality', function (Nationality $nationality): array {
                return $nationality->only(['id', 'uuid', 'name', 'code']);
            }),
            'has_enrollment' => $student->hasEnrollment(),
            'number' => $student->number,
            'registration_status' => $student->registration_status->toArray(),
            'exam_enrollment_status' => $student->exam_enrollment_status->toArray(),
            'full_name' => $student->full_name,
            'first_name' => $student->first_name,
            'father_name' => $student->father_name,
            'grandfather_name' => $student->grandfather_name,
            'surname' => $student->surname,
            'father_full_name' => $student->father_full_name,
            'mother_name' => $student->mother_name,
            'passport_number' => $student->passport_number,
            'gender' => $student->gender->toArray(),
            'date_of_birth' => $student->date_of_birth->toDateString(),
            'national_id' => $student->national_id,
            'family_registration_number' => $student->family_registration_number,
            'is_libyan' => $student->is_libyan,
            'monitor' => $this->whenLoaded('monitor', function (EducationMonitor $monitor): array {
                return $monitor->only(['id', 'uuid', 'name']);
            }),
            'school_period' => $this->whenLoaded('schoolPeriod', function (SchoolPeriod $schoolPeriod): array {
                return $schoolPeriod->only(['id', 'uuid', 'name']);
            }),
        ];
    }

    /**
     * @param  list<string>  $columns
     * @return MissingValue|array<string, mixed>|null
     */
    private function whenEnrollmentRelationLoaded(string $relation, array $columns): MissingValue|array|null
    {
        /** @var Student $student */
        $student = $this->resource;

        if (! $student->relationLoaded('enrollment') || ! $student->enrollment?->relationLoaded($relation)) {
            return new MissingValue;
        }

        $related = $student->enrollment->{$relation};

        if ($related === null) {
            return null;
        }

        $data = $related->only($columns);

        if (in_array('educational_stage', $columns, true)) {
            $data['educational_stage'] = $related->educational_stage->toArray();
        }

        return $data;
    }
}
