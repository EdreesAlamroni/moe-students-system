<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'مدير النظام',
            'username' => 'administrator',
            'email' => 'info@example.com',
            'scope' => UserScope::ADMINISTRATION,
            'role' => UserRole::MANAGER,
        ]);

        User::factory()->create([
            'name' => 'مدير المخزن',
            'username' => 'warehouse',
            'email' => 'warehouse@example.com',
            'scope' => UserScope::WAREHOUSE,
            'role' => UserRole::MANAGER,
        ]);

        User::factory()->create([
            'name' => 'مدير المُراقبة',
            'username' => 'monitor',
            'email' => 'monitor@example.com',
            'scope' => UserScope::EDUCATION_MONITOR,
            'role' => UserRole::MANAGER,
        ]);

        User::factory()->create([
            'name' => 'مدير مكتب الخدمات التعليمية',
            'username' => 'office',
            'email' => 'office@example.com',
            'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
            'role' => UserRole::MANAGER,
        ]);

        User::factory()->create([
            'name' => 'مدير المدرسة',
            'username' => 'school',
            'email' => 'school@example.com',
            'scope' => UserScope::SCHOOL,
            'role' => UserRole::MANAGER,
        ]);
    }
}
