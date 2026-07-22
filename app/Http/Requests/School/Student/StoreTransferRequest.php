<?php

namespace App\Http\Requests\School\Student;

use App\Models\School;
use App\Models\Student;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'student_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'student_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(Student::class, 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'student_ids.required' => __('validation.custom.student_transfer.student_ids_required'),
            'student_ids.array' => __('validation.custom.student_transfer.student_ids_array'),
            'student_ids.min' => __('validation.custom.student_transfer.student_ids_min'),
            'student_ids.*.required' => __('validation.custom.student_transfer.student_id_required'),
            'student_ids.*.integer' => __('validation.custom.student_transfer.student_id_integer'),
            'student_ids.*.distinct' => __('validation.custom.student_transfer.student_id_distinct'),
            'student_ids.*.exists' => __('validation.custom.student_transfer.student_id_not_found'),
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var School $school */
            $school = auth('school')->user()->organization;

            /** @var list<int> $studentIds */
            $studentIds = $this->input('student_ids', []);

            $this->validateStudentTransferEligibility($validator, $school, $studentIds);
        });
    }

    /**
     * @param  list<int>  $studentIds
     */
    private function validateStudentTransferEligibility(Validator $validator, School $school, array $studentIds): void
    {
        /** @var Collection<int, int> $schoolGradeLevelIds */
        $schoolGradeLevelIds = $school->gradeLevels()->pluck('grade_levels.id');

        /** @var Collection<int, Student> $students */
        $students = Student::query()
            ->with(['enrollment.gradeLevel', 'transfer'])
            ->whereIn('id', $studentIds)
            ->get()
            ->keyBy('id');

        foreach ($studentIds as $index => $studentId) {
            $attribute = "student_ids.{$index}";

            /** @var Student|null $student */
            $student = $students->get($studentId);

            if (is_null($student)) {
                continue;
            }

            if ($message = $this->resolveTransferEligibilityError($student, $school, $schoolGradeLevelIds)) {
                $validator->errors()->add($attribute, $message);
            }
        }
    }

    /**
     * @param  Collection<int, int>  $schoolGradeLevelIds
     */
    private function resolveTransferEligibilityError(Student $student, School $school, Collection $schoolGradeLevelIds): ?string
    {
        if (! is_null($student->school_id)) {
            return __('validation.custom.student_transfer.already_in_school', [
                'name' => $student->fullName,
            ]);
        }

        if (! is_null($student->education_monitor_id) && $student->education_monitor_id !== $school->education_monitor_id) {
            return __('validation.custom.student_transfer.wrong_education_monitor', [
                'name' => $student->fullName,
            ]);
        }

        $gradeLevelId = $student->enrollment?->grade_level_id;

        if (is_null($gradeLevelId)) {
            return __('validation.custom.student_transfer.no_grade_level', [
                'name' => $student->fullName,
            ]);
        }

        if (! $schoolGradeLevelIds->contains($gradeLevelId)) {
            return __('validation.custom.student_transfer.grade_level_not_in_school', [
                'name' => $student->fullName,
                'grade_level' => $student->enrollment?->gradeLevel?->name ?? '',
            ]);
        }

        if (! $student->isAwaitingSchoolTransfer()) {
            return __('validation.custom.student_transfer.not_awaiting_transfer', [
                'name' => $student->fullName,
            ]);
        }

        return null;
    }
}
