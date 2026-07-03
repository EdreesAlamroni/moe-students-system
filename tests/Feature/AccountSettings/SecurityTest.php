<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

test('security page is displayed when password is confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('administration.account-settings.security.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('account-settings/security')
            ->has('passwordRules')
            ->has('routes.update'),
        );
});

test('security page requires password confirmation', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.account-settings.security.edit'))
        ->assertRedirect(route('administration.password.confirm'));
});

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'administration')
        ->from(route('administration.account-settings.security.edit'))
        ->put(route('administration.account-settings.password.update'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('administration.account-settings.security.edit'));

    expect(Hash::check('new-password-123', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'administration')
        ->from(route('administration.account-settings.security.edit'))
        ->put(route('administration.account-settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('administration.account-settings.security.edit'));
});
