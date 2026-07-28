<?php

namespace App\Console\Commands\Setup;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('setup:install')]
#[Description('Perform the complete initial application setup')]
class FirstInstallCommand extends Command
{
    public function handle(): int
    {
        $this->components->info('Starting the first-install setup...');

        $this->newLine();

        foreach ($this->steps() as $step) {
            $exitCode = $this->call($step);

            if ($exitCode !== self::SUCCESS) {
                $this->newLine();

                $error = sprintf('Setup failed at step "%s".', class_basename($step));
                $this->components->error($error);

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->components->success('First-install setup completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @return list<class-string<Command>>
     */
    private function steps(): array
    {
        return [
            InstallNationalitiesCommand::class,
            InstallMunicipalsCommand::class,
            InstallAcademicYearsCommand::class,
            InstallGradeLevelsCommand::class,
            InstallSubjectsCommand::class,
            InstallClassPeriodsCommand::class,
            InstallAdministratorUsersCommand::class,
            InstallRolesAndPermissionsCommand::class,
        ];
    }
}
