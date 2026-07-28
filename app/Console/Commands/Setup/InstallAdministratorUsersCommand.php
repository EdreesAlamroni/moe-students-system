<?php

namespace App\Console\Commands\Setup;

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\State\Activated;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('setup:install-administrators')]
#[Description('Create administrator user accounts')]
class InstallAdministratorUsersCommand extends Command
{
    private const DEFAULT_PASSWORD = 'password';

    public function handle(): int
    {
        if (User::query()->exists()) {
            $this->components->info('Administrator users already exist. Skipping.');

            return self::SUCCESS;
        }

        $usersToCreate = $this->promptForCount();

        if ($usersToCreate === 0) {
            $this->components->warn('No administrator users were created.');

            return self::SUCCESS;
        }

        $info = sprintf(
            'Provide each user\'s name, username, and password (leave password empty to use "%s").',
            self::DEFAULT_PASSWORD,
        );
        $this->components->info($info);

        for ($index = 0; $index < $usersToCreate; $index++) {
            $this->newLine();
            $this->components->twoColumnDetail(
                'Administrator user',
                sprintf('%d of %d', $index + 1, $usersToCreate),
            );

            $name = $this->promptForRequiredText('Name');
            $username = $this->promptForUniqueUsername();
            $password = $this->promptForPassword($username);

            $attributes = $this->attributes($name, $username, $password);

            User::create($attributes);
        }

        $this->newLine();
        $this->components->info(sprintf('Created %d administrator user(s).', $usersToCreate));

        return self::SUCCESS;
    }

    private function promptForCount(): int
    {
        while (true) {
            $input = $this->components->ask('How many administrator users do you want to create?', '1');
            $value = is_string($input) ? trim($input) : '';

            if ($value === '') {
                $value = '1';
            }

            if (ctype_digit($value)) {
                return (int) $value;
            }

            $this->components->error('Please enter a valid non-negative number.');
        }
    }

    private function promptForRequiredText(string $label): string
    {
        while (true) {
            $value = $this->components->ask($label);
            $value = is_string($value) ? trim($value) : '';

            if ($value !== '') {
                return $value;
            }

            $error = sprintf('%s is required.', $label);
            $this->components->error($error);
        }
    }

    private function promptForUniqueUsername(): string
    {
        while (true) {
            $username = $this->promptForRequiredText('Username');

            if (! User::query()->where('username', $username)->exists()) {
                return $username;
            }

            $this->components->error('This username already exists. Please choose a different one.');
        }
    }

    private function promptForPassword(string $username): string
    {
        $question = sprintf(
            'Password for %s (leave empty for "%s")',
            $username,
            self::DEFAULT_PASSWORD,
        );

        $password = $this->secret($question);

        $value = is_string($password) ? trim($password) : '';

        return $value !== '' ? $value : self::DEFAULT_PASSWORD;
    }

    private function attributes(string $name, string $username, string $password): array
    {
        return [
            'organization_id' => null,
            'organization_type' => null,
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'scope' => UserScope::ADMINISTRATION,
            'role' => UserRole::MANAGER,
            'state' => Activated::class,
            'request_state' => Approved::class,
            'must_change_password' => true,
        ];
    }
}
