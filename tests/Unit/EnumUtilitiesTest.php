<?php

use App\Enums\UserRole;
use Tests\TestCase;

uses(TestCase::class);

it('generates options with id and name keys by default', function () {
    $option = UserRole::MANAGER->toOption();

    expect($option)->toBe([
        'id' => 'manager',
        'name' => UserRole::MANAGER->label(),
    ]);
});

it('generates options with customizable keys', function () {
    $option = UserRole::MANAGER->toOption('value', 'label');

    expect($option)->toHaveKeys(['value', 'label'])
        ->and($option['value'])->toBe('manager')
        ->and($option['label'])->toBe(UserRole::MANAGER->label());
});

it('returns all enum cases as options', function () {
    expect(UserRole::options())->toHaveCount(2)
        ->and(UserRole::options()->first())->toHaveKeys(['id', 'name']);
});

it('returns options as a plain array', function () {
    expect(UserRole::optionsArray())->toBeArray()
        ->and(UserRole::optionsArray())->toHaveCount(2);
});

it('serializes a case to a structured array', function () {
    expect(UserRole::MANAGER->toArray())->toBe([
        'id' => 'manager',
        'name' => UserRole::MANAGER->label(),
        'key' => 'MANAGER',
    ]);
});

it('resolves toArrayFor from a case or backed value', function () {
    expect(UserRole::toArrayFor(UserRole::MANAGER))->toBe(UserRole::MANAGER->toArray())
        ->and(UserRole::toArrayFor('manager'))->toBe(UserRole::MANAGER->toArray())
        ->and(UserRole::toArrayFor('invalid'))->toBe([]);
});

it('returns backed values', function () {
    expect(UserRole::values())->toBe(['manager', 'employee']);
});
