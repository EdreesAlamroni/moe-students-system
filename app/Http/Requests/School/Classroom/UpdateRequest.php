<?php

namespace App\Http\Requests\School\Classroom;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'capacity' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function getAttributes(): array
    {
        return [
            'capacity' => $this->integer('capacity'),
        ];
    }
}
