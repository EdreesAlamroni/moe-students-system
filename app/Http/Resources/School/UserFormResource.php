<?php

namespace App\Http\Resources\School;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFormResource extends JsonResource
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
            'organization' => $user->resolvedOrganization(),
            'role_ids' => $this->whenLoaded('roles', fn () => $user->roles->pluck('id')->values()->all(), []),
        ];
    }
}
