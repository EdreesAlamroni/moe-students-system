<?php

namespace App\Http\Resources\Shared;

use App\Models\SchoolPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        $user->loadMissing(['organization']);

        $academicPeriodLabel = $user->organization instanceof SchoolPeriod
            ? $user->organization->academic_period->displayName()
            : null;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'initial_password' => $user->hasInitialPassword() ? $user->initial_password : null,
            'academic_period_label' => $academicPeriodLabel,
        ];
    }
}
