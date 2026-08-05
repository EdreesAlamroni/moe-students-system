<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\State\Activated;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $monitor = $this->benghaziEducationMonitor();
        $office = EducationServicesOffice::query()
            ->whereBelongsTo($monitor, 'monitor')
            ->firstOrFail();
        $schoolPeriod = SchoolPeriod::query()->firstOrFail();

        foreach ($this->userDefinitions($monitor, $office, $schoolPeriod) as $attributes) {
            $this->createUser($attributes);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function userDefinitions(
        EducationMonitor $monitor,
        EducationServicesOffice $office,
        SchoolPeriod $schoolPeriod,
    ): array {
        return [
            $this->administratorUser(),
            $this->warehouseUser($monitor),
            $this->educationMonitorUser($monitor),
            $this->educationServicesOfficeUser($office),
            $this->schoolUser($schoolPeriod),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createUser(array $attributes): void
    {
        User::query()->updateOrCreate(
            ['username' => $attributes['username']],
            array_merge($this->defaultUserAttributes(), $attributes),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultUserAttributes(): array
    {
        return [
            'role' => UserRole::MANAGER,
            'state' => Activated::class,
            'request_state' => Approved::class,
            'must_change_password' => false,
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function administratorUser(): array
    {
        return [
            'name' => 'مدير النظام',
            'username' => 'administrator',
            'email' => 'info@example.com',
            'scope' => UserScope::ADMINISTRATION,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function warehouseUser(EducationMonitor $monitor): array
    {
        return [
            'name' => 'مُستخدم مخزن',
            'username' => 'warehouse',
            'email' => 'warehouse@example.com',
            'scope' => UserScope::WAREHOUSE,
            'organization_id' => $monitor->warehouse_id,
            'organization_type' => Warehouse::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function educationMonitorUser(EducationMonitor $monitor): array
    {
        return [
            'name' => 'مُستخدم مُراقبة',
            'username' => 'monitor',
            'email' => 'monitor@example.com',
            'scope' => UserScope::EDUCATION_MONITOR,
            'organization_id' => $monitor->id,
            'organization_type' => EducationMonitor::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function educationServicesOfficeUser(EducationServicesOffice $office): array
    {
        return [
            'name' => 'مُستخدم مكتب خدمات تعليمية',
            'username' => 'office',
            'email' => 'office@example.com',
            'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
            'organization_id' => $office->id,
            'organization_type' => EducationServicesOffice::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function schoolUser(SchoolPeriod $schoolPeriod): array
    {
        return [
            'name' => 'مُستخدم مدرسة',
            'username' => 'school',
            'email' => 'school@example.com',
            'scope' => UserScope::SCHOOL,
            'organization_id' => $schoolPeriod->id,
            'organization_type' => SchoolPeriod::class,
        ];
    }

    protected function benghaziEducationMonitor(): EducationMonitor
    {
        return EducationMonitor::query()
            ->whereRelation('municipal', 'name', '=', 'بنغازي')
            ->firstOrFail();
    }
}
