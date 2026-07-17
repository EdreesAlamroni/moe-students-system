<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\Municipal;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createWarehouseEducationMonitorUser(Warehouse $warehouse, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));

    foreach (['education-monitor:view-any', 'education-monitor:view'] as $permission) {
        Permission::findOrCreate($permission, UserScope::WAREHOUSE->value);
    }

    $user->givePermissionTo([
        'education-monitor:view-any',
        'education-monitor:view',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/education-monitors', 'GET'));
});

test('guests are redirected from the warehouse education monitors page', function () {
    $this->get(route('warehouse.education-monitors.index'))
        ->assertRedirect(route('warehouse.login'));
});

test('users without education monitor permissions cannot view education monitors', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.education-monitors.index'))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the education monitors index', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseEducationMonitorUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.education-monitors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/education-monitors/index')
            ->has('monitors.data', 1)
            ->where('monitors.data.0.name', $monitor->name)
            ->where('monitors.data.0.students_count', 0)
            ->where('filter', [])
            ->missing('can.create')
        );
});

test('authenticated warehouse users can filter education monitors by name', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseEducationMonitorUser($warehouse);
    $benghazi = Municipal::factory()->create(['name' => 'بنغازي']);
    $tripoli = Municipal::factory()->create(['name' => 'طرابلس']);
    EducationMonitor::factory()->for($warehouse, 'warehouse')->for($benghazi, 'municipal')->create();
    EducationMonitor::factory()->for($warehouse, 'warehouse')->for($tripoli, 'municipal')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.education-monitors.index', ['filter' => ['name' => 'بنغازي']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/education-monitors/index')
            ->has('monitors.data', 1)
            ->where('monitors.data.0.name', 'مُراقبة التّربية والتّعليم بنغازي')
            ->where('filter.name', 'بنغازي')
        );
});

test('authenticated warehouse users can visit the show education monitor page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseEducationMonitorUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.education-monitors.show', ['monitor' => $monitor]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/education-monitors/show')
            ->where('monitor.name', $monitor->name)
            ->where('monitor.students_count', 0)
            ->has('offices.data', 1)
            ->where('offices.data.0.uuid', $office->uuid)
            ->missing('can.update')
            ->missing('can.delete')
        );
});

test('warehouse users cannot view education monitors from another warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseEducationMonitorUser($warehouse);
    $monitor = EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.education-monitors.show', ['monitor' => $monitor]))
        ->assertForbidden();
});

test('warehouse users cannot view education monitors from another warehouse on the index', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseEducationMonitorUser($warehouse);
    EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.education-monitors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/education-monitors/index')
            ->has('monitors.data', 0)
        );
});
