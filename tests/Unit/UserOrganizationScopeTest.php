<?php

use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

uses(TestCase::class);

it('does not constrain the query when no user is authenticated', function () {
    $baseSql = User::query()->toSql();

    expect(User::query()->forCurrentEducationMonitor()->toSql())->toBe($baseSql)
        ->and(User::query()->forCurrentEducationServicesOffice()->toSql())->toBe($baseSql)
        ->and(User::query()->forCurrentSchool()->toSql())->toBe($baseSql)
        ->and(User::query()->forCurrentWarehouse()->toSql())->toBe($baseSql);
});

it('does not constrain the query when the authenticated user has no organization', function () {
    $user = User::factory()->make(['model_id' => null]);

    $this->actingAs($user, UserScope::SCHOOL->guard());

    expect(User::query()->forCurrentSchool()->toSql())->toBe(User::query()->toSql());
});

it('scopes users to the authenticated school organization', function () {
    $user = User::factory()->make(['model_id' => 42]);

    $this->actingAs($user, UserScope::SCHOOL->guard());

    $query = User::query()->forCurrentSchool();

    expect($query->getBindings())->toContain(42, School::class);
});

it('scopes users to the authenticated warehouse organization', function () {
    $user = User::factory()->make(['model_id' => 7]);

    $this->actingAs($user, UserScope::WAREHOUSE->guard());

    $query = User::query()->forCurrentWarehouse();

    expect($query->getBindings())->toContain(7, Warehouse::class);
});

it('scopes users to the authenticated education monitor and its descendants', function () {
    $user = User::factory()->make(['model_id' => 3]);

    $this->actingAs($user, UserScope::EDUCATION_MONITOR->guard());

    $query = User::query()->forCurrentEducationMonitor();

    expect($query->getBindings())->toContain(3, EducationMonitor::class)
        ->and($query->toSql())->toContain('exists');
});

it('scopes users to the authenticated education services office and its schools', function () {
    $user = User::factory()->make(['model_id' => 11]);

    $this->actingAs($user, UserScope::EDUCATION_SERVICES_OFFICE->guard());

    $query = User::query()->forCurrentEducationServicesOffice();

    expect($query->getBindings())->toContain(11, EducationServicesOffice::class)
        ->and($query->toSql())->toContain('exists');
});
