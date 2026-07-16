<?php

namespace App\Http\Resources\EducationMonitor;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = $this->resource;

        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'scope' => $user->scope->toArray(),
            'role' => $user->role->toArray(),
            'state' => $user->state->toArray(),
            'request_state' => $user->request_state->toArray(),
            'organization' => $user->resolvedOrganization(),
        ];
    }
}
