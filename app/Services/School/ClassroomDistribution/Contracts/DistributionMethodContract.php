<?php

namespace App\Services\School\ClassroomDistribution\Contracts;

use App\Http\Requests\School\ClassroomDistribution\DistributionMethodRequest;
use Illuminate\Http\Request;

interface DistributionMethodContract
{
    /**
     * Get the credentials for the distribution method view page.
     */
    public function credentials(Request $request): array;

    /**
     * Apply the distribution method.
     */
    public function apply(DistributionMethodRequest $request): void;
}
