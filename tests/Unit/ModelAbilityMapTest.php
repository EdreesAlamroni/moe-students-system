<?php

use App\Models\Municipal;
use App\Models\User;
use App\Support\Authorization\ModelAbilityMap;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class);

it('builds an ability map for an explicit user', function () {
    $municipal = Municipal::factory()->makeOne();

    /** @var User $user */
    $user = User::factory()->makeOne();

    Gate::define('view', fn () => true);
    Gate::define('update', fn () => false);

    expect(ModelAbilityMap::make($municipal, ['view', 'update'], user: $user))->toBe([
        'view' => true,
        'update' => false,
    ]);
});

it('returns false for every ability when no user is authenticated', function () {
    $municipal = Municipal::factory()->make();

    Gate::define('view', fn () => true);

    expect(ModelAbilityMap::make($municipal, ['view']))->toBe([
        'view' => false,
    ]);
});

it('resolves the user from an explicit guard', function () {
    $municipal = Municipal::factory()->make();
    $user = User::factory()->create();

    Gate::define('view', fn (User $authenticatedUser) => $authenticatedUser->is($user));

    $this->actingAs($user, 'administration');

    expect(ModelAbilityMap::make($municipal, ['view'], guard: 'administration'))->toBe([
        'view' => true,
    ]);
});
