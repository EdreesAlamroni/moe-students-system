<?php

namespace App\Http\Requests\School\ClassroomDistribution;

use App\Services\School\ClassroomDistribution\ClassroomDistributionMethodRegistry;
use Illuminate\Foundation\Http\FormRequest;

class DistributionMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        $method = $this->route('method');

        return ClassroomDistributionMethodRegistry::getValidationRules($method)->rules($this);
    }
}
