<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

#[Signature('seed:permissions')]
#[Description('Seed the roles and permissions')]
class SeedRolesAndPermissions extends Command
{
    public function handle()
    {
        app(PermissionSeeder::class)->run();

        User::query()->onlyManagers()->each(function (User $user) {
            $roles = Role::query()
                ->where('guard_name', '=', $user->scope->value)
                ->oldest()
                ->pluck('name')
                ->all();

            $user->syncRoles($roles);
        });

        $this->newLine();

        $this->components->info('Roles and permissions seeded successfully.');

        return Command::SUCCESS;
    }
}
