<?php

namespace App\Support\Organization;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class OrganizationContext
{
    /**
     * @return class-string<Model>
     */
    abstract protected function organizationType(): string;

    /**
     * @return list<string>
     */
    abstract protected function columns(): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected function build(Model $organization): array;

    /**
     * @return array<string, mixed>|null
     */
    public function resolve(User $user): ?array
    {
        if ($user->organization_type !== $this->organizationType() || $user->organization_id === null) {
            return null;
        }

        $organization = $this->organizationType()::query()
            ->select($this->columns())
            ->find($user->organization_id);

        if ($organization === null) {
            return null;
        }

        return $this->build($organization);
    }
}
