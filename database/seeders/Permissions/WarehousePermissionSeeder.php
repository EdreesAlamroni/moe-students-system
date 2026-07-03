<?php

namespace Database\Seeders\Permissions;

use App\Enums\UserScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WarehousePermissionSeeder extends Seeder
{
    private string $scope = UserScope::WAREHOUSE->value;

    public function run(): void
    {
        $this->seedUser();
        $this->seedEducationMonitor();
        $this->seedSchool();
        $this->seedBookDistribution();
    }

    private function seedUser(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('user:view-any', $this->scope);
        $view = Permission::findOrCreate('user:view', $this->scope);
        $create = Permission::findOrCreate('user:create', $this->scope);
        $update = Permission::findOrCreate('user:update', $this->scope);
        $delete = Permission::findOrCreate('user:delete', $this->scope);
        $stateUpdate = Permission::findOrCreate('user:state-update', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('user:role:view', $this->scope);
        $createRole = Role::findOrCreate('user:role:create', $this->scope);
        $updateRole = Role::findOrCreate('user:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('user:role:delete', $this->scope);
        $stateUpdateRole = Role::findOrCreate('user:role:state-update', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
        $stateUpdateRole->syncPermissions([$viewAny, $view, $stateUpdate]);
    }

    private function seedEducationMonitor(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('education-monitor:view-any', $this->scope);
        $view = Permission::findOrCreate('education-monitor:view', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('education-monitor:role:view', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
    }

    private function seedSchool(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('school:view-any', $this->scope);
        $view = Permission::findOrCreate('school:view', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('school:role:view', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
    }

    private function seedBookDistribution(): void
    {
        // Permissions
        $view = Permission::findOrCreate('book-distribution:view', $this->scope);
        $distribute = Permission::findOrCreate('book-distribution:distribute', $this->scope);
        $viewStatistics = Permission::findOrCreate('book-distribution:view-statistics', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('book-distribution:role:view', $this->scope);
        $distributeRole = Role::findOrCreate('book-distribution:role:distribute', $this->scope);
        $viewStatisticsRole = Role::findOrCreate('book-distribution:role:view-statistics', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$view]);
        $distributeRole->syncPermissions([$view, $distribute]);
        $viewStatisticsRole->syncPermissions([$viewStatistics]);
    }
}
