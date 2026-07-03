<?php

use App\Models\User;
use App\Support\Navigation\NavigationPanel;
use Inertia\Testing\AssertableInertia as Assert;

test('administration panel shares navigation for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('routeName', 'administration.dashboard')
            ->where('navigation.home', route('administration.dashboard'))
            ->has('navigation.main', 3)
            ->where('navigation.main.0.title', 'العمليات الأساسية')
            ->has('navigation.main.0.items')
            ->where('navigation.main.0.items.0.title', 'الرئيسية')
            ->where('navigation.main.0.items.0.activeRoutes', 'administration.dashboard')
            ->has('navigation.account.menu', 2)
            ->has('navigation.account.tabs', 2)
            ->where('navigation.account.menu.1.key', 'logout')
            ->where('navigation.account.menu.1.href', route('administration.logout'))
            ->where('navigation.account.tabs.1.activeRoutes', 'administration.account-settings.security.*')
            ->missing('navigation.main.0.items.0.can')
        );
});

test('unknown panel returns empty navigation structure', function () {
    $this->get(route('welcome'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('navigation.home', null)
            ->where('navigation.main', [])
            ->where('navigation.account.menu', [])
            ->where('navigation.account.tabs', [])
        );
});

test('navigation excludes items the user is not authorized to see', function () {
    $panel = new class(app('request')) extends NavigationPanel
    {
        protected function main(): array
        {
            return [
                ['title' => 'Visible', 'href' => '/visible', 'can' => true],
                ['title' => 'Hidden', 'href' => '/hidden', 'can' => false],
            ];
        }
    };

    $items = $panel->get()['main'][0]['items'];

    expect($items)->toHaveCount(1)
        ->and($items[0]['title'])->toBe('Visible')
        ->and($items[0])->not->toHaveKey('can');
});

test('navigation defaults the icon and preserves route matching attributes', function () {
    $panel = new class(app('request')) extends NavigationPanel
    {
        protected function main(): array
        {
            return [
                [
                    'title' => 'Students',
                    'href' => '/students',
                    'can' => true,
                    'activeRoutes' => ['school.students.*', 'school.classes.*'],
                    'excludedRoutes' => 'school.students.unenrolled-from-*',
                ],
            ];
        }
    };

    expect($panel->get()['main'][0]['items'][0])->toMatchArray([
        'title' => 'Students',
        'href' => '/students',
        'icon' => 'CircleIcon',
        'activeRoutes' => ['school.students.*', 'school.classes.*'],
        'excludedRoutes' => 'school.students.unenrolled-from-*',
    ]);
});
