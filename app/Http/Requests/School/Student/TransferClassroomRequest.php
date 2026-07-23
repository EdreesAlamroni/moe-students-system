<?php

namespace App\Http\Requests\School\Student;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        /** @var Student $student */
        $student = $this->route('student');

        $student->loadMissing(['enrollment']);

        return [
            'classroom_id' => [
                'required',
                Rule::exists(Classroom::class, 'id')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('school_id', auth('school')->user()->organization_id)
                    ->where('grade_level_id', $student->enrollment?->grade_level_id),
                function (string $attribute, mixed $value, Closure $fail) use ($student): void {
                    if ((int) $value === (int) $student->enrollment?->classroom_id) {
                        $fail(__('validation.custom.classroom_id.different'));
                    }
                },
            ],
        ];
    }
}
