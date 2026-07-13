<?php

namespace Database\Seeders\Permissions;

use App\Enums\UserScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EducationMonitorPermissionSeeder extends Seeder
{
    private string $scope = UserScope::EDUCATION_MONITOR->value;

    public function run(): void
    {
        $this->seedUser();
        $this->seedEducationServicesOffice();
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
        $resetClassroomDistribution = Permission::findOrCreate('school:reset-classroom-distribution', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('school:role:view', $this->scope);
        $createRole = Role::findOrCreate('school:role:create', $this->scope);
        $updateRole = Role::findOrCreate('school:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('school:role:delete', $this->scope);
        $addGradeLevelRole = Role::findOrCreate('school:role:add-grade-level', $this->scope);
        $removeGradeLevelRole = Role::findOrCreate('school:role:remove-grade-level', $this->scope);
        $resetClassroomDistributionRole = Role::findOrCreate('school:role:reset-classroom-distribution', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
        $addGradeLevelRole->syncPermissions([$viewAny, $view, $addGradeLevel]);
        $removeGradeLevelRole->syncPermissions([$viewAny, $view, $removeGradeLevel]);
        $resetClassroomDistributionRole->syncPermissions([$viewAny, $view, $resetClassroomDistribution]);
    }

    private function seedStudent(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('student:view-any', $this->scope);
        $view = Permission::findOrCreate('student:view', $this->scope);

        // Transfer permissions
        $addTransferredStudent = Permission::findOrCreate('student:add-transferred-student', $this->scope);
        $transferStudentOut = Permission::findOrCreate('student:transfer-student-out-of-monitor', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('student:role:view', $this->scope);

        // Transfer roles
        $addTransferredStudentRole = Role::findOrCreate('student:role:add-transferred-student', $this->scope);
        $transferStudentOutRole = Role::findOrCreate('student:role:transfer-student-out-of-monitor', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);

        // Sync transfer permissions with roles
        $addTransferredStudentRole->syncPermissions([$viewAny, $view, $addTransferredStudent]);
        $transferStudentOutRole->syncPermissions([$viewAny, $view, $transferStudentOut]);
    }

    protected function seedReports(): void
    {
        $groups = [
            'education-services-office',
            'school',
            'student-count-by-grade-level',
        ];

        foreach ($groups as $group) {
            // Permissions
            $view = Permission::findOrCreate("report:{$group}:view", $this->scope);
            $print = Permission::findOrCreate("report:{$group}:print", $this->scope);

            // Roles
            $viewRole = Role::findOrCreate("report:role:{$group}:view", $this->scope);
            $printRole = Role::findOrCreate("report:role:{$group}:print", $this->scope);

            // Sync permissions with roles
            $viewRole->syncPermissions([$view]);
            $printRole->syncPermissions([$view, $print]);
        }
    }
}
