<?php

namespace App\Console\Commands\Setup;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('setup:install-permissions')]
#[Description('Install application roles and permissions')]
class InstallRolesAndPermissionsCommand extends Command
{
    public function handle(): int
    {
        return $this->call('seed:permissions');
    }
}
