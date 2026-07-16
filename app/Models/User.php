<?php

namespace App\Models;

use App\Concerns\HasUuid;
use App\Concerns\ModelStateUtilities;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\RequestState\Pending;
use App\ModelStates\User\RequestState\UserRequestState;
use App\ModelStates\User\State\Activated;
use App\ModelStates\User\State\UserState;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $model_id
 * @property string|null $model_type
 * @property string $name
 * @property string $username
 * @property string $email
 * @property UserScope $scope
 * @property UserRole $role
 * @property UserState $state
 * @property UserRequestState $request_state
 * @property bool $must_change_password
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Guarded(['id'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasStates, HasUuid, ModelStateUtilities, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'model_id' => 'integer',
            'scope' => UserScope::class,
            'role' => UserRole::class,
            'state' => UserState::class,
            'request_state' => UserRequestState::class,
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function guardName(): string
    {
        return $this->scope->guard();
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $dashboard = $this->scope->getDashboardAuth();

        if ($dashboard?->supportsPasswordReset !== true) {
            return;
        }

        $this->notify(new ResetPasswordNotification($token, $dashboard->routeName('password.reset')));
    }

    /*
     * Start: Scopes
     */

    #[Scope]
    protected function onlyAdministrators(Builder $query): Builder
    {
        return $query
            ->where('scope', '=', UserScope::ADMINISTRATION->value)
            ->where('role', '=', UserRole::MANAGER->value);
    }

    #[Scope]
    protected function onlyManagers(Builder $query): Builder
    {
        return $query->where('role', '=', UserRole::MANAGER->value);
    }

    #[Scope]
    protected function onlyEmployees(Builder $query): Builder
    {
        return $query->where('role', '=', UserRole::EMPLOYEE->value);
    }

    #[Scope]
    protected function forCurrentEducationMonitor(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::EDUCATION_MONITOR);

        $descendants = [
            EducationServicesOffice::class => 'education_monitor_id',
            School::class => 'education_monitor_id',
        ];

        return $this->scopedToOrganization($query, $organizationId, EducationMonitor::class, $descendants);
    }

    #[Scope]
    protected function forCurrentEducationServicesOffice(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::EDUCATION_SERVICES_OFFICE);

        $descendants = [
            School::class => 'education_services_office_id',
        ];

        return $this->scopedToOrganization($query, $organizationId, EducationServicesOffice::class, $descendants);
    }

    #[Scope]
    protected function forCurrentSchool(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::SCHOOL);

        return $this->scopedToOrganization($query, $organizationId, School::class);
    }

    #[Scope]
    protected function forCurrentWarehouse(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::WAREHOUSE);

        return $this->scopedToOrganization($query, $organizationId, Warehouse::class);
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /*
     * End: Relations
     */

    /*
     * Start: Custom Functions
     */

    public function isNotActivated(): bool
    {
        return ! $this->state->equals(Activated::class);
    }

    public function isNotApproved(): bool
    {
        return ! $this->request_state->equals(Approved::class);
    }

    /**
     * Typed organizational context for the attached morph model.
     *
     * @return array{
     *     type: string,
     *     organization: array<string, array{id: int, name: string}>
     * }|null
     */
    public function resolvedOrganization(): ?array
    {
        $this->loadMissing(match ($this->model_type) {
            EducationServicesOffice::class, School::class => ['model.monitor'],
            default => ['model'],
        });

        $model = $this->model;

        if ($model === null) {
            return null;
        }

        $reference = static fn (Model $entity): array => [
            'id' => (int) $entity->getKey(),
            'name' => (string) $entity->getAttribute('name'),
        ];

        return match (true) {
            $this->scope === UserScope::WAREHOUSE && $model instanceof Warehouse => [
                'type' => 'warehouse',
                'organization' => [
                    'warehouse' => $reference($model),
                ],
            ],
            $this->scope === UserScope::EDUCATION_MONITOR && $model instanceof EducationMonitor => [
                'type' => 'education_monitor',
                'organization' => [
                    'education_monitor' => $reference($model),
                ],
            ],
            $this->scope === UserScope::EDUCATION_SERVICES_OFFICE && $model instanceof EducationServicesOffice => [
                'type' => 'education_services_office',
                'organization' => [
                    'education_services_office' => $reference($model),
                    'education_monitor' => $reference($model->monitor),
                ],
            ],
            $this->scope === UserScope::SCHOOL && $model instanceof School => [
                'type' => 'school',
                'organization' => [
                    'school' => $reference($model),
                    'education_monitor' => $reference($model->monitor),
                ],
            ],
            default => null,
        };
    }

    /*
     * End: Custom Functions
     */

    /**
     * @param  array<class-string<Model>, string>  $descendants
     */
    private function scopedToOrganization(Builder $query, int|string|null $organizationId, string $organizationClass, array $descendants = []): Builder
    {
        if ($organizationId === null) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($organizationId, $organizationClass, $descendants): void {
            $query
                ->where('model_type', $organizationClass)
                ->where('model_id', $organizationId);

            if ($descendants !== []) {
                $query->orWhereHasMorph('model', array_keys($descendants), function (Builder $query, string $type) use ($organizationId, $descendants): void {
                    $query->where($descendants[$type], $organizationId);
                });
            }
        });
    }

    private function authenticatedOrganizationId(UserScope $scope): ?int
    {
        return auth($scope->guard())->user()?->model_id;
    }

    public function hasAnyRelations(): bool
    {
        return false;
    }

    public function isAdministrator(): bool
    {
        return $this->scope->isAdministration() && $this->role->isManager();
    }

    public function isAdministrationStaff(): bool
    {
        return $this->scope->isAdministration();
    }

    public function isEducationMonitorStaff(): bool
    {
        return $this->scope->isEducationMonitor();
    }

    public function isEducationServicesOfficeStaff(): bool
    {
        return $this->scope->isEducationServicesOffice();
    }

    public function isSchoolStaff(): bool
    {
        return $this->scope->isSchool();
    }

    public function isWarehouseStaff(): bool
    {
        return $this->scope->isWarehouse();
    }

    public function isRoleManager(): bool
    {
        return $this->role->isManager();
    }

    public function isRoleEmployee(): bool
    {
        return $this->role->isEmployee();
    }

    public function requestIsPending(): bool
    {
        return $this->request_state->equals(Pending::class);
    }
}
