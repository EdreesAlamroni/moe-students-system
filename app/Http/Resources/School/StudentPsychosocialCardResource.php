<?php

namespace App\Http\Resources\School;

use App\Enums\StudentBehavioralProblem;
use App\Models\Nationality;
use App\Models\StudentPsychosocialCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class StudentPsychosocialCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var StudentPsychosocialCard $card */
        $card = $this->resource;

        if ($card === null) {
            return [];
        }

        return [
            'id' => $card->id,
            'uuid' => $card->uuid,
            'guardian_name' => $card->guardian_name,
            'guardian_date_of_birth' => $card->guardian_date_of_birth?->toDateString(),
            'guardian_nationality_id' => $card->guardian_nationality_id,
            'guardian_nationality' => $this->whenLoaded('guardianNationality', function (Nationality $nationality) {
                return $nationality->only(['name']);
            }),
            'guardian_relationship' => $card->guardian_relationship,
            'guardian_phone_number' => $card->guardian_phone_number,
            'guardian_education_level' => $card->guardian_education_level,
            'guardian_job_title' => $card->guardian_job_title,
            'guardian_work_place' => $card->guardian_work_place,
            'mother_date_of_birth' => $card->mother_date_of_birth?->toDateString(),
            'mother_nationality_id' => $card->mother_nationality_id,
            'mother_nationality' => $this->whenLoaded('motherNationality', function (Nationality $nationality) {
                return $nationality->only(['name']);
            }),
            'mother_phone_number' => $card->mother_phone_number,
            'mother_education_level' => $card->mother_education_level,
            'mother_profession' => $card->mother_profession,
            'mother_work_place' => $card->mother_work_place,
            'number_of_family_members' => $card->number_of_family_members,
            'student_family_order' => $card->student_family_order,
            'number_of_siblings' => $card->number_of_siblings,
            'student_living_situation' => $card->student_living_situation?->toArray(),
            'family_situation_reason' => $card->family_situation_reason?->toArray(),
            'residential_area' => $card->residential_area,
            'residential_street' => $card->residential_street,
            'nearest_landmark' => $card->nearest_landmark,
            'previous_activities' => $card->previous_activities,
            'talents' => $card->talents,
            'previous_diseases' => $card->previous_diseases,
            'physical_disability_type' => $card->physical_disability_type,
            'vision_level' => $card->vision_level?->toArray(),
            'hearing_level' => $card->hearing_level?->toArray(),
            'family_income' => $card->family_income?->toArray(),
            'accommodation_type' => $card->accommodation_type?->toArray(),
            'accommodation_form' => $card->accommodation_form?->toArray(),
            'behavioral_problems' => Arr::map($card->behavioral_problems, function (array $problem) {
                return [
                    'label' => StudentBehavioralProblem::from($problem['behavior'])->label(),
                    'behavior' => $problem['behavior'],
                    'has_problem' => boolval($problem['has_problem']),
                    'notes' => $problem['notes'],
                ];
            }),
            'guardian_representative_name' => $card->guardian_representative_name,
            'guardian_representative_relationship' => $card->guardian_representative_relationship,
            'guardian_representative_id_card_number' => $card->guardian_representative_id_card_number,
            'guardian_representative_phone_number' => $card->guardian_representative_phone_number,
            'guardian_representative_work_place' => $card->guardian_representative_work_place,
        ];
    }
}
