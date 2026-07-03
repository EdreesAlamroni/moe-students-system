<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\RequestState\Pending;
use App\ModelStates\User\State\Activated;
use App\ModelStates\User\State\Deactivated;

it('creates a user with default attributes', function () {
    $user = User::factory()->create();

    expect($user->scope)->toBe(UserScope::ADMINISTRATION)
        ->and($user->role)->toBe(UserRole::MANAGER)
        ->and($user->must_change_password)->toBeFalse()
        ->and($user->state)->toBeInstanceOf(Activated::class)
        ->and($user->request_state)->toBeInstanceOf(Approved::class)
        ->and($user->state->value())->toBe('activated')
        ->and($user->request_state->value())->toBe('approved');
});

it('creates a user with custom states', function () {
    $user = User::factory()
        ->withState(Deactivated::class)
        ->withRequestState(Pending::class)
        ->create();

    expect($user->state)->toBeInstanceOf(Deactivated::class)
        ->and($user->request_state)->toBeInstanceOf(Pending::class)
        ->and($user->state->value())->toBe('deactivated')
        ->and($user->request_state->value())->toBe('pending');
});

it('creates a user with custom scope and role', function () {
    $user = User::factory()
        ->withScope(UserScope::SCHOOL)
        ->withRole(UserRole::EMPLOYEE)
        ->create();

    expect($user->scope)->toBe(UserScope::SCHOOL)
        ->and($user->role)->toBe(UserRole::EMPLOYEE);
});

it('creates a user that must change password', function () {
    $user = User::factory()->withMustChangePassword()->create();

    expect($user->must_change_password)->toBeTrue();
});

it('creates a user without a required password change', function () {
    $user = User::factory()->withMustChangePassword(false)->create();

    expect($user->must_change_password)->toBeFalse();
});
