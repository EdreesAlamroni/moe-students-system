<?php

namespace App\Http\Requests\EducationMonitor\User;

use App\Http\Requests\Shared\User\UpdateUserRequest;
use App\Models\User;

class UpdateRequest extends UpdateUserRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    protected function schoolIdRules(): array
    {
        return [
            'school_id' => $this->schoolIdRulesForMonitor($this->currentMonitorId()),
        ];
    }

    private function currentMonitorId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_monitor');

        return $user?->organization_id;
    }
}
