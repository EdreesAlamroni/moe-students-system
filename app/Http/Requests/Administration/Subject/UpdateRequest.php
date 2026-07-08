<?php

namespace App\Http\Requests\Administration\Subject;

use App\Models\GradeLevel;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        return [
            'grade_level_id' => [
                'required',
                'integer',
                Rule::exists(GradeLevel::class, 'id'),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Subject::class, 'code')->ignore($this->route('subject')),
            ],
            'included_in_total_score' => [
                'required',
                'boolean',
            ],
            'needs_lab' => [
                'required',
                'boolean',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function getAttributes(): array
    {
        return $this->validated();
    }
}
