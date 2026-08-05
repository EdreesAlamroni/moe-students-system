<?php

namespace App\Http\Requests\EducationMonitor\School;

use App\Enums\ClassroomDistributionResetScope;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ResetClassroomDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    public function rules(): array
    {
        /** @var School $school */
        $school = $this->route('school');

        return [
            'scope' => [
                'required',
                Rule::enum(ClassroomDistributionResetScope::class),
            ],
            'grade_level_ids' => [
                'sometimes',
                'nullable',
                Rule::requiredIf(function (): bool {
                    return $this->requiresGradeLevelSelection();
                }),
                Rule::when($this->requiresGradeLevelSelection(), [
                    'array',
                    'min:1',
                ]),
            ],
            'grade_level_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(GradeLevel::class, 'id')
                    ->whereIn('id', $this->schoolGradeLevelIds($school)),
            ],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (! $this->requiresGradeLevelSelection()) {
                return;
            }

            /** @var School $school */
            $school = $this->route('school');

            $schoolGradeLevelIds = $this->schoolGradeLevelIds($school);

            if (empty($schoolGradeLevelIds)) {
                $validator->errors()->add('grade_level_ids', __('alerts.messages.classroom-distribution-reset-no-grade-levels'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $gradeLevelIds = [];

        if ($this->requiresGradeLevelSelection()) {
            $gradeLevelIds = array_map('intval', $this->array('grade_level_ids'));
        }

        $this->merge([
            'grade_level_ids' => $gradeLevelIds,
        ]);
    }

    private function requiresGradeLevelSelection(): bool
    {
        $scope = ClassroomDistributionResetScope::tryFrom($this->input('scope'));

        if ($scope === null) {
            return false;
        }

        return $scope->requiresGradeLevelSelection();
    }

    private function schoolGradeLevelIds(School $school): array
    {
        return GradeLevelSchoolPeriod::query()
            ->where('academic_year_id', '=', AcademicYear::currentId())
            ->whereIn('school_period_id', $school->periods()->select('school_periods.id'))
            ->distinct()
            ->pluck('grade_level_id')
            ->all();
    }
}
