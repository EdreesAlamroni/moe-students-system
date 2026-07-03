<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.account-settings.profile.edit'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('account-settings/profile'));
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'administration')
        ->patch(route('administration.account-settings.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('administration.account-settings.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com');
});

test('profile information can be updated when the email address is unchanged', function () {
    $user = User::factory()->create();
    $email = $user->email;

    $response = $this->actingAs($user, 'administration')
        ->patch(route('administration.account-settings.profile.update'), [
            'name' => 'Test User',
            'email' => $email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('administration.account-settings.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User')
        ->and($user->email)->toBe($email);
});
