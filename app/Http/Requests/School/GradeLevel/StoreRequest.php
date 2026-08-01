<?php

namespace App\Http\Requests\School\GradeLevel;

use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRequest extends FormRequest
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

                /** @var School $school */
                $school = auth('school')->user()->organization;

                $availableGradeLevelIds = $school->availableGradeLevels()->pluck('id');

                if ($gradeLevelIds->diff($availableGradeLevelIds)->isNotEmpty()) {
                    $validator->errors()->add(
                        'grade_levels',
                        __('validation.custom.grade_levels.must_be_available_for_school'),
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

    /**
     * Grade level IDs keyed by ID, each with the pivot attributes needed to attach them.
     *
     * @return array<int, array{academic_year_id: int}>
     */
    public function gradeLevelsToAttach(): array
    {
        return $this->gradeLevelIds()->mapWithKeys(fn (int $gradeLevelId): array => [
            $gradeLevelId => ['academic_year_id' => AcademicYear::currentId()],
        ])->all();
    }
}
