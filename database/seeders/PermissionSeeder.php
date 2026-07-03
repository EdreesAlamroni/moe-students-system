<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            Permissions\AdministrationPermissionSeeder::class,
            Permissions\WarehousePermissionSeeder::class,
            Permissions\EducationMonitorPermissionSeeder::class,
            Permissions\EducationServicesOfficePermissionSeeder::class,
            Permissions\SchoolPermissionSeeder::class,
        ]);
    }
}
