<?php

namespace App\Http\Requests\Shared\School;

use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

abstract class StoreSchoolRequest extends FormRequest
{
    use ValidatesSchoolOrganization;

    /**
     * @return array<string, array<int, mixed>>
     */
    abstract protected function organizationRules(): array;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return array_merge(
            $this->organizationRules(),
            $this->schoolRules(),
        );
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function schoolRules(): array
    {
        return [
            'type' => [
                'required',
                Rule::enum(SchoolType::class),
            ],
            'academic_period' => [
                'required',
                Rule::enum(SchoolAcademicPeriod::class),
            ],
            'is_same_school' => [
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
                    return ! $this->isDualPeriod() || $this->isSameSchool();
                }),
                'string',
                'max:255',
            ],
            'name_morning' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isSeparateSchool();
                }),
                'string',
                'max:255',
            ],
            'name_evening' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function () {
                    return $this->isSeparateSchool();
                }),
                'string',
                'max:255',
            ],
            'students_gender' => [
                'sometimes',
                'nullable',
                Rule::enum(SchoolStudentsGender::class),
            ],
            'students_gender_morning' => [
                'sometimes',
                'nullable',
                Rule::enum(SchoolStudentsGender::class),
            ],
            'students_gender_evening' => [
                'sometimes',
                'nullable',
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
            'grade_levels' => [
                'sometimes',
                'nullable',
                'array',
                'min:1',
            ],
            'grade_levels.*' => [
                'integer',
                'distinct',
                Rule::exists(GradeLevel::class, 'id'),
            ],
            'grade_levels_morning' => [
                'sometimes',
                'nullable',
                'array',
                'min:1',
            ],
            'grade_levels_morning.*' => [
                'integer',
                'distinct',
                Rule::exists(GradeLevel::class, 'id'),
            ],
            'grade_levels_evening' => [
                'sometimes',
                'nullable',
                'array',
                'min:1',
            ],
            'grade_levels_evening.*' => [
                'integer',
                'distinct',
                Rule::exists(GradeLevel::class, 'id'),
            ],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->isDualPeriod()) {
                    $this->ensureGradeLevelsBelongToStages(
                        $validator,
                        'grade_levels',
                        'educational_stages',
                    );

                    return;
                }

                $this->ensureGradeLevelsBelongToStages(
                    $validator,
                    'grade_levels_morning',
                    'educational_stages_morning',
                );

                $this->ensureGradeLevelsBelongToStages(
                    $validator,
                    'grade_levels_evening',
                    'educational_stages_evening',
                );

                $this->ensureUniqueGradeLevelsAcrossPeriods($validator);
            },
        ];
    }

    public function getAttributes(?string $key = null): array
    {
        if (! $this->isDualPeriod()) {
            $academicPeriod = $this->input('academic_period');

            $attributes = [
                'school_aggregates' => [
                    [
                        'school' => $this->buildSchoolRecord($this->input('name')),
                        'periods' => [
                            $academicPeriod => $this->buildSchoolPeriodRecord(
                                academicPeriod: $academicPeriod,
                                studentsGender: $this->input('students_gender'),
                            ),
                        ],
                    ],
                ],
                'educational_stages' => [
                    $academicPeriod => $this->buildEducationalStages('educational_stages'),
                ],
                'grade_levels' => [
                    $academicPeriod => $this->buildGradeLevels('grade_levels'),
                ],
            ];

            return is_null($key) ? $attributes : ($attributes[$key] ?? []);
        }

        $morningPeriod = SchoolAcademicPeriod::MORNING->value;
        $eveningPeriod = SchoolAcademicPeriod::EVENING->value;

        $attributes = [
            'school_aggregates' => $this->buildDualPeriodSchoolAggregates(
                morningPeriod: $morningPeriod,
                eveningPeriod: $eveningPeriod,
            ),
            'educational_stages' => [
                $morningPeriod => $this->buildEducationalStages('educational_stages_morning'),
                $eveningPeriod => $this->buildEducationalStages('educational_stages_evening'),
            ],
            'grade_levels' => [
                $morningPeriod => $this->buildGradeLevels('grade_levels_morning'),
                $eveningPeriod => $this->buildGradeLevels('grade_levels_evening'),
            ],
        ];

        return is_null($key) ? $attributes : ($attributes[$key] ?? []);
    }

    protected function prepareForValidation(): void
    {
        $educationalStages = null;
        $educationalStagesMorning = $educationalStagesEvening = null;
        $gradeLevels = null;
        $gradeLevelsMorning = $gradeLevelsEvening = null;

        if (! $this->isDualPeriod()) {
            $educationalStages = $this->decodeArrayInput('educational_stages');
            $gradeLevels = $this->decodeArrayInput('grade_levels');
        }

        if ($this->isDualPeriod()) {
            $educationalStagesMorning = $this->decodeArrayInput('educational_stages_morning');
            $educationalStagesEvening = $this->decodeArrayInput('educational_stages_evening');
            $gradeLevelsMorning = $this->decodeArrayInput('grade_levels_morning');
            $gradeLevelsEvening = $this->decodeArrayInput('grade_levels_evening');
        }

        $isSameSchool = $this->isSameSchool();

        $this->merge([
            'is_same_school' => $isSameSchool,

            'educational_company_name' => $this->isPrivateType() ? $this->input('educational_company_name') : null,
            'branch_type' => $this->isPrivateType() ? $this->input('branch_type') : null,
            'building_type' => $this->isPrivateType() ? $this->input('building_type') : null,

            'name' => (! $this->isDualPeriod() || $isSameSchool) ? $this->input('name') : null,
            'name_morning' => $this->isSeparateSchool() ? $this->input('name_morning') : null,
            'name_evening' => $this->isSeparateSchool() ? $this->input('name_evening') : null,

            'students_gender' => ! $this->isDualPeriod() ? $this->input('students_gender') : null,
            'students_gender_morning' => $this->isDualPeriod() ? $this->input('students_gender_morning') : null,
            'students_gender_evening' => $this->isDualPeriod() ? $this->input('students_gender_evening') : null,

            'educational_stages' => $educationalStages,
            'educational_stages_morning' => $educationalStagesMorning,
            'educational_stages_evening' => $educationalStagesEvening,

            'grade_levels' => $gradeLevels,
            'grade_levels_morning' => $gradeLevelsMorning,
            'grade_levels_evening' => $gradeLevelsEvening,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSchoolRecord(?string $name): array
    {
        return Arr::merge($this->sharedSchoolValues(), [
            'name' => $name,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSchoolPeriodRecord(string $academicPeriod, mixed $studentsGender): array
    {
        return [
            'academic_period' => $academicPeriod,
            'students_gender' => $studentsGender,
        ];
    }

    /**
     * @return array<int, array{school: array<string, mixed>, periods: array<string, array<string, mixed>>}>
     */
    protected function buildDualPeriodSchoolAggregates(string $morningPeriod, string $eveningPeriod): array
    {
        $morning = $this->buildSchoolPeriodRecord(
            academicPeriod: $morningPeriod,
            studentsGender: $this->input('students_gender_morning'),
        );
        $evening = $this->buildSchoolPeriodRecord(
            academicPeriod: $eveningPeriod,
            studentsGender: $this->input('students_gender_evening'),
        );

        if ($this->isSameSchool()) {
            return [
                [
                    'school' => $this->buildSchoolRecord($this->input('name')),
                    'periods' => [
                        $morningPeriod => $morning,
                        $eveningPeriod => $evening,
                    ],
                ],
            ];
        }

        return [
            [
                'school' => $this->buildSchoolRecord($this->input('name_morning')),
                'periods' => [$morningPeriod => $morning],
            ],
            [
                'school' => $this->buildSchoolRecord($this->input('name_evening')),
                'periods' => [$eveningPeriod => $evening],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedSchoolValues(): array
    {
        return Arr::merge(
            $this->only([
                'type',
                'educational_company_name',
                'branch_type',
                'building_type',
            ]),
            [
                'education_monitor_id' => $this->educationMonitorId(),
                'education_services_office_id' => $this->resolvedEducationServicesOfficeId(),
            ],
        );
    }

    protected function isPrivateType(): bool
    {
        return $this->enum('type', SchoolType::class) === SchoolType::PRIVATE;
    }

    protected function isDualPeriod(): bool
    {
        return $this->enum('academic_period', SchoolAcademicPeriod::class) === SchoolAcademicPeriod::DUAL_PERIOD;
    }

    protected function isSameSchool(): bool
    {
        return $this->isDualPeriod() && $this->boolean('is_same_school');
    }

    protected function isSeparateSchool(): bool
    {
        return $this->isDualPeriod() && ! $this->isSameSchool();
    }

    protected function decodeArrayInput(string $key): ?array
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
        $currentAcademicYearId = AcademicYear::currentId();

        if (is_null($currentAcademicYearId)) {
            return [];
        }

        $stages = [];

        foreach (array_unique($this->input($key, [])) as $stage) {
            $stages[] = [
                'academic_year_id' => $currentAcademicYearId,
                'stage' => $stage,
            ];
        }

        return $stages;
    }

    /**
     * @return array<int, array{academic_year_id: int}>
     */
    protected function buildGradeLevels(string $key): array
    {
        $currentAcademicYearId = AcademicYear::currentId();

        $ids = $this->input($key) ?? [];

        if (is_null($currentAcademicYearId) || ! is_array($ids) || $ids === []) {
            return [];
        }

        $gradeLevels = [];

        foreach (array_unique(array_map('intval', $ids)) as $gradeLevelId) {
            $gradeLevels[$gradeLevelId] = [
                'academic_year_id' => $currentAcademicYearId,
            ];
        }

        return $gradeLevels;
    }

    protected function ensureGradeLevelsBelongToStages(Validator $validator, string $gradeLevelsKey, string $stagesKey): void
    {
        $ids = $this->input($gradeLevelsKey) ?? [];
        $stages = $this->input($stagesKey) ?? [];

        if (! is_array($ids) || $ids === [] || ! is_array($stages) || $stages === []) {
            return;
        }

        $gradeLevelIds = array_values(array_unique(array_map('intval', $ids)));

        $invalidExists = GradeLevel::query()
            ->whereIn('id', $gradeLevelIds)
            ->whereNotIn('educational_stage', $stages)
            ->exists();

        if ($invalidExists) {
            $validator->errors()->add(
                $gradeLevelsKey,
                __('validation.custom.grade_levels.must_belong_to_educational_stages'),
            );
        }
    }

    protected function ensureUniqueGradeLevelsAcrossPeriods(Validator $validator): void
    {
        if (! $this->isSameSchool()) {
            return;
        }

        $morningIds = array_map('intval', $this->input('grade_levels_morning') ?? []);
        $eveningIds = array_map('intval', $this->input('grade_levels_evening') ?? []);

        if ($morningIds === [] || $eveningIds === []) {
            return;
        }

        if (array_intersect($morningIds, $eveningIds) !== []) {
            $message = __('validation.custom.grade_levels.must_be_unique_across_periods');

            $validator->errors()->add('grade_levels_morning', $message);
            $validator->errors()->add('grade_levels_evening', $message);
        }
    }
}
