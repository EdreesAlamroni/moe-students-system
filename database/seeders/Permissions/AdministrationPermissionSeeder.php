<?php

namespace Database\Seeders\Permissions;

use App\Enums\UserScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdministrationPermissionSeeder extends Seeder
{
    private string $scope = UserScope::ADMINISTRATION->value;

    public function run(): void
    {
        $this->seedUser();
        $this->seedMunicipal();
        $this->seedAcademicYear();
        $this->seedGradeLevel();
        $this->seedSubject();
        $this->seedClassPeriod();
        $this->seedWarehouse();
        $this->seedEducationMonitor();
        $this->seedEducationServicesOffice();
        $this->seedSchool();
        $this->seedStudent();
        $this->seedSubjectClassification();
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

    private function seedMunicipal(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('municipal:view-any', $this->scope);
        $view = Permission::findOrCreate('municipal:view', $this->scope);
        $create = Permission::findOrCreate('municipal:create', $this->scope);
        $update = Permission::findOrCreate('municipal:update', $this->scope);
        $delete = Permission::findOrCreate('municipal:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('municipal:role:view', $this->scope);
        $createRole = Role::findOrCreate('municipal:role:create', $this->scope);
        $updateRole = Role::findOrCreate('municipal:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('municipal:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedAcademicYear(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('academic-year:view-any', $this->scope);
        $view = Permission::findOrCreate('academic-year:view', $this->scope);
        $create = Permission::findOrCreate('academic-year:create', $this->scope);
        $update = Permission::findOrCreate('academic-year:update', $this->scope);
        $delete = Permission::findOrCreate('academic-year:delete', $this->scope);
        $close = Permission::findOrCreate('academic-year:close', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('academic-year:role:view', $this->scope);
        $createRole = Role::findOrCreate('academic-year:role:create', $this->scope);
        $updateRole = Role::findOrCreate('academic-year:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('academic-year:role:delete', $this->scope);
        $closeRole = Role::findOrCreate('academic-year:role:close', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
        $closeRole->syncPermissions([$viewAny, $view, $close]);
    }

    private function seedGradeLevel(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('grade-level:view-any', $this->scope);
        $view = Permission::findOrCreate('grade-level:view', $this->scope);
        $create = Permission::findOrCreate('grade-level:create', $this->scope);
        $update = Permission::findOrCreate('grade-level:update', $this->scope);
        $delete = Permission::findOrCreate('grade-level:delete', $this->scope);
        $stateUpdate = Permission::findOrCreate('grade-level:state-update', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('grade-level:role:view', $this->scope);
        $createRole = Role::findOrCreate('grade-level:role:create', $this->scope);
        $updateRole = Role::findOrCreate('grade-level:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('grade-level:role:delete', $this->scope);
        $stateUpdateRole = Role::findOrCreate('grade-level:role:state-update', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
        $stateUpdateRole->syncPermissions([$viewAny, $view, $stateUpdate]);
    }

    private function seedSubject(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('subject:view-any', $this->scope);
        $view = Permission::findOrCreate('subject:view', $this->scope);
        $create = Permission::findOrCreate('subject:create', $this->scope);
        $update = Permission::findOrCreate('subject:update', $this->scope);
        $delete = Permission::findOrCreate('subject:delete', $this->scope);
        $stateUpdate = Permission::findOrCreate('subject:state-update', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('subject:role:view', $this->scope);
        $createRole = Role::findOrCreate('subject:role:create', $this->scope);
        $updateRole = Role::findOrCreate('subject:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('subject:role:delete', $this->scope);
        $stateUpdateRole = Role::findOrCreate('subject:role:state-update', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
        $stateUpdateRole->syncPermissions([$viewAny, $view, $stateUpdate]);
    }

    private function seedClassPeriod(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('class-period:view-any', $this->scope);
        $view = Permission::findOrCreate('class-period:view', $this->scope);
        $create = Permission::findOrCreate('class-period:create', $this->scope);
        $update = Permission::findOrCreate('class-period:update', $this->scope);
        $delete = Permission::findOrCreate('class-period:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('class-period:role:view', $this->scope);
        $createRole = Role::findOrCreate('class-period:role:create', $this->scope);
        $updateRole = Role::findOrCreate('class-period:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('class-period:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedWarehouse(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('warehouse:view-any', $this->scope);
        $view = Permission::findOrCreate('warehouse:view', $this->scope);
        $create = Permission::findOrCreate('warehouse:create', $this->scope);
        $update = Permission::findOrCreate('warehouse:update', $this->scope);
        $delete = Permission::findOrCreate('warehouse:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('warehouse:role:view', $this->scope);
        $createRole = Role::findOrCreate('warehouse:role:create', $this->scope);
        $updateRole = Role::findOrCreate('warehouse:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('warehouse:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedEducationMonitor(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('education-monitor:view-any', $this->scope);
        $view = Permission::findOrCreate('education-monitor:view', $this->scope);
        $create = Permission::findOrCreate('education-monitor:create', $this->scope);
        $update = Permission::findOrCreate('education-monitor:update', $this->scope);
        $delete = Permission::findOrCreate('education-monitor:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('education-monitor:role:view', $this->scope);
        $createRole = Role::findOrCreate('education-monitor:role:create', $this->scope);
        $updateRole = Role::findOrCreate('education-monitor:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('education-monitor:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedEducationServicesOffice(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('education-services-office:view-any', $this->scope);
        $view = Permission::findOrCreate('education-services-office:view', $this->scope);
        $create = Permission::findOrCreate('education-services-office:create', $this->scope);
        $update = Permission::findOrCreate('education-services-office:update', $this->scope);
        $delete = Permission::findOrCreate('education-services-office:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('education-services-office:role:view', $this->scope);
        $createRole = Role::findOrCreate('education-services-office:role:create', $this->scope);
        $updateRole = Role::findOrCreate('education-services-office:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('education-services-office:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedSchool(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('school:view-any', $this->scope);
        $view = Permission::findOrCreate('school:view', $this->scope);
        $create = Permission::findOrCreate('school:create', $this->scope);
        $update = Permission::findOrCreate('school:update', $this->scope);
        $delete = Permission::findOrCreate('school:delete', $this->scope);
        $addGradeLevel = Permission::findOrCreate('school:add-grade-level', $this->scope);
        $removeGradeLevel = Permission::findOrCreate('school:remove-grade-level', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('school:role:view', $this->scope);
        $createRole = Role::findOrCreate('school:role:create', $this->scope);
        $updateRole = Role::findOrCreate('school:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('school:role:delete', $this->scope);
        $addGradeLevelRole = Role::findOrCreate('school:role:add-grade-level', $this->scope);
        $removeGradeLevelRole = Role::findOrCreate('school:role:remove-grade-level', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
        $addGradeLevelRole->syncPermissions([$viewAny, $view, $addGradeLevel]);
        $removeGradeLevelRole->syncPermissions([$viewAny, $view, $removeGradeLevel]);
    }

    private function seedStudent(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('student:view-any', $this->scope);
        $view = Permission::findOrCreate('student:view', $this->scope);
        $create = Permission::findOrCreate('student:create', $this->scope);
        $update = Permission::findOrCreate('student:update', $this->scope);
        $delete = Permission::findOrCreate('student:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('student:role:view', $this->scope);
        $createRole = Role::findOrCreate('student:role:create', $this->scope);
        $updateRole = Role::findOrCreate('student:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('student:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedSubjectClassification(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('subject-classification:view-any', $this->scope);
        $view = Permission::findOrCreate('subject-classification:view', $this->scope);
        $create = Permission::findOrCreate('subject-classification:create', $this->scope);
        $update = Permission::findOrCreate('subject-classification:update', $this->scope);
        $delete = Permission::findOrCreate('subject-classification:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('subject-classification:role:view', $this->scope);
        $createRole = Role::findOrCreate('subject-classification:role:create', $this->scope);
        $updateRole = Role::findOrCreate('subject-classification:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('subject-classification:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedReports(): void
    {
        $groups = [
            'education-monitor',
            'education-services-office',
            'school',
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
