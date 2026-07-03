<?php

use App\Enums\AuthPage;
use App\Enums\UserScope;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use App\ModelStates\User\State\Deactivated;
use App\Support\Auth\DashboardAuth;

test('panel login screens can be rendered', function (DashboardAuth $dashboard) {
    $response = $this->get(route($dashboard->loginRouteName()));

    $response->assertOk();

    $heading = $dashboard->authPageHeading(AuthPage::LOGIN);

    $response->assertInertia(fn ($page) => $page
        ->component('auth/login')
        ->has('dashboard')
        ->has('routes')
        ->where('heading.title', $heading['title'])
        ->where('heading.description', $heading['description'])
    );
})->with([
    'administration' => [DashboardAuth::administration()],
    'warehouse' => [DashboardAuth::warehouse()],
    'education-monitor' => [DashboardAuth::educationMonitor()],
    'education-services-office' => [DashboardAuth::educationServicesOffice()],
    'school' => [DashboardAuth::school()],
]);

test('legacy login route redirects to administration login', function () {
    $this->get('/login')
        ->assertRedirect(route('administration.login'));
});

test('users can authenticate using the panel login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('administration.login'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('administration');
    $response->assertRedirect(route('administration.dashboard'));
});

test('users cannot authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('administration.login'), [
        'username' => $user->username,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest('administration');
});

test('users cannot authenticate on the wrong panel', function () {
    $user = User::factory()->withScope(UserScope::WAREHOUSE)->create();

    $this->post(route('school.login'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertGuest('school');
});

test('deactivated users cannot authenticate', function () {
    $user = User::factory()->withState(Deactivated::class)->create();

    $this->post(route('administration.login'), [
        'username' => $user->username,
        'password' => 'password',
    ])->assertSessionHasErrors('username');

    $this->assertGuest('administration');
});

test('pending users cannot authenticate', function () {
    $user = User::factory()->withRequestState(Pending::class)->create();

    $this->post(route('administration.login'), [
        'username' => $user->username,
        'password' => 'password',
    ])->assertSessionHasErrors('username');

    $this->assertGuest('administration');
});

test('users with must change password are redirected to change password', function () {
    $user = User::factory()->withMustChangePassword()->create();

    $response = $this->post(route('administration.login'), [
        'username' => $user->username,
        'password' => 'password',
    ]);

    $this->assertAuthenticated('administration');
    $response->assertRedirect(route('administration.password.change'));
});

test('must change password blocks dashboard access', function () {
    $user = User::factory()->withMustChangePassword()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertRedirect(route('administration.password.change'));
});

test('users can change required password and access dashboard', function () {
    $user = User::factory()->withMustChangePassword()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.password.change.store'), [
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('administration.dashboard'));

    expect($user->refresh()->must_change_password)->toBeFalse();

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertOk();
});

test('users can logout from their panel', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'administration')
        ->post(route('administration.logout'));

    $response->assertRedirect(route('welcome'));
    $this->assertGuest('administration');
});

test('users are rate limited on panel login', function () {
    $user = User::factory()->create();

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post(route('administration.login'), [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);
    }

    $this->post(route('administration.login'), [
        'username' => $user->username,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('username');
});

test('panel guards are isolated', function () {
    $user = User::factory()->withScope(UserScope::WAREHOUSE)->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('administration.dashboard'))
        ->assertRedirect(route('administration.login'));
});
