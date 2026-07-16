<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @param  array<string, mixed>  $attributes
 */
function createWarehouseManager(Warehouse $warehouse, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));

    foreach (['user:view-any', 'user:view', 'user:create', 'user:update', 'user:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::WAREHOUSE->value);
    }

    $user->givePermissionTo([
        'user:view-any',
        'user:view',
        'user:create',
        'user:update',
        'user:delete',
    ]);

    return $user;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function warehouseDashboardUserPayload(array $overrides = []): array
{
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);

    return array_merge([
        'name' => 'New Warehouse User',
        'username' => 'warehouse.dashboard.user',
        'email' => 'warehouse.dashboard.user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createWarehousePeer(Warehouse $warehouse, array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/users', 'GET'));
});

test('guests are redirected from the warehouse users page', function () {
    $this->get(route('warehouse.users.index'))
        ->assertRedirect(route('warehouse.login'));
});

test('users without user permissions cannot visit the create user page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.users.create'))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the users index', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $peer = createWarehousePeer($warehouse, [
        'name' => 'Peer User',
        'username' => 'peer.user',
    ]);
    $otherWarehouse = Warehouse::factory()->create();
    createWarehousePeer($otherWarehouse, [
        'name' => 'Other Warehouse User',
        'username' => 'other.warehouse.user',
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/users/index')
            ->has('users.data', 2)
            ->where('users.data.0.username', $user->username)
            ->where('users.data.1.username', $peer->username)
            ->has('can.create')
        );
});

test('authenticated warehouse users can visit the create user page', function () {
    $warehouse = Warehouse::factory()->create(['name' => 'Central Warehouse']);
    $user = createWarehouseManager($warehouse);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.users.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/users/create')
            ->where('scope.id', UserScope::WAREHOUSE->value)
            ->where('warehouse.id', $warehouse->id)
            ->where('warehouse.name', 'Central Warehouse')
            ->has('groupedRoles')
        );
});

test('authenticated warehouse users can store a user for their warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $payload = warehouseDashboardUserPayload();

    $response = $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::WAREHOUSE)
        ->and($createdUser->role)->toBe(UserRole::EMPLOYEE)
        ->and($createdUser->request_state)->toBeInstanceOf(Approved::class)
        ->and($createdUser->organization_id)->toBe($warehouse->id)
        ->and($createdUser->organization_type)->toBe(Warehouse::class)
        ->and($createdUser->hasRole($payload['roles'][0]))->toBeTrue();

    $response->assertRedirect(route('warehouse.users.show', ['user' => $createdUser]));
});

test('store validates required fields', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);

    $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.users.store'), [])
        ->assertSessionHasErrors(['name', 'username', 'password', 'roles']);
});

test('store rejects roles that do not belong to the warehouse scope', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $foreignRole = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);

    $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.users.store'), warehouseDashboardUserPayload([
            'roles' => [$foreignRole->id],
        ]))
        ->assertSessionHasErrors('roles.0');
});

test('store accepts roles submitted as json', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);
    $payload = warehouseDashboardUserPayload([
        'username' => 'json.roles.warehouse.user',
        'email' => 'json.roles.warehouse.user@example.com',
        'roles' => json_encode([$role->id]),
    ]);

    $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.users.store'), $payload)
        ->assertRedirect();

    $createdUser = User::query()->where('username', 'json.roles.warehouse.user')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->hasRole($role))->toBeTrue();
});

test('authenticated warehouse users can visit the show user page', function () {
    $warehouse = Warehouse::factory()->create(['name' => 'Show Warehouse']);
    $user = createWarehouseManager($warehouse);
    $target = createWarehousePeer($warehouse, [
        'name' => 'Shown User',
        'username' => 'shown.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);
    $target->assignRole($role);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/users/show')
            ->where('user.name', 'Shown User')
            ->where('user.username', 'shown.user')
            ->where('user.scope.id', UserScope::WAREHOUSE->value)
            ->where('user.organization.type', 'warehouse')
            ->where('user.organization.organization.warehouse.name', 'Show Warehouse')
            ->has('roles')
            ->has('availableStates')
            ->has('can.update')
            ->has('can.delete')
        );
});

test('warehouse users cannot view users from another warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $otherWarehouse = Warehouse::factory()->create();
    $target = createWarehousePeer($otherWarehouse);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.users.show', ['user' => $target]))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the edit user page', function () {
    $warehouse = Warehouse::factory()->create(['name' => 'Edit Warehouse']);
    $user = createWarehouseManager($warehouse);
    $target = createWarehousePeer($warehouse, [
        'name' => 'Editable User',
        'username' => 'editable.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);
    $target->assignRole($role);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.users.edit', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/users/edit')
            ->where('user.name', 'Editable User')
            ->where('user.username', 'editable.user')
            ->where('user.scope.id', UserScope::WAREHOUSE->value)
            ->where('user.organization.type', 'warehouse')
            ->where('user.organization.organization.warehouse.name', 'Edit Warehouse')
            ->has('groupedRoles')
            ->has('user.role_ids', 1)
        );
});

test('authenticated warehouse users can update a user', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $target = createWarehousePeer($warehouse, [
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);
    $existingRole = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);
    $newRole = Role::findOrCreate('user:role:update', UserScope::WAREHOUSE->value);
    $target->assignRole($existingRole);

    $this->actingAs($user, 'warehouse')
        ->put(route('warehouse.users.update', ['user' => $target]), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => [$newRole->id],
        ])
        ->assertRedirect(route('warehouse.users.show', ['user' => $target]));

    $target->refresh();

    expect($target->name)->toBe('Updated Name')
        ->and($target->email)->toBe('updated@example.com')
        ->and($target->hasRole($newRole))->toBeTrue()
        ->and($target->hasRole($existingRole))->toBeFalse();
});

test('update validates required fields', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $target = createWarehousePeer($warehouse);

    $this->actingAs($user, 'warehouse')
        ->put(route('warehouse.users.update', ['user' => $target]), [])
        ->assertSessionHasErrors(['name', 'roles']);
});

test('authenticated warehouse users can delete a user', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $target = createWarehousePeer($warehouse);

    $this->actingAs($user, 'warehouse')
        ->delete(route('warehouse.users.destroy', ['user' => $target]))
        ->assertRedirect(route('warehouse.users.index'));

    $this->assertSoftDeleted($target);
});

test('warehouse users cannot delete users from another warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseManager($warehouse);
    $otherWarehouse = Warehouse::factory()->create();
    $target = createWarehousePeer($otherWarehouse);

    $this->actingAs($user, 'warehouse')
        ->delete(route('warehouse.users.destroy', ['user' => $target]))
        ->assertForbidden();

    $this->assertNotSoftDeleted($target);
});
