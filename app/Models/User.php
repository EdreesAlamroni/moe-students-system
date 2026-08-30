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
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\ModelStates\HasStates;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $uuid
 * @property int|null $organization_id
 * @property string|null $organization_type
 * @property string $name
 * @property string $username
 * @property string $email
 * @property UserScope $scope
 * @property UserRole $role
 * @property UserState $state
 * @property UserRequestState $request_state
 * @property bool $must_change_password
 * @property string $password
 * @property string|null $initial_password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Warehouse|EducationMonitor|EducationServicesOffice|SchoolPeriod|null $organization
 * @property-read EloquentCollection<int, SchoolPeriod> $schoolPeriods
 */
#[Guarded(['id', 'initial_password'])]
#[Hidden(['password', 'initial_password', 'remember_token'])]

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, HasStates, HasUuid, ModelStateUtilities, Notifiable, SoftDeletes;

    protected function casts(): array
    {
        return [
            'organization_id' => 'integer',
            'scope' => UserScope::class,
            'role' => UserRole::class,
            'state' => UserState::class,
            'request_state' => UserRequestState::class,
            'must_change_password' => 'boolean',
            'password' => 'hashed',
            'initial_password' => 'encrypted',
        ];
    }

    public static function booted(): void
    {
        static::updating(function (self $user): void {
            if ($user->isDirty('password')) {
                $user->initial_password = null;
            }
        });
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
            SchoolPeriod::class => 'education_monitor_id',
        ];

        return $this->scopedToOrganization($query, $organizationId, EducationMonitor::class, $descendants);
    }

    #[Scope]
    protected function forCurrentEducationServicesOffice(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::EDUCATION_SERVICES_OFFICE);

        $descendants = [
            SchoolPeriod::class => 'education_services_office_id',
        ];

        return $this->scopedToOrganization($query, $organizationId, EducationServicesOffice::class, $descendants);
    }

    #[Scope]
    protected function forCurrentSchool(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::SCHOOL);

        return $this->scopedToOrganization($query, $organizationId, SchoolPeriod::class);
    }

    #[Scope]
    protected function forCurrentWarehouse(Builder $query): Builder
    {
        $organizationId = $this->authenticatedOrganizationId(UserScope::WAREHOUSE);

        return $this->scopedToOrganization($query, $organizationId, Warehouse::class);
    }

    #[Scope]
    protected function orderedByScope(Builder $query): Builder
    {
        $scopes = UserScope::values();

        /** @var \Illuminate\Database\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'mysql') {
            $placeholders = implode(', ', array_fill(0, count($scopes), '?'));

            return $query->orderByRaw("FIELD(scope, {$placeholders})", $scopes);
        }

        $case = implode(' ', Arr::map($scopes, function (string $scope, int $index): string {
            return sprintf('WHEN ? THEN %d', $index + 1);
        }));

        return $query->orderByRaw(
            sprintf('CASE scope %s ELSE %d END', $case, count($scopes) + 1),
            $scopes,
        );
    }

    /*
     * End: Scopes
     */

    /*
     * Start: Relations
     */

    public function organization(): MorphTo
    {
        return $this->morphTo();
    }

    public function schoolPeriods(): BelongsToMany
    {
        return $this->belongsToMany(SchoolPeriod::class, 'school_period_user');
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

    public function hasInitialPassword(): bool
    {
        return filled($this->initial_password);
    }

    public function hasOrganization(): bool
    {
        return $this->organization_id !== null && $this->organization_type !== null;
    }

    public function belongsToDeletedOrganization(): bool
    {
        if (! $this->hasOrganization()) {
            return false;
        }

        if ($this->organization()->onlyTrashed()->exists()) {
            return true;
        }

        if ($this->organization_type !== SchoolPeriod::class) {
            return false;
        }

        $schoolPeriod = SchoolPeriod::query()->withTrashed()->find($this->organization_id);

        if ($schoolPeriod === null) {
            return false;
        }

        return School::query()->onlyTrashed()->whereKey($schoolPeriod->school_id)->exists();
    }

    public function hasOrphanedOrganization(): bool
    {
        if (! $this->hasOrganization()) {
            return false;
        }

        if (! $this->organization()->exists()) {
            return true;
        }

        if ($this->organization_type !== SchoolPeriod::class) {
            return false;
        }

        $schoolPeriod = $this->organization;

        if (! $schoolPeriod instanceof SchoolPeriod) {
            return true;
        }

        return ! School::query()->withTrashed()->whereKey($schoolPeriod->school_id)->exists();
    }

    public function hasValidOrganizationContext(): bool
    {
        if ($this->scope === UserScope::ADMINISTRATION) {
            return ! $this->hasOrganization();
        }

        $expectedType = match ($this->scope) {
            UserScope::WAREHOUSE => Warehouse::class,
            UserScope::EDUCATION_MONITOR => EducationMonitor::class,
            UserScope::EDUCATION_SERVICES_OFFICE => EducationServicesOffice::class,
            UserScope::SCHOOL => SchoolPeriod::class,
        };

        if ($this->organization_type !== $expectedType || $this->organization_id === null) {
            return false;
        }

        if ($this->belongsToDeletedOrganization() || $this->hasOrphanedOrganization()) {
            return false;
        }

        if ($this->scope === UserScope::SCHOOL && ! $this->isMemberOfSchoolPeriod((int) $this->organization_id)) {
            return false;
        }

        return true;
    }

    public function isMemberOfSchoolPeriod(int $schoolPeriodId): bool
    {
        if ($this->scope !== UserScope::SCHOOL) {
            return false;
        }

        if ($this->relationLoaded('schoolPeriods')) {
            return $this->schoolPeriods->contains('id', '=', $schoolPeriodId);
        }

        return $this->schoolPeriods()->where('id', '=', $schoolPeriodId)->exists();
    }

    public static function resolveSchoolPeriodIds(int $schoolId, ?array $requestedIds): array
    {
        $periodIds = SchoolPeriod::query()
            ->where('school_id', '=', $schoolId)
            ->orderedByAcademicPeriod()
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();

        if (count($periodIds) === 1) {
            return $periodIds;
        }

        $requestedIds = array_values(array_unique(array_map(intval(...), $requestedIds ?? [])));

        return $requestedIds;
    }

    public static function resolveDefaultActiveSchoolPeriodId(array $schoolPeriodIds): int
    {
        return (int) SchoolPeriod::query()
            ->whereIn('id', $schoolPeriodIds)
            ->orderedByAcademicPeriod()
            ->value('id');
    }

    public function syncSchoolPeriodMemberships(array $schoolPeriodIds): void
    {
        $this->schoolPeriods()->sync($schoolPeriodIds);
    }

    public function ensureActiveSchoolPeriodIsValid(): bool
    {
        if ($this->scope !== UserScope::SCHOOL) {
            return false;
        }

        $membershipIds = $this->schoolPeriods()->pluck('school_periods.id')->map(fn ($id): int => (int) $id)->all();

        if ($membershipIds === []) {
            return false;
        }

        if ($this->organization_id !== null && in_array((int) $this->organization_id, $membershipIds, true)) {
            return false;
        }

        $this->update([
            'organization_id' => self::resolveDefaultActiveSchoolPeriodId($membershipIds),
            'organization_type' => SchoolPeriod::class,
        ]);

        return true;
    }

    public function schoolPeriodFormData(): array
    {
        if ($this->scope !== UserScope::SCHOOL) {
            return [];
        }

        $this->loadMissing([
            'schoolPeriods:id,school_id,academic_period,name',
            'organization',
        ]);

        $schoolId = $this->organization instanceof SchoolPeriod
            ? $this->organization->school_id
            : $this->schoolPeriods->first()?->school_id;

        return [
            'school_id' => $schoolId,
            'school_period_ids' => $this->schoolPeriods->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
        ];
    }

    public function schoolPeriodMemberships(): array
    {
        if ($this->scope !== UserScope::SCHOOL) {
            return [];
        }

        $this->loadMissing(['schoolPeriods:id,school_id,academic_period,name']);

        return $this->schoolPeriods
            ->sortBy(fn (SchoolPeriod $period): int => $period->academic_period->isMorning() ? 0 : 1)
            ->values()
            ->map(fn (SchoolPeriod $period): array => [
                'id' => $period->id,
                'name' => $period->display_name,
                'academic_period' => $period->academic_period->toArray(),
            ])->all();
    }

    /**
     * Typed organizational context for the attached organization morph.
     */
    public function resolvedOrganization(): ?array
    {
        $this->loadMissing(match ($this->organization_type) {
            EducationServicesOffice::class, SchoolPeriod::class => ['organization.monitor'],
            default => ['organization'],
        });

        $organization = $this->organization;

        if ($organization === null) {
            return null;
        }

        $reference = static fn (Model $entity): array => [
            'id' => (int) $entity->getKey(),
            'name' => (string) $entity->getAttribute('name'),
        ];

        return match (true) {
            $this->scope === UserScope::WAREHOUSE && $organization instanceof Warehouse => [
                'type' => 'warehouse',
                'organization' => [
                    'warehouse' => $reference($organization),
                ],
            ],
            $this->scope === UserScope::EDUCATION_MONITOR && $organization instanceof EducationMonitor => [
                'type' => 'education_monitor',
                'organization' => [
                    'education_monitor' => $reference($organization),
                ],
            ],
            $this->scope === UserScope::EDUCATION_SERVICES_OFFICE && $organization instanceof EducationServicesOffice => [
                'type' => 'education_services_office',
                'organization' => [
                    'education_services_office' => $reference($organization),
                    'education_monitor' => $reference($organization->monitor),
                ],
            ],
            $this->scope === UserScope::SCHOOL && $organization instanceof SchoolPeriod => [
                'type' => 'school',
                'organization' => [
                    'school' => [
                        ...$reference($organization),
                        'school_id' => $organization->school_id,
                        'academic_period' => $organization->academic_period->toArray(),
                    ],
                    'education_monitor' => $reference($organization->monitor),
                ],
            ],
            default => null,
        };
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

    private function scopedToOrganization(Builder $query, int|string|null $organizationId, string $organizationClass, array $descendants = []): Builder
    {
        if ($organizationId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $query) use ($organizationId, $organizationClass, $descendants): void {
            $query
                ->where('organization_type', $organizationClass)
                ->where('organization_id', $organizationId);

            if ($descendants !== []) {
                $query->orWhereHasMorph('organization', array_keys($descendants), function (Builder $query, string $type) use ($organizationId, $descendants): void {
                    $query->where($descendants[$type], $organizationId);
                });
            }
        });
    }

    private function authenticatedOrganizationId(UserScope $scope): ?int
    {
        $user = auth($scope->guard())->user();

        if (! $user instanceof self || $user->scope !== $scope || ! $user->hasValidOrganizationContext()) {
            return null;
        }

        return $user->organization_id;
    }

    /*
     * End: Custom Functions
     */
}
