<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        if (! app()->isLocal()) {
            $this->command->error('Seeding is only allowed in the local environment.');
            $this->command->newLine();

            return;
        }

        $this->call([
            NationalitySeeder::class,
            MunicipalSeeder::class,
            AcademicYearSeeder::class,
            GradeLevelSeeder::class,
            SubjectSeeder::class,
            ClassPeriodSeeder::class,
            WarehouseSeeder::class,
            EducationMonitorSeeder::class,
            EducationServicesOfficeSeeder::class,
            SchoolSeeder::class,
            ClassroomSeeder::class,
            StudentSeeder::class,
            UserSeeder::class,
        ]);

        Artisan::call('seed:permissions');
    }
}
