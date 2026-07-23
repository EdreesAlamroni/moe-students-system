<?php

namespace App\Http\Requests\School\Student;

use App\Enums\AccommodationForm;
use App\Enums\AccommodationType;
use App\Enums\FamilyIncome;
use App\Enums\HealthLevel;
use App\Enums\StudentBehavioralProblem;
use App\Enums\StudentFamilySituationReason;
use App\Enums\StudentLivingSituation;
use App\Models\Nationality;
use App\Rules\LibyanPhoneNumberRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePsychosocialCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        $maxDateOfBirth = Carbon::create((now()->year - 18), 01, 01)->toDateString(); // 1 Jan (18 years ago)

        return [
            // Guardian fields
            'guardian_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_date_of_birth' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d', "before_or_equal:{$maxDateOfBirth}"],
            'guardian_nationality_id' => ['sometimes', 'nullable', Rule::exists(Nationality::class, 'id')],
            'guardian_relationship' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_phone_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_education_level' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_job_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_work_place' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Mother fields
            'mother_date_of_birth' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
            'mother_nationality_id' => ['sometimes', 'nullable', Rule::exists(Nationality::class, 'id')],
            'mother_phone_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mother_education_level' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mother_profession' => ['sometimes', 'nullable', 'string', 'max:255'],
            'mother_work_place' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Family structure fields
            'number_of_family_members' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'student_family_order' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'number_of_siblings' => ['sometimes', 'nullable', 'integer', 'min:0'],

            // Living situation fields
            'student_living_situation' => ['sometimes', 'nullable', Rule::enum(StudentLivingSituation::class)],
            'family_situation_reason' => ['sometimes', 'nullable', Rule::enum(StudentFamilySituationReason::class)],
            'residential_area' => ['sometimes', 'nullable', 'string', 'max:255'],
            'residential_street' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nearest_landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'previous_activities' => ['sometimes', 'nullable', 'string'],
            'talents' => ['sometimes', 'nullable', 'string'],

            // Health fields
            'previous_diseases' => ['sometimes', 'nullable', 'string'],
            'physical_disability_type' => ['sometimes', 'nullable', 'string'],
            'vision_level' => ['sometimes', 'nullable', Rule::enum(HealthLevel::class)],
            'hearing_level' => ['sometimes', 'nullable', Rule::enum(HealthLevel::class)],

            // Accommodation fields
            'family_income' => ['sometimes', 'nullable', Rule::enum(FamilyIncome::class)],
            'accommodation_type' => ['sometimes', 'nullable', Rule::enum(AccommodationType::class)],
            'accommodation_form' => ['sometimes', 'nullable', Rule::enum(AccommodationForm::class)],

            // Behavioral problems (JSON array)
            'behavioral_problems' => ['sometimes', 'nullable', 'array'],
            'behavioral_problems.*.behavior' => ['sometimes', 'nullable', Rule::enum(StudentBehavioralProblem::class)],
            'behavioral_problems.*.has_problem' => ['sometimes', 'nullable', 'boolean'],
            'behavioral_problems.*.notes' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Guardian representative fields
            'guardian_representative_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_representative_relationship' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_representative_id_card_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'guardian_representative_phone_number' => ['sometimes', 'nullable', 'string', new LibyanPhoneNumberRule],
            'guardian_representative_work_place' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'behavioral_problems' => json_decode($this->input('behavioral_problems', '[]'), true),
        ]);
    }

    public function validatedAttributes(): array
    {
        return $this->validated();
    }
}
