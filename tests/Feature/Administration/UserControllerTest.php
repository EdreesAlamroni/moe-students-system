<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createUserAdminUser(): User
{
    $user = User::factory()->create();

    foreach (['user:view-any', 'user:view', 'user:create', 'user:update', 'user:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
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
function administrationUserPayload(array $overrides = []): array
{
    $role = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);

    return array_merge([
        'scope' => UserScope::ADMINISTRATION->value,
        'name' => 'New Administration User',
        'username' => 'admin.user.create',
        'email' => 'admin.user.create@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function warehouseUserPayload(Warehouse $warehouse, array $overrides = []): array
{
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);

    return array_merge([
        'scope' => UserScope::WAREHOUSE->value,
        'warehouse_id' => $warehouse->id,
        'name' => 'New Warehouse User',
        'username' => 'warehouse.user.create',
        'email' => null,
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/users', 'GET'));
});

test('guests are redirected from the users page', function () {
    $this->get(route('administration.users.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without user permissions cannot visit the create user page', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.create', ['scope' => UserScope::ADMINISTRATION->value]))
        ->assertForbidden();
});

test('authenticated users can visit the create administration user page', function () {
    $user = createUserAdminUser();

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.create', ['scope' => UserScope::ADMINISTRATION->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/create')
            ->where('scope.id', UserScope::ADMINISTRATION->value)
            ->where('creationLabel', UserScope::ADMINISTRATION->getCreationLabel())
            ->where('warehouses', [])
            ->where('monitors', [])
            ->has('groupedRoles')
        );
});

test('create warehouse user page loads warehouses', function () {
    $user = createUserAdminUser();
    $warehouse = Warehouse::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.create', ['scope' => UserScope::WAREHOUSE->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/create')
            ->where('scope.id', UserScope::WAREHOUSE->value)
            ->has('warehouses', 1)
            ->where('warehouses.0.id', $warehouse->id)
            ->where('monitors', [])
        );
});

test('create education services office user page loads monitors with offices', function () {
    $user = createUserAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.create', ['scope' => UserScope::EDUCATION_SERVICES_OFFICE->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/create')
            ->where('scope.id', UserScope::EDUCATION_SERVICES_OFFICE->value)
            ->has('monitors', 1)
            ->where('monitors.0.id', $monitor->id)
            ->has('monitors.0.offices', 1)
            ->where('monitors.0.offices.0.id', $office->id)
            ->where('warehouses', [])
        );
});

test('create school user page loads monitors with schools', function () {
    $user = createUserAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.create', ['scope' => UserScope::SCHOOL->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/create')
            ->where('scope.id', UserScope::SCHOOL->value)
            ->has('monitors', 1)
            ->where('monitors.0.id', $monitor->id)
            ->has('monitors.0.schools', 1)
            ->where('monitors.0.schools.0.id', $school->id)
        );
});

test('authenticated users can store an administration user', function () {
    $user = createUserAdminUser();
    $payload = administrationUserPayload();

    $response = $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::ADMINISTRATION)
        ->and($createdUser->role)->toBe(UserRole::EMPLOYEE)
        ->and($createdUser->request_state)->toBeInstanceOf(Approved::class)
        ->and($createdUser->organization_id)->toBeNull()
        ->and($createdUser->organization_type)->toBeNull()
        ->and($createdUser->hasRole($payload['roles'][0]))->toBeTrue();

    $response->assertRedirect(route('administration.users.show', ['user' => $createdUser]));
});

test('authenticated users can store a warehouse user', function () {
    $user = createUserAdminUser();
    $warehouse = Warehouse::factory()->create();
    $payload = warehouseUserPayload($warehouse);

    $response = $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::WAREHOUSE)
        ->and($createdUser->organization_id)->toBe($warehouse->id)
        ->and($createdUser->organization_type)->toBe(Warehouse::class)
        ->and($createdUser->email)->toBeNull();

    $response->assertRedirect(route('administration.users.show', ['user' => $createdUser]));
});

test('store validates required fields for an administration user', function () {
    $user = createUserAdminUser();

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), [
            'scope' => UserScope::ADMINISTRATION->value,
        ])
        ->assertSessionHasErrors(['name', 'username', 'password', 'roles']);
});

