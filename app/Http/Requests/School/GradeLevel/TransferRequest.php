<?php

namespace App\Http\Requests\School\GradeLevel;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\GradeLevelSchoolPeriod;
use App\Models\SchoolPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'grade_levels' => [
                'required',
                'array',
                'min:1',
            ],
            'grade_levels.*' => [
                'integer',
                'distinct',
                Rule::exists(GradeLevel::class, 'id'),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $gradeLevelIds = $this->gradeLevelIds();

                if ($gradeLevelIds->isEmpty()) {
                    return;
                }

                /** @var SchoolPeriod $sourcePeriod */
                $sourcePeriod = auth('school')->user()->organization;

                $destinationPeriod = $sourcePeriod->siblingPeriod();

                if (is_null($destinationPeriod)) {
                    $validator->errors()->add(
                        'grade_levels',
                        __('validation.custom.grade_levels.must_have_sibling_period'),
                    );

                    return;
                }

                $currentAcademicYearId = AcademicYear::currentId();

                if (is_null($currentAcademicYearId)) {
                    $validator->errors()->add(
                        'grade_levels',
                        __('validation.custom.grade_levels.must_belong_to_current_period'),
                    );

                    return;
                }

                $sourceGradeLevelIds = GradeLevelSchoolPeriod::query()
                    ->where('academic_year_id', '=', $currentAcademicYearId)
                    ->where('school_period_id', '=', $sourcePeriod->id)
                    ->whereIn('grade_level_id', $gradeLevelIds)
                    ->pluck('grade_level_id');

                if ($gradeLevelIds->diff($sourceGradeLevelIds)->isNotEmpty()) {
                    $validator->errors()->add(
                        'grade_levels',
                        __('validation.custom.grade_levels.must_belong_to_current_period'),
                    );

                    return;
                }

                $destinationGradeLevelIds = GradeLevelSchoolPeriod::query()
                    ->where('academic_year_id', '=', $currentAcademicYearId)
                    ->where('school_period_id', '=', $destinationPeriod->id)
                    ->whereIn('grade_level_id', $gradeLevelIds)
                    ->pluck('grade_level_id');

                if ($destinationGradeLevelIds->isNotEmpty()) {
                    $validator->errors()->add(
                        'grade_levels',
                        __('validation.custom.grade_levels.must_not_exist_in_destination_period'),
                    );
                }
            },
        ];
    }

    private function gradeLevelIds(): Collection
    {
        $gradeLevelIds = array_map('intval', $this->input('grade_levels', []));

        return collect($gradeLevelIds)->unique()->values();
    }

    public function getAttributes(): array
    {
        return $this->gradeLevelIds()->all();
    }
}
