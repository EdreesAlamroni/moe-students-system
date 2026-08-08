<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\State\Activated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

test('first install command creates administrator users from interactive input', function () {
    $this->artisan('setup:install')
        ->expectsQuestion('Start year for the active academic year (e.g. 2026 creates 2027/2026)', '2025')
        ->expectsQuestion('How many administrator users do you want to create?', '2')
        ->expectsQuestion('Name', 'Primary Admin')
        ->expectsQuestion('Username', 'primary-admin')
        ->expectsQuestion('Password for primary-admin (leave empty for "password")', 'first-password')
        ->expectsQuestion('Name', 'Secondary Admin')
        ->expectsQuestion('Username', 'secondary-admin')
        ->expectsQuestion('Password for secondary-admin (leave empty for "password")', '')
        ->assertExitCode(Command::SUCCESS);

    $primaryAdmin = User::query()->where('username', 'primary-admin')->first();
    $secondaryAdmin = User::query()->where('username', 'secondary-admin')->first();

    expect($primaryAdmin)->not->toBeNull()
        ->and($secondaryAdmin)->not->toBeNull()
        ->and(User::query()->count())->toBe(2);

    expect($primaryAdmin->name)->toBe('Primary Admin')
        ->and($primaryAdmin->scope)->toBe(UserScope::ADMINISTRATION)
        ->and($primaryAdmin->role)->toBe(UserRole::MANAGER)
        ->and($primaryAdmin->state->equals(Activated::class))->toBeTrue()
        ->and($primaryAdmin->request_state->equals(Approved::class))->toBeTrue()
        ->and($primaryAdmin->must_change_password)->toBeTrue()
        ->and($primaryAdmin->organization_id)->toBeNull()
        ->and($primaryAdmin->organization_type)->toBeNull()
        ->and(Hash::check('first-password', $primaryAdmin->password))->toBeTrue();

    expect($secondaryAdmin->name)->toBe('Secondary Admin')
        ->and($secondaryAdmin->scope)->toBe(UserScope::ADMINISTRATION)
        ->and($secondaryAdmin->role)->toBe(UserRole::MANAGER)
        ->and($secondaryAdmin->state->equals(Activated::class))->toBeTrue()
        ->and($secondaryAdmin->request_state->equals(Approved::class))->toBeTrue()
        ->and($secondaryAdmin->must_change_password)->toBeTrue()
        ->and($secondaryAdmin->organization_id)->toBeNull()
        ->and($secondaryAdmin->organization_type)->toBeNull()
        ->and(Hash::check('password', $secondaryAdmin->password))->toBeTrue();
});
