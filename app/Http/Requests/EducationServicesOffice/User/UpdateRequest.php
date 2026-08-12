<?php

namespace App\Http\Requests\EducationServicesOffice\User;

use App\Http\Requests\Shared\User\UpdateUserRequest;
use App\Models\User;

class UpdateRequest extends UpdateUserRequest
{
    public function authorize(): bool
    {
        return auth('education_services_office')->check();
    }

    protected function schoolIdRules(): array
    {
        return [
            'school_id' => $this->schoolIdRulesForOffice($this->currentOfficeId()),
        ];
    }

    private function currentOfficeId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_services_office');

        return $user?->organization_id;
    }
}
