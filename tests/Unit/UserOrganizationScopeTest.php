<?php

use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\SchoolPeriod;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('fails closed when no user is authenticated', function () {
    expect(User::query()->forCurrentEducationMonitor()->toSql())->toContain('1 = 0')
        ->and(User::query()->forCurrentEducationServicesOffice()->toSql())->toContain('1 = 0')
        ->and(User::query()->forCurrentSchool()->toSql())->toContain('1 = 0')
        ->and(User::query()->forCurrentWarehouse()->toSql())->toContain('1 = 0');
});

it('fails closed when the authenticated user has no organization', function () {
    $user = User::factory()->make([
        'scope' => UserScope::SCHOOL,
        'organization_id' => null,
        'organization_type' => null,
    ]);

    $this->actingAs($user, UserScope::SCHOOL->guard());

    expect(User::query()->forCurrentSchool()->toSql())->toContain('1 = 0');
});

it('scopes users to the authenticated school organization', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_id' => $schoolPeriod->id,
        'organization_type' => SchoolPeriod::class,
    ]);

    $this->actingAs($user, UserScope::SCHOOL->guard());

    $query = User::query()->forCurrentSchool();

    expect($query->getBindings())->toContain($schoolPeriod->id, SchoolPeriod::class);
});

it('scopes users to the authenticated warehouse organization', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->make([
        'scope' => UserScope::WAREHOUSE,
        'organization_id' => $warehouse->id,
        'organization_type' => Warehouse::class,
    ]);

    $this->actingAs($user, UserScope::WAREHOUSE->guard());

    $query = User::query()->forCurrentWarehouse();

    expect($query->getBindings())->toContain($warehouse->id, Warehouse::class);
});

it('scopes users to the authenticated education monitor and its descendants', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->make([
        'scope' => UserScope::EDUCATION_MONITOR,
        'organization_id' => $monitor->id,
        'organization_type' => EducationMonitor::class,
    ]);

    $this->actingAs($user, UserScope::EDUCATION_MONITOR->guard());

    $query = User::query()->forCurrentEducationMonitor();

    expect($query->getBindings())->toContain($monitor->id, EducationMonitor::class)
        ->and($query->toSql())->toContain('exists');
});

it('scopes users to the authenticated education services office and its schools', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->make([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'organization_id' => $office->id,
        'organization_type' => EducationServicesOffice::class,
    ]);

    $this->actingAs($user, UserScope::EDUCATION_SERVICES_OFFICE->guard());

    $query = User::query()->forCurrentEducationServicesOffice();

    expect($query->getBindings())->toContain($office->id, EducationServicesOffice::class)
        ->and($query->toSql())->toContain('exists');
});
