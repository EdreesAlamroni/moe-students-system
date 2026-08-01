<?php

namespace App\Http\Requests\School;

use App\Enums\SchoolStudentsGender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentsGenderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'students_gender' => [
                'required',
                Rule::enum(SchoolStudentsGender::class),
            ],
        ];
    }
}
