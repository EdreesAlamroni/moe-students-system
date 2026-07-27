<?php

use Tests\TestCase;

uses(TestCase::class);

test('application helpers are available at runtime', function () {
    expect(function_exists('flash_success'))->toBeTrue()
        ->and(function_exists('flash_error'))->toBeTrue()
        ->and(function_exists('classroom_names'))->toBeTrue();
});

test('application helpers are registered in production composer autoload', function () {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($composer['autoload']['files'] ?? [])->toContain('app/Support/helpers.php')
        ->and($composer['autoload-dev']['files'] ?? [])->not->toContain('app/Support/helpers.php');
});
