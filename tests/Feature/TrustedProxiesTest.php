<?php

use Illuminate\Support\Facades\URL;

test('generated urls use https when the request is forwarded as https', function () {
    $this->get('http://students-stagin.moe-edu.ly/up', [
        'X-Forwarded-Proto' => 'https',
        'X-Forwarded-Host' => 'students-stagin.moe-edu.ly',
        'X-Forwarded-Port' => '443',
    ])->assertSuccessful();

    expect(url('/administration/dashboard'))->toStartWith('https://');
});

test('https is forced when app url uses https', function () {
    config(['app.url' => 'https://students-stagin.moe-edu.ly']);

    URL::forceScheme('https');

    expect(asset('build/assets/app.css'))->toStartWith('https://');
});
