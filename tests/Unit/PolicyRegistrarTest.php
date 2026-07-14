<?php

use App\Models\EducationMonitor;
use App\Policies\Administration\EducationMonitorPolicy;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

uses(TestCase::class);

test('register binds policies for the current request group', function () {
    PolicyRegistrar::register(Request::create('/administration/education-monitors', 'GET'));

    expect(Gate::getPolicyFor(EducationMonitor::class))
        ->toBeInstanceOf(EducationMonitorPolicy::class);
});

test('registerAll binds policies from every group for console discovery', function () {
    PolicyRegistrar::registerAll();

    expect(Gate::getPolicyFor(EducationMonitor::class))
        ->toBeInstanceOf(EducationMonitorPolicy::class);
});

test('policy service provider registers all policies when running in console', function () {
    expect(Gate::getPolicyFor(EducationMonitor::class))
        ->toBeInstanceOf(EducationMonitorPolicy::class);
});
