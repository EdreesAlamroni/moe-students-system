<?php

use App\Enums\UserScope;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('forgot password page can be rendered', function () {
    $this->get(route('administration.password.request'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $page->component('auth/forgot-password');
        });
});

test('reset link can be requested with a valid email', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('administration.password.email'), [
        'email' => $user->email,
    ])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('reset link request requires a valid email', function () {
    $this->post(route('administration.password.email'), [
        'email' => 'not-an-email',
    ])->assertSessionHasErrors('email');
});

test('reset password page can be rendered', function () {
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);

    $this->get(route('administration.password.reset', ['token' => $token, 'email' => $user->email]))
        ->assertOk()
        ->assertInertia(function ($page) {
            $page->component('auth/reset-password');
        });
});

test('password can be reset with valid token', function () {
    $user = User::factory()->create();
    $oldHash = $user->password;
    $token = Password::broker()->createToken($user);

    $this->post(route('administration.password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect(route('administration.login'));

    $user->refresh();

    expect(Hash::check('new-password-123', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeFalse()
        ->and($user->password)->not->toBe($oldHash);
});

test('password reset fails when user scope does not match the dashboard', function () {
    $user = User::factory()->withScope(UserScope::WAREHOUSE)->create();
    $oldHash = $user->password;
    $token = Password::broker()->createToken($user);

    $this->post(route('administration.password.store'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertSessionHasErrors('email');

    expect($user->refresh()->password)->toBe($oldHash);
});

test('password reset routes are unavailable for dashboards without password reset support', function () {
    $this->get('/school/forgot-password')->assertNotFound();
    $this->post('/school/forgot-password')->assertNotFound();
    $this->get('/school/reset-password/token')->assertNotFound();
});
