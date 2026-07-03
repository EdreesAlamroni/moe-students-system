<?php

test('app layout does not inject automatic vite fonts', function () {
    $response = $this->get(route('welcome'));

    $response->assertOk();
    $response->assertDontSee('Instrument Sans', false);
    $response->assertDontSee('--font-instrument-sans', false);
    $response->assertDontSee('font-instrument-sans', false);
});

test('app css defines SomarSans as the default sans font', function () {
    $appCss = file_get_contents(resource_path('css/app.css'));

    expect($appCss)
        ->toContain("'SomarSans'")
        ->not->toContain('@fontsource-variable/inter');
});

test('vite config does not register automatic font providers', function () {
    $viteConfig = file_get_contents(base_path('vite.config.ts'));

    expect($viteConfig)
        ->not->toContain('laravel-vite-plugin/fonts')
        ->not->toContain('fonts:');
});
