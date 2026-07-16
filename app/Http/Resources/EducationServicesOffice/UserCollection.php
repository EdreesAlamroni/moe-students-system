<?php

namespace App\Http\Resources\EducationServicesOffice;

use App\Http\Resources\DirectModelCollection;
use App\Models\User;
use Illuminate\Http\Request;

class UserCollection extends DirectModelCollection
{
    public function toArray(Request $request): array
    {
        return $this->collection->map(fn (User $user): array => [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'username' => $user->username,
            'scope' => $user->scope->toArray(),
        ])->all();
    }
}
