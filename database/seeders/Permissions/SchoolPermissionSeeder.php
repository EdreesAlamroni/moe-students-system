<?php

namespace Database\Seeders\Permissions;

use App\Enums\UserScope;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SchoolPermissionSeeder extends Seeder
{
    private string $scope = UserScope::SCHOOL->value;

    public function run(): void
    {
        $this->seedGradeLevel();
        $this->seedClassroom();
        $this->seedClassSchedule();
        $this->seedStudent();
        $this->seedClassroomDistribution();
        $this->seedBookDistribution();
        $this->seedSchoolStaff();
        $this->seedUser();
        $this->seedReports();
    }

    private function seedGradeLevel(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('grade-level:view-any', $this->scope);
        $view = Permission::findOrCreate('grade-level:view', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('grade-level:role:view', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
    }

    private function seedClassroom(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('classroom:view-any', $this->scope);
        $view = Permission::findOrCreate('classroom:view', $this->scope);
        $create = Permission::findOrCreate('classroom:create', $this->scope);
        $update = Permission::findOrCreate('classroom:update', $this->scope);
        $delete = Permission::findOrCreate('classroom:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('classroom:role:view', $this->scope);
        $createRole = Role::findOrCreate('classroom:role:create', $this->scope);
        $updateRole = Role::findOrCreate('classroom:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('classroom:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
    }

    private function seedClassSchedule(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('class-schedule:view-any', $this->scope);
        $view = Permission::findOrCreate('class-schedule:view', $this->scope);
        $update = Permission::findOrCreate('class-schedule:update', $this->scope);
        $print = Permission::findOrCreate('class-schedule:print', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('class-schedule:role:view', $this->scope);
        $updateRole = Role::findOrCreate('class-schedule:role:update', $this->scope);
        $printRole = Role::findOrCreate('class-schedule:role:print', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $printRole->syncPermissions([$viewAny, $view, $print]);
    }

    private function seedStudent(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('student:view-any', $this->scope);
        $view = Permission::findOrCreate('student:view', $this->scope);
        $create = Permission::findOrCreate('student:create', $this->scope);
        $update = Permission::findOrCreate('student:update', $this->scope);
        $delete = Permission::findOrCreate('student:delete', $this->scope);

        // Transfer permissions
        $addTransferredStudent = Permission::findOrCreate('student:add-transferred-student', $this->scope);
        $transferStudentOut = Permission::findOrCreate('student:transfer-student-out-of-school', $this->scope);

        // Enrollment permissions
        $enrollInGradeLevel = Permission::findOrCreate('student:enroll-in-grade-level', $this->scope);
        $enrollInClassroom = Permission::findOrCreate('student:enroll-in-classroom', $this->scope);

        // Psychosocial card permissions
        $viewPsychosocialCard = Permission::findOrCreate('student:view-psychosocial-card', $this->scope);
        $updatePsychosocialCard = Permission::findOrCreate('student:update-psychosocial-card', $this->scope);
        $printPsychosocialCard = Permission::findOrCreate('student:print-psychosocial-card', $this->scope);

        // Academic record permissions
        $viewAcademicRecord = Permission::findOrCreate('student:view-academic-record', $this->scope);
        $createAcademicRecord = Permission::findOrCreate('student:create-academic-record', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('student:role:view', $this->scope);
        $createRole = Role::findOrCreate('student:role:create', $this->scope);
        $updateRole = Role::findOrCreate('student:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('student:role:delete', $this->scope);

        // Transfer roles
        $addTransferredStudentRole = Role::findOrCreate('student:role:add-transferred-student', $this->scope);
        $transferStudentOutRole = Role::findOrCreate('student:role:transfer-student-out-of-school', $this->scope);

        // Enrollment roles
        $enrollInGradeLevelRole = Role::findOrCreate('student:role:enroll-in-grade-level', $this->scope);
        $enrollInClassroomRole = Role::findOrCreate('student:role:enroll-in-classroom', $this->scope);

        // Psychosocial card roles
        $viewPsychosocialCardRole = Role::findOrCreate('student:role:view-psychosocial-card', $this->scope);
        $updatePsychosocialCardRole = Role::findOrCreate('student:role:update-psychosocial-card', $this->scope);
        $printPsychosocialCardRole = Role::findOrCreate('student:role:print-psychosocial-card', $this->scope);

        // Academic record roles
        $viewAcademicRecordRole = Role::findOrCreate('student:role:view-academic-record', $this->scope);
        $createAcademicRecordRole = Role::findOrCreate('student:role:create-academic-record', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);

        // Sync transfer permissions with roles
        $addTransferredStudentRole->syncPermissions([$viewAny, $view, $addTransferredStudent]);
        $transferStudentOutRole->syncPermissions([$viewAny, $view, $transferStudentOut]);

        // Sync enrollment permissions with roles
        $enrollInGradeLevelRole->syncPermissions([$viewAny, $view, $enrollInGradeLevel]);
        $enrollInClassroomRole->syncPermissions([$viewAny, $view, $enrollInClassroom]);

        // Sync psychosocial card permissions with roles
        $viewPsychosocialCardRole->syncPermissions([$viewAny, $view, $viewPsychosocialCard]);
        $updatePsychosocialCardRole->syncPermissions([$viewAny, $view, $viewPsychosocialCard, $updatePsychosocialCard]);
        $printPsychosocialCardRole->syncPermissions([$viewAny, $view, $viewPsychosocialCard, $printPsychosocialCard]);

        // Sync academic record permissions with roles
        $viewAcademicRecordRole->syncPermissions([$viewAny, $view, $viewAcademicRecord]);
        $createAcademicRecordRole->syncPermissions([$viewAny, $view, $viewAcademicRecord, $createAcademicRecord]);
    }

    private function seedClassroomDistribution(): void
    {
        // Permissions
        $view = Permission::findOrCreate('classroom-distribution:view', $this->scope);
        $distribute = Permission::findOrCreate('classroom-distribution:distribute', $this->scope);
        $finalize = Permission::findOrCreate('classroom-distribution:finalize', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('classroom-distribution:role:view', $this->scope);
        $distributeRole = Role::findOrCreate('classroom-distribution:role:distribute', $this->scope);
        $finalizeRole = Role::findOrCreate('classroom-distribution:role:finalize', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$view]);
        $distributeRole->syncPermissions([$view, $distribute]);
        $finalizeRole->syncPermissions([$view, $finalize]);
    }

    private function seedBookDistribution(): void
    {
        // Permissions
        $view = Permission::findOrCreate('book-distribution:view', $this->scope);
        $distribute = Permission::findOrCreate('book-distribution:distribute', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('book-distribution:role:view', $this->scope);
        $distributeRole = Role::findOrCreate('book-distribution:role:distribute', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$view]);
        $distributeRole->syncPermissions([$view, $distribute]);
    }

    private function seedSchoolStaff(): void
    {
        // Permissions
        $viewAny = Permission::findOrCreate('school-staff:view-any', $this->scope);
        $view = Permission::findOrCreate('school-staff:view', $this->scope);
        $create = Permission::findOrCreate('school-staff:create', $this->scope);
        $update = Permission::findOrCreate('school-staff:update', $this->scope);
        $delete = Permission::findOrCreate('school-staff:delete', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('school-staff:role:view', $this->scope);
        $createRole = Role::findOrCreate('school-staff:role:create', $this->scope);
        $updateRole = Role::findOrCreate('school-staff:role:update', $this->scope);
        $deleteRole = Role::findOrCreate('school-staff:role:delete', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$viewAny, $view]);
        $createRole->syncPermissions([$viewAny, $create]);
        $updateRole->syncPermissions([$viewAny, $view, $update]);
        $deleteRole->syncPermissions([$viewAny, $view, $delete]);
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

    protected function seedReports(): void
    {
        $groups = [
            'student-by-grade-level',
            'student-by-classroom',
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

        // Permissions
        $view = Permission::findOrCreate('report:attendance:view', $this->scope);
        $print = Permission::findOrCreate('report:attendance:print', $this->scope);

        // Roles
        $viewRole = Role::findOrCreate('report:role:attendance:view', $this->scope);
        $printRole = Role::findOrCreate('report:role:attendance:print', $this->scope);

        // Sync permissions with roles
        $viewRole->syncPermissions([$view]);
        $printRole->syncPermissions([$view, $print]);
    }
}
