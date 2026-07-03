<?php

use App\Models\User;

test('guests are redirected to the panel login page', function () {
    $response = $this->get(route('administration.dashboard'));
    $response->assertRedirect(route('administration.login'));
});

test('authenticated users can visit the panel dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertOk();
});
