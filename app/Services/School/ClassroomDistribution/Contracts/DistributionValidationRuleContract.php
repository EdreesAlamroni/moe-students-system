<?php

namespace App\Services\School\ClassroomDistribution\Contracts;

use Illuminate\Http\Request;

interface DistributionValidationRuleContract
{
    /**
     * Get the validation rules for the distribution method.
     */
    public function rules(Request $request): array;
}