test('store requires a warehouse when creating a warehouse user', function () {
    $user = createUserAdminUser();
    $payload = warehouseUserPayload(Warehouse::factory()->create(), [
        'warehouse_id' => null,
    ]);

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertSessionHasErrors('warehouse_id');
});

test('store rejects roles that do not belong to the selected scope', function () {
    $user = createUserAdminUser();
    $foreignRole = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), administrationUserPayload([
            'roles' => [$foreignRole->id],
        ]))
        ->assertSessionHasErrors('roles.0');
});

test('store accepts roles submitted as json', function () {
    $user = createUserAdminUser();
    $role = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);
    $payload = administrationUserPayload([
        'username' => 'json.roles.user',
        'email' => 'json.roles.user@example.com',
        'roles' => json_encode([$role->id]),
    ]);

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertRedirect();

    $createdUser = User::query()->where('username', 'json.roles.user')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->hasRole($role))->toBeTrue();
});

test('authenticated users can visit the show user page', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create([
        'name' => 'Shown User',
        'username' => 'shown.user',
        'role' => UserRole::EMPLOYEE,
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);
    $target->assignRole($role);

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/show')
            ->where('user.name', 'Shown User')
            ->where('user.username', 'shown.user')
            ->where('user.scope.id', UserScope::ADMINISTRATION->value)
            ->where('user.organization', null)
            ->has('roles')
            ->has('availableStates')
            ->has('availableRequestStates')
            ->has('can.update')
            ->has('can.delete')
        );
});

test('show page resolves warehouse organization context', function () {
    $user = createUserAdminUser();
    $warehouse = Warehouse::factory()->create(['name' => 'Central Warehouse']);
    $target = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('user.organization.type', 'warehouse')
            ->where('user.organization.organization.warehouse.id', $warehouse->id)
            ->where('user.organization.organization.warehouse.name', 'Central Warehouse')
        );
});

test('show page resolves school organization with parent monitor', function () {
    $user = createUserAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create(['name' => 'School One']);
    $target = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('user.organization.type', 'school')
            ->where('user.organization.organization.school.name', 'School One')
            ->where('user.organization.organization.education_monitor.id', $monitor->id)
            ->where('user.organization.organization.education_monitor.name', $monitor->name)
        );
});

test('authenticated users can visit the edit user page', function () {
    $user = createUserAdminUser();
    $warehouse = Warehouse::factory()->create(['name' => 'Edit Warehouse']);
    $target = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
        'name' => 'Editable User',
        'username' => 'editable.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);
    $target->assignRole($role);

    $this->actingAs($user, 'administration')
        ->get(route('administration.users.edit', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/edit')
            ->where('user.name', 'Editable User')
            ->where('user.username', 'editable.user')
            ->where('user.scope.id', UserScope::WAREHOUSE->value)
            ->where('user.organization.type', 'warehouse')
            ->where('user.organization.organization.warehouse.name', 'Edit Warehouse')
            ->has('groupedRoles')
            ->has('user.role_ids', 1)
        );
});

test('authenticated users can update a user', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create([
        'role' => UserRole::EMPLOYEE,
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);
    $existingRole = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);
    $newRole = Role::findOrCreate('user:role:update', UserScope::ADMINISTRATION->value);
    $target->assignRole($existingRole);

    $this->actingAs($user, 'administration')
        ->put(route('administration.users.update', ['user' => $target]), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => [$newRole->id],
        ])
        ->assertRedirect(route('administration.users.show', ['user' => $target]));

    $target->refresh();

    expect($target->name)->toBe('Updated Name')
        ->and($target->email)->toBe('updated@example.com')
        ->and($target->hasRole($newRole))->toBeTrue()
        ->and($target->hasRole($existingRole))->toBeFalse();
});

test('update validates required fields', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.users.update', ['user' => $target]), [])
        ->assertSessionHasErrors(['name', 'roles']);
});

test('authenticated users can delete a user', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.users.destroy', ['user' => $target]))
        ->assertRedirect(route('administration.users.index'));

    $this->assertSoftDeleted($target);
});
