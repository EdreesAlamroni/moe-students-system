<?php

use App\Enums\UserScope;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

/**
 * Wrap a persisted warehouse in a partial mock so route-model binding resolves it,
 * allowing the instance-level hasAnyRelations() check to be controlled in tests.
 */
function bindWarehouseBinding(Warehouse $warehouse, bool $hasAnyRelations): Warehouse
{
    /** @var Warehouse&MockInterface $mock */
    $mock = Mockery::mock($warehouse)->makePartial();
    $mock->shouldReceive('hasAnyRelations')->andReturn($hasAnyRelations);
    $mock->shouldReceive('resolveRouteBinding')->andReturn($mock);

    app()->instance(Warehouse::class, $mock);

    return $mock;
}

function createWarehouseAdminUser(): User
{
    $user = User::factory()->create();

    foreach (['warehouse:view-any', 'warehouse:view', 'warehouse:create', 'warehouse:update', 'warehouse:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'warehouse:view-any',
        'warehouse:view',
        'warehouse:create',
        'warehouse:update',
        'warehouse:delete',
    ]);

    return $user;
}

function warehousePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Central Warehouse',
        'education_monitor_ids' => '[]',
        'address' => 'Benghazi - Libya',
        'add_location_to_map' => false,
        'latitude' => null,
        'longitude' => null,
    ], $overrides);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/warehouses', 'GET'));
});

test('guests are redirected from the warehouses page', function () {
    $this->get(route('administration.warehouses.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without warehouse permissions cannot view warehouses', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.warehouses.index'))
        ->assertForbidden();
});

test('authenticated users can visit the warehouses page', function () {
    $user = createWarehouseAdminUser();
    $warehouse = Warehouse::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.warehouses.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/warehouses/index')
            ->has('warehouses.data', 1)
            ->where('warehouses.data.0.number', $warehouse->number)
            ->where('warehouses.data.0.name', $warehouse->name)
            ->where('filter', [])
        );
});

test('authenticated users can store a warehouse with a generated number', function () {
    $user = createWarehouseAdminUser();

    $this->actingAs($user, 'administration')
        ->post(route('administration.warehouses.store'), warehousePayload())
        ->assertRedirect();

    $warehouse = Warehouse::query()->firstOrFail();

    expect($warehouse->number)->toMatch('/^WH-\d{3}$/');

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'name' => 'Central Warehouse',
    ]);
});

test('authenticated users can visit the show warehouse page', function () {
    $user = createWarehouseAdminUser();
    $warehouse = Warehouse::factory()->create(['name' => 'Show Warehouse']);

    $this->actingAs($user, 'administration')
        ->get(route('administration.warehouses.show', ['warehouse' => $warehouse]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/warehouses/show')
            ->where('warehouse.number', $warehouse->number)
            ->where('warehouse.name', 'Show Warehouse')
        );
});

test('authenticated users can visit the edit warehouse page', function () {
    $user = createWarehouseAdminUser();
    $warehouse = Warehouse::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.warehouses.edit', ['warehouse' => $warehouse]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/warehouses/edit')
            ->where('warehouse.number', $warehouse->number)
            ->where('warehouse.uuid', $warehouse->uuid)
        );
});

test('authenticated users can delete a warehouse without relations', function () {
    $user = createWarehouseAdminUser();
    $warehouse = bindWarehouseBinding(Warehouse::factory()->create(), hasAnyRelations: false);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.warehouses.destroy', ['warehouse' => $warehouse]))
        ->assertRedirect(route('administration.warehouses.index'));

    $this->assertSoftDeleted('warehouses', [
        'id' => $warehouse->id,
    ]);
});

test('authenticated users cannot delete a warehouse with relations', function () {
    $user = createWarehouseAdminUser();
    $warehouse = bindWarehouseBinding(Warehouse::factory()->create(), hasAnyRelations: true);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.warehouses.destroy', ['warehouse' => $warehouse]))
        ->assertForbidden();
});
