<?php

namespace App\Actions\User;

use App\DataTransferObjects\User\CreatedDefaultUser;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\State\Activated;
use App\Support\User\DefaultUserNameBuilder;
use App\Support\User\PasswordGenerator;
use App\Support\User\UsernameGenerator;
use Spatie\Permission\Models\Role;

class CreateDefaultOrganizationUser
{
    public function __construct(
        private DefaultUserNameBuilder $userNameBuilder,
        private UsernameGenerator $usernameGenerator,
        private PasswordGenerator $passwordGenerator,
    ) {}

    public function forEducationMonitor(EducationMonitor $monitor): CreatedDefaultUser
    {
        $name = $this->userNameBuilder->forEducationMonitor($monitor);

        return $this->create(
            name: $name,
            scope: UserScope::EDUCATION_MONITOR,
            organizationId: $monitor->id,
            organizationType: EducationMonitor::class,
        );
    }

    public function forEducationServicesOffice(EducationServicesOffice $office): CreatedDefaultUser
    {
        $name = $this->userNameBuilder->forEducationServicesOffice($office);

        return $this->create(
            name: $name,
            scope: UserScope::EDUCATION_SERVICES_OFFICE,
            organizationId: $office->id,
            organizationType: EducationServicesOffice::class,
        );
    }

    public function forSchoolPeriod(SchoolPeriod $period): CreatedDefaultUser
    {
        $period->loadMissing(['school:id,name']);

        $name = $this->userNameBuilder->forSchoolPeriod($period);

        $created = $this->create(
            name: $name,
            scope: UserScope::SCHOOL,
            organizationId: $period->id,
            organizationType: SchoolPeriod::class,
        );

        $created->user->syncSchoolPeriodMemberships([(int) $period->id]);

        return $created;
    }

    private function create(
        string $name,
        UserScope $scope,
        int $organizationId,
        string $organizationType,
    ): CreatedDefaultUser {
        $initialPassword = $this->passwordGenerator->generate();

        $user = User::query()->create([
            'name' => $name,
            'username' => $this->usernameGenerator->generate(),
            'email' => null,
            'scope' => $scope,
            'role' => UserRole::EMPLOYEE,
            'state' => Activated::class,
            'request_state' => Approved::class,
            'must_change_password' => true,
            'organization_id' => $organizationId,
            'organization_type' => $organizationType,
            'password' => $initialPassword,
        ]);

        $user->forceFill([
            'initial_password' => $initialPassword,
        ])->save();

        $roles = Role::query()
            ->where('guard_name', '=', $scope->value)
            ->pluck('name')
            ->all();

        $user->syncRoles($roles);

        return new CreatedDefaultUser(
            user: $user->fresh(),
            initialPassword: $initialPassword,
        );
    }
}
