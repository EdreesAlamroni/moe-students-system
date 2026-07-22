<?php

namespace App\Http\Requests\School\Student;

use App\Enums\StudentRegistrationStatus;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchool;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Rules\NationalIdRule;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        // TODO: Review this logic.
        $maxDateOfBirth = Carbon::create((now()->year - 4), 01, 01)->toDateString();

        return [
            'grade_level_id' => [
                'required',
                Rule::exists(GradeLevel::class, 'id'),
                Rule::exists(GradeLevelSchool::class, 'grade_level_id')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('school_id', auth('school')->user()->organization_id),
            ],
            'nationality_id' => [
                'required',
                Rule::exists(Nationality::class, 'id'),
            ],
            'registration_status' => [
                'required',
                Rule::enum(StudentRegistrationStatus::class),
            ],
            'student_first_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'student_father_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'student_grandfather_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'student_surname' => [
                'required',
                'string',
                'max:255',
            ],
            'mother_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'passport_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique(Student::class, 'passport_number')->ignore($this->route('student')),
            ],
            'gender' => [
                'required',
                'string',
                'in:male,female',
            ],
            'date_of_birth' => [
                'required',
                'date',
                'date_format:Y-m-d',
                "before_or_equal:{$maxDateOfBirth}",
            ],
            'national_id' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->hasLibyanNationality();
                }),
                Rule::unique(Student::class, 'national_id'),
                new NationalIdRule($this->input('date_of_birth'), $this->input('gender')),
            ],
            'family_registration_number' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->hasLibyanNationality();
                }),
                'string',
                'max:255',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $nationalId = $this->hasLibyanNationality() ? $this->input('national_id') : null;
        $familyRegistrationNumber = $this->hasLibyanNationality() ? $this->input('family_registration_number') : null;

        $this->merge([
            'national_id' => $nationalId,
            'family_registration_number' => $familyRegistrationNumber,
        ]);
    }

    private function hasLibyanNationality(): bool
    {
        return $this->integer('nationality_id') === Nationality::libyanId();
    }

    public function getAttributes(): array
    {
        /** @var School $school */
        $school = auth('school')->user()->organization;

        $data = collect($this->validated());

        $filteredData = $data->merge([
            'education_monitor_id' => $school->education_monitor_id,
            'school_id' => $school->id,
            'first_name' => $data->get('student_first_name'),
            'father_name' => $data->get('student_father_name'),
            'grandfather_name' => $data->get('student_grandfather_name'),
            'surname' => $data->get('student_surname'),
        ])->except([
            'grade_level_id',
            'student_first_name',
            'student_father_name',
            'student_grandfather_name',
            'student_surname',
        ])->all();

        return $filteredData;
    }
}
