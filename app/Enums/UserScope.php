<?php

namespace App\Enums;

use App\Concerns\EnumUtilities;
use App\Support\Auth\DashboardAuth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

enum UserScope: string
{
    use EnumUtilities;

    case ADMINISTRATION = 'administration';
    case WAREHOUSE = 'warehouse';
    case EDUCATION_MONITOR = 'education_monitor';
    case EDUCATION_SERVICES_OFFICE = 'education_services_office';
    case SCHOOL = 'school';

    protected function getTranslationKey(): string
    {
        return 'user_scopes';
    }

    public function getAccessibleScopes(): Collection
    {
        $scopes = match ($this) {
            self::ADMINISTRATION => [
                self::ADMINISTRATION,
                self::WAREHOUSE,
                self::EDUCATION_MONITOR,
                self::EDUCATION_SERVICES_OFFICE,
                self::SCHOOL,
            ],
            self::WAREHOUSE => [
                self::WAREHOUSE,
            ],
            self::EDUCATION_MONITOR => [
                self::EDUCATION_MONITOR,
                self::EDUCATION_SERVICES_OFFICE,
                self::SCHOOL,
            ],
            self::EDUCATION_SERVICES_OFFICE => [
                self::EDUCATION_SERVICES_OFFICE,
                self::SCHOOL,
            ],
            self::SCHOOL => [
                self::SCHOOL,
            ],
        };

        return collect($scopes)->values();
    }

    public static function options(string $idKey = 'id', string $nameKey = 'name'): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect([]);
        }

        return $user->scope->getAccessibleScopes()->map(function (self $scope) use ($idKey, $nameKey): array {
            return $scope->toOption($idKey, $nameKey);
        });
    }

    public function getCreationLabel(): string
    {
        return __(sprintf(
            'app.enums.%s.create.%s',
            $this->getTranslationKey(),
            $this->value,
        ));
    }

    public function icon(): string
    {
        return match ($this) {
            self::ADMINISTRATION => 'UserRoundCogIcon',
            self::WAREHOUSE => 'WarehouseIcon',
            self::EDUCATION_MONITOR => 'LandmarkIcon',
            self::EDUCATION_SERVICES_OFFICE => 'BuildingIcon',
            self::SCHOOL => 'SchoolIcon',
        };
    }

    public function toCreationMenuItem(): array
    {
        return [
            'label' => $this->getCreationLabel(),
            'value' => $this->value,
            'icon' => $this->icon(),
        ];
    }

    public static function getCreationMenuItems(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect([]);
        }

        return $user->scope->getAccessibleScopes()->map(function (self $scope): array {
            return $scope->toCreationMenuItem();
        });
    }

    public function flags(): Collection
    {
        $user = Auth::user();

        if (! $user) {
            return collect([]);
        }

        $scopes = $user->scope->getAccessibleScopes();

        return $scopes->mapWithKeys(function (self $case): array {
            return [
                $case->name => $case === $this,
            ];
        });
    }

    public static function flagsFor(self $scope): Collection
    {
        return $scope->flags();
    }

    public function isAdministration(): bool
    {
        return $this === self::ADMINISTRATION;
    }

    public function isWarehouse(): bool
    {
        return $this === self::WAREHOUSE;
    }

    public function isEducationMonitor(): bool
    {
        return $this === self::EDUCATION_MONITOR;
    }

    public function isEducationServicesOffice(): bool
    {
        return $this === self::EDUCATION_SERVICES_OFFICE;
    }

    public function isSchool(): bool
    {
        return $this === self::SCHOOL;
    }

    public function guard(): string
    {
        return match ($this) {
            self::ADMINISTRATION => 'administration',
            self::WAREHOUSE => 'warehouse',
            self::EDUCATION_MONITOR => 'education_monitor',
            self::EDUCATION_SERVICES_OFFICE => 'education_services_office',
            self::SCHOOL => 'school',
        };
    }

    public function getDashboardAuth(): ?DashboardAuth
    {
        return DashboardAuth::fromScope($this);
    }
}
