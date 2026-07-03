<?php

use App\Models\Municipal;
use App\Models\User;

test('guests are redirected from the municipals page', function () {
    $this->get(route('administration.municipals.index'))
        ->assertRedirect(route('administration.login'));
});

test('authenticated users can visit the municipals page', function () {
    $user = User::factory()->create();
    $municipal = Municipal::factory()->create(['name' => 'Tripoli']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.municipals.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/municipals/index')
            ->has('municipals.data', 1)
            ->where('municipals.data.0.name', $municipal->name)
            ->where('filter', [])
        );
});

test('municipals page can be filtered by name', function () {
    $user = User::factory()->create();
    Municipal::factory()->create(['name' => 'Tripoli']);
    Municipal::factory()->create(['name' => 'Benghazi']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.municipals.index', ['filter' => ['name' => 'Tripoli']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('municipals.data', 1)
            ->where('municipals.data.0.name', 'Tripoli')
            ->where('filter.name', 'Tripoli')
        );
});
