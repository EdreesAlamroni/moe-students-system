<?php

namespace App\Http\Requests\EducationMonitor\Student;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_ids.required' => __('validation.custom.education_monitor_student_transfer.student_ids_required'),
            'student_ids.array' => __('validation.custom.student_transfer.student_ids_array'),
            'student_ids.min' => __('validation.custom.education_monitor_student_transfer.student_ids_min'),
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

            /** @var list<int> $studentIds */
            $studentIds = $this->input('student_ids', []);

            $this->validateStudentTransferEligibility($validator, $studentIds);
        });
    }

    /**
     * @param  list<int>  $studentIds
     */
    private function validateStudentTransferEligibility(Validator $validator, array $studentIds): void
    {
        /** @var Collection<int, Student> $students */
        $students = Student::query()
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

            if ($message = $this->resolveTransferEligibilityError($student)) {
                $validator->errors()->add($attribute, $message);
            }
        }
    }

    private function resolveTransferEligibilityError(Student $student): ?string
    {
        if (! is_null($student->school_id)) {
            return __('validation.custom.student_transfer.already_in_school', [
                'name' => $student->fullName,
            ]);
        }

        if (! is_null($student->education_monitor_id)) {
            return __('validation.custom.education_monitor_student_transfer.already_in_monitor', [
                'name' => $student->fullName,
            ]);
        }

        return null;
    }
}
