<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AcademicYearSelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists(AcademicYear::class, 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'academic_year_id.required' => 'الرجاء اختيار السنة الدراسية',
            'academic_year_id.exists' => 'السنة الدراسية المحددة غير موجودة',
        ];
    }
}
