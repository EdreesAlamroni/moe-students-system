<?php

use App\Models\AcademicYear;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'administration')
        ->get(route('administration.password.confirm'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('auth/confirm-password'),
    );
});

test('password confirmation requires authentication', function () {
    $response = $this->get(route('administration.password.confirm'));

    $response->assertRedirect(route('administration.login'));
});

test('password confirmation is allowed when the selected academic year is inactive', function () {
    AcademicYear::clearCachedCurrent();

    $user = User::factory()->create();
    $inactiveYear = AcademicYear::factory()->create(['is_active' => false]);

    $this->actingAs($user, 'administration')
        ->withSession([sprintf('selected_academic_year_id.%d', $user->id) => $inactiveYear->id])
        ->post(route('administration.password.confirm.store'), [
            'password' => 'password',
        ])
        ->assertRedirect();
});
