<?php

namespace App\Http\Requests\Administration\School;

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'education_monitor_id' => [
                'required',
                'integer',
                Rule::exists(EducationMonitor::class, 'id'),
            ],
            'education_services_office_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::requiredIf(function () {
                    return $this->monitorHasOffices();
                }),
                Rule::exists(EducationServicesOffice::class, 'id')
                    ->where('education_monitor_id', $this->input('education_monitor_id')),
            ],
            'type' => [
                'required',
                Rule::enum(SchoolType::class),
            ],
            'academic_period' => [
                'required',
                Rule::enum(SchoolAcademicPeriod::class),
            ],
            'same_school_name' => [
                'sometimes',
                'boolean',
            ],
            'educational_company_name' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isPrivateType();
                }),
                'string',
                'max:255',
            ],
            'branch_type' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isPrivateType();
                }),
                Rule::enum(SchoolBranchType::class),
            ],
            'building_type' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isPrivateType();
                }),
                Rule::enum(SchoolBuildingType::class),
            ],
            'name' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return ! $this->isDualPeriod() || $this->usesSharedSchoolName();
                }),
                'string',
                'max:255',
            ],
            'name_morning' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->usesSeparateSchoolNames();
                }),
                'string',
                'max:255',
            ],
            'name_evening' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->usesSeparateSchoolNames();
                }),
                'string',
                'max:255',
            ],
            'students_gender' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return ! $this->isDualPeriod();
                }),
                Rule::enum(SchoolStudentsGender::class),
            ],
            'students_gender_morning' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isDualPeriod();
                }),
                Rule::enum(SchoolStudentsGender::class),
            ],
            'students_gender_evening' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isDualPeriod();
                }),
                Rule::enum(SchoolStudentsGender::class),
            ],
            'educational_stages' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return ! $this->isDualPeriod();
                }),
                'array',
                'min:1',
            ],
            'educational_stages.*' => [
                Rule::enum(SchoolEducationalStageEnum::class),
            ],
            'educational_stages_morning' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isDualPeriod();
                }),
                'array',
                'min:1',
            ],
            'educational_stages_morning.*' => [
                Rule::enum(SchoolEducationalStageEnum::class),
            ],
            'educational_stages_evening' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isDualPeriod();
                }),
                'array',
                'min:1',
            ],
            'educational_stages_evening.*' => [
                Rule::enum(SchoolEducationalStageEnum::class),
            ],
        ];
    }

    public function getAttributes(?string $key = null): array
    {
        $sharedValues = $this->only([
            'education_monitor_id',
            'education_services_office_id',
            'type',
            'educational_company_name',
            'branch_type',
            'building_type',
        ]);

        if (! $this->isDualPeriod()) {
            $academicPeriod = $this->input('academic_period');

            $attributes = [
                'schools' => [
                    $academicPeriod => Arr::merge($sharedValues, [
                        'name' => $this->input('name'),
                        'academic_period' => $academicPeriod,
                        'students_gender' => $this->input('students_gender'),
                    ]),
                ],
                'educational_stages' => [
                    $academicPeriod => $this->buildEducationalStages('educational_stages'),
                ],
            ];

            return is_null($key) ? $attributes : ($attributes[$key] ?? []);
        }

        $morningPeriod = SchoolAcademicPeriod::MORNING->value;
        $eveningPeriod = SchoolAcademicPeriod::EVENING->value;

        $sharedName = $this->input('name');
        $morningName = $this->usesSharedSchoolName() ? $sharedName : $this->input('name_morning');
        $eveningName = $this->usesSharedSchoolName() ? $sharedName : $this->input('name_evening');

        $attributes = [
            'schools' => [
                $morningPeriod => Arr::merge($sharedValues, [
                    'name' => $morningName,
                    'academic_period' => $morningPeriod,
                    'students_gender' => $this->input('students_gender_morning'),
                ]),
                $eveningPeriod => Arr::merge($sharedValues, [
                    'name' => $eveningName,
                    'academic_period' => $eveningPeriod,
                    'students_gender' => $this->input('students_gender_evening'),
                ]),
            ],
            'educational_stages' => [
                $morningPeriod => $this->buildEducationalStages('educational_stages_morning'),
                $eveningPeriod => $this->buildEducationalStages('educational_stages_evening'),
            ],
        ];

        return is_null($key) ? $attributes : ($attributes[$key] ?? []);
    }

    protected function prepareForValidation(): void
    {
        $educationalStages = null;
        $educationalStagesMorning = $educationalStagesEvening = null;

        if (! $this->isDualPeriod()) {
            $educationalStages = $this->decodeEducationalStages('educational_stages');
        }

        if ($this->isDualPeriod()) {
            $educationalStagesMorning = $this->decodeEducationalStages('educational_stages_morning');
            $educationalStagesEvening = $this->decodeEducationalStages('educational_stages_evening');
        }

        $usesSharedSchoolName = $this->usesSharedSchoolName();

        $this->merge([
            'same_school_name' => $usesSharedSchoolName,

            'educational_company_name' => $this->isPrivateType() ? $this->input('educational_company_name') : null,
            'branch_type' => $this->isPrivateType() ? $this->input('branch_type') : null,
            'building_type' => $this->isPrivateType() ? $this->input('building_type') : null,

            'name' => (! $this->isDualPeriod() || $usesSharedSchoolName) ? $this->input('name') : null,
            'name_morning' => $this->usesSeparateSchoolNames() ? $this->input('name_morning') : null,
            'name_evening' => $this->usesSeparateSchoolNames() ? $this->input('name_evening') : null,

            'students_gender' => ! $this->isDualPeriod() ? $this->input('students_gender') : null,
            'students_gender_morning' => $this->isDualPeriod() ? $this->input('students_gender_morning') : null,
            'students_gender_evening' => $this->isDualPeriod() ? $this->input('students_gender_evening') : null,

            'educational_stages' => $educationalStages,
            'educational_stages_morning' => $educationalStagesMorning,
            'educational_stages_evening' => $educationalStagesEvening,
        ]);
    }

    protected function isPrivateType(): bool
    {
        return $this->enum('type', SchoolType::class) === SchoolType::PRIVATE;
    }

    protected function isDualPeriod(): bool
    {
        return $this->enum('academic_period', SchoolAcademicPeriod::class) === SchoolAcademicPeriod::DUAL_PERIOD;
    }

    protected function usesSharedSchoolName(): bool
    {
        return $this->isDualPeriod() && $this->boolean('same_school_name');
    }

    protected function usesSeparateSchoolNames(): bool
    {
        return $this->isDualPeriod() && ! $this->usesSharedSchoolName();
    }

    protected function monitorHasOffices(): bool
    {
        $monitorId = $this->input('education_monitor_id');

        if (blank($monitorId)) {
            return false;
        }

        return EducationServicesOffice::query()
            ->where('education_monitor_id', '=', $monitorId)
            ->exists();
    }

    protected function decodeEducationalStages(string $key): ?array
    {
        $value = $this->input($key);

        if (is_array($value)) {
            return $value ?: null;
        }

        if (is_string($value)) {
            return json_decode($value, true) ?: null;
        }

        return null;
    }

    protected function buildEducationalStages(string $key): array
    {
        $academicYearId = AcademicYear::currentId();

        if (is_null($academicYearId)) {
            return [];
        }

        $stages = [];

        foreach (array_unique($this->input($key, [])) as $stage) {
            $stages[] = [
                'academic_year_id' => $academicYearId,
                'stage' => $stage,
            ];
        }

        return $stages;
    }
}
