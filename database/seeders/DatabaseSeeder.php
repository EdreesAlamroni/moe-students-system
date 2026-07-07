<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NationalitySeeder::class,
            MunicipalSeeder::class,
            AcademicYearSeeder::class,
            GradeLevelSeeder::class,
            UserSeeder::class,
        ]);

        Artisan::call('seed:permissions');
    }
}
