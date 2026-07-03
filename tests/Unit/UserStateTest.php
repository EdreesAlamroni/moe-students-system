<?php

use App\Models\User;
use App\ModelStates\User\State\Activated;
use App\ModelStates\User\State\Deactivated;
use App\ModelStates\User\State\UserState;
use Tests\TestCase;

uses(TestCase::class);

it('resolves the morph class as the state value', function () {
    expect(Activated::getMorphClass())->toBe('activated')
        ->and(Deactivated::getMorphClass())->toBe('deactivated');
});

it('serializes a state to a ui option array', function () {
    $user = User::factory()->make();

    expect(UserState::make('activated', $user)->toArray())->toBe([
        'value' => 'activated',
        'label' => __('app.states.user.state.labels.activated'),
        'uiClasses' => 'pill pill-green',
        'action' => __('app.states.user.state.actions.activated'),
    ]);
});

it('defines allowed transitions in config', function () {
    $config = Activated::config();

    expect($config->defaultStateClass)->toBe(Activated::class)
        ->and($config->transitionableStates('activated'))->toContain('deactivated')
        ->and($config->transitionableStates('deactivated'))->toContain('activated');
});
