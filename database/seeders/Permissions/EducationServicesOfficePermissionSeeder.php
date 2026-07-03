<?php

namespace Database\Seeders\Permissions;

use App\Enums\UserScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EducationServicesOfficePermissionSeeder extends Seeder
{
    private string $scope = UserScope::EDUCATION_SERVICES_OFFICE->value;

    public function run(): void
    {
        $this->seedUser();
        $this->seedSchool();
        $this->seedStudent();
        $this->seedReports();
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

    private function seedSchool(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('school:view-any', $this->scope);
        $view = Permission::findOrCreate('school:view', $this->scope);
        $addGradeLevel = Permission::findOrCreate('school:add-grade-level', $this->scope);
        $removeGradeLevel = Permission::findOrCreate('school:remove-grade-level', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('school:role:view', $this->scope);
        $addGradeLevelRole = Role::findOrCreate('school:role:add-grade-level', $this->scope);
        $removeGradeLevelRole = Role::findOrCreate('school:role:remove-grade-level', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $addGradeLevelRole->syncPermissions([$viewAny, $view, $addGradeLevel]);
        $removeGradeLevelRole->syncPermissions([$viewAny, $view, $removeGradeLevel]);
    }

    private function seedStudent(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('student:view-any', $this->scope);
        $view = Permission::findOrCreate('student:view', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('student:role:view', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
    }

    protected function seedReports(): void
    {
        $groups = [
            'school',
            'student-count-by-grade-level',
        ];

        foreach ($groups as $group) {
            // Permissions
            $view = Permission::findOrCreate("report:{$group}:view", $this->scope);
            $print = Permission::findOrCreate("report:{$group}:print", $this->scope);
            $export = Permission::findOrCreate("report:{$group}:export", $this->scope);

            // Roles
            $viewRole = Role::findOrCreate("report:role:{$group}:view", $this->scope);
            $printRole = Role::findOrCreate("report:role:{$group}:print", $this->scope);
            $exportRole = Role::findOrCreate("report:role:{$group}:export", $this->scope);

            // Sync permissions with roles
            $viewRole->syncPermissions([$view]);
            $printRole->syncPermissions([$view, $print]);
            $exportRole->syncPermissions([$view, $export]);
        }
    }
}
