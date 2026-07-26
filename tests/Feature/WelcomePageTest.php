<?php

use Inertia\Testing\AssertableInertia as Assert;

test('welcome page renders the portal entry experience', function () {
    $this->get(route('welcome'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('routeName', 'welcome')
            ->where('navigation.home', null)
            ->where('navigation.main', [])
        );
});
