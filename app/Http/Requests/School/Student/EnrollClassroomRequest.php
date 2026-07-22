<?php

namespace App\Http\Requests\School\Student;

use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollClassroomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'classroom_id' => [
                'required',
                Rule::exists(Classroom::class, 'id')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('school_id', auth('school')->user()->organization_id),
            ],
        ];
    }
}
