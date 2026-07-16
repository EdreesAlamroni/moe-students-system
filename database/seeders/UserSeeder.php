<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $monitor = $this->benghaziEducationMonitor();
        $office = $monitor->offices->firstOrFail();
        $school = School::query()->firstOrFail();

        foreach ($this->userDefinitions($monitor, $office, $school) as $attributes) {
            $this->createUser($attributes);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function userDefinitions(
        EducationMonitor $monitor,
        EducationServicesOffice $office,
        School $school,
    ): array {
        return [
            $this->administratorUser(),
            $this->warehouseUser($monitor),
            $this->educationMonitorUser($monitor),
            $this->educationServicesOfficeUser($office),
            $this->schoolUser($school),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function createUser(array $attributes): void
    {
        User::factory()->create(array_merge($this->defaultUserAttributes(), $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultUserAttributes(): array
    {
        return [
            'role' => UserRole::MANAGER,
            'request_state' => Approved::class,
            'must_change_password' => false,
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
    protected function schoolUser(School $school): array
    {
        return [
            'name' => 'مُستخدم مدرسة',
            'username' => 'school',
            'email' => 'school@example.com',
            'scope' => UserScope::SCHOOL,
            'organization_id' => $school->id,
            'organization_type' => School::class,
        ];
    }

    protected function benghaziEducationMonitor(): EducationMonitor
    {
        return EducationMonitor::query()
            ->whereRelation('municipal', 'name', '=', 'بنغازي')
            ->with('offices')
            ->firstOrFail();
    }
}
