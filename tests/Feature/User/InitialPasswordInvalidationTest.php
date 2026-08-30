<?php

use App\Actions\Auth\ChangeUserPassword;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/users', 'GET'));
});

test('changing password via ChangeUserPassword clears initial password', function () {
    $user = User::factory()->create([
        'must_change_password' => true,
    ]);

    $user->forceFill(['initial_password' => '48291037'])->save();

    app(ChangeUserPassword::class)->execute($user, 'new-password-1');

    $user->refresh();

    expect($user->hasInitialPassword())->toBeFalse()
        ->and(Hash::check('new-password-1', $user->password))->toBeTrue();
});

test('updating password on user model clears initial password', function () {
    $user = User::factory()->create();

    $user->forceFill(['initial_password' => '48291037'])->save();

    $user->update(['password' => 'another-password-1']);

    $user->refresh();

    expect($user->hasInitialPassword())->toBeFalse()
        ->and(Hash::check('another-password-1', $user->password))->toBeTrue();
});

test('administration password reset clears initial password', function () {
    $admin = User::factory()->create();
    Permission::findOrCreate('user:update', UserScope::ADMINISTRATION->value);
    $admin->givePermissionTo('user:update');

    $target = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
    ]);
    $target->forceFill(['initial_password' => '48291037'])->save();

    $this->actingAs($admin, 'administration')
        ->put(route('administration.users.password.update', ['user' => $target]), [
            'password' => 'admin-reset-password',
            'password_confirmation' => 'admin-reset-password',
        ])
        ->assertRedirect();

    $target->refresh();

    expect($target->hasInitialPassword())->toBeFalse()
        ->and(Hash::check('admin-reset-password', $target->password))->toBeTrue();
});

test('account settings password update clears initial password', function () {
    PolicyRegistrar::register(Request::create('/administration/account-settings/security', 'GET'));

    $user = User::factory()->create([
        'scope' => UserScope::ADMINISTRATION,
    ]);
    $user->forceFill(['initial_password' => '48291037'])->save();

    $this->actingAs($user, 'administration')
        ->from(route('administration.account-settings.security.edit'))
        ->put(route('administration.account-settings.password.update'), [
            'current_password' => 'password',
            'password' => 'self-service-password',
            'password_confirmation' => 'self-service-password',
        ])
        ->assertRedirect();

    $user->refresh();

    expect($user->hasInitialPassword())->toBeFalse()
        ->and(Hash::check('self-service-password', $user->password))->toBeTrue();
});
