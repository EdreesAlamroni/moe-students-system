<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\ModelStates\User\RequestState\Approved;
use App\ModelStates\User\RequestState\Pending;
use App\ModelStates\User\State\Activated;
use App\ModelStates\User\State\Deactivated;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function createUserAdminUser(): User
{
    $user = User::factory()->create();

    foreach ([
        'user:view-any',
        'user:view',
        'user:create',
        'user:update',
        'user:delete',
        'user:state-update',
    ] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'user:view-any',
        'user:view',
        'user:create',
        'user:update',
        'user:delete',
        'user:state-update',
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

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function educationMonitorUserPayload(EducationMonitor $monitor, array $overrides = []): array
{
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_MONITOR->value);

    return array_merge([
        'scope' => UserScope::EDUCATION_MONITOR->value,
        'education_monitor_id' => $monitor->id,
        'name' => 'New Education Monitor User',
        'username' => 'education.monitor.user.create',
        'email' => null,
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function educationServicesOfficeUserPayload(
    EducationMonitor $monitor,
    EducationServicesOffice $office,
    array $overrides = [],
): array {
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_SERVICES_OFFICE->value);

    return array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE->value,
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $office->id,
        'name' => 'New Education Services Office User',
        'username' => 'education.services.office.user.create',
        'email' => null,
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function schoolUserPayload(EducationMonitor $monitor, School $school, array $overrides = []): array
{
    $role = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    return array_merge([
        'scope' => UserScope::SCHOOL->value,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'name' => 'New School User',
        'username' => 'school.user.create',
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

test('users index orders by scope hierarchy', function () {
    $admin = createUserAdminUser();

    User::factory()->withScope(UserScope::SCHOOL)->create(['username' => 'scope.school']);
    User::factory()->withScope(UserScope::EDUCATION_SERVICES_OFFICE)->create(['username' => 'scope.office']);
    User::factory()->withScope(UserScope::EDUCATION_MONITOR)->create(['username' => 'scope.monitor']);
    User::factory()->withScope(UserScope::WAREHOUSE)->create(['username' => 'scope.warehouse']);

    $this->actingAs($admin, 'administration')
        ->get(route('administration.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/users/index')
            ->where('users.data', function ($users) {
                $scopes = collect($users)->pluck('scope.id')->all();

                return $scopes === [
                    UserScope::ADMINISTRATION->value,
                    UserScope::WAREHOUSE->value,
                    UserScope::EDUCATION_MONITOR->value,
                    UserScope::EDUCATION_SERVICES_OFFICE->value,
                    UserScope::SCHOOL->value,
                ];
            })
        );
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

test('store requires a warehouse when the field is omitted', function () {
    $user = createUserAdminUser();
    $role = Role::findOrCreate('user:role:view', UserScope::WAREHOUSE->value);
    $payload = [
        'scope' => UserScope::WAREHOUSE->value,
        'name' => 'Warehouse User Without Organization',
        'username' => 'warehouse.user.without.organization',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ];

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertSessionHasErrors('warehouse_id');

    expect(User::query()->where('username', $payload['username'])->exists())->toBeFalse();
});

test('store requires an education monitor when the field is omitted', function () {
    $user = createUserAdminUser();
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_MONITOR->value);
    $payload = [
        'scope' => UserScope::EDUCATION_MONITOR->value,
        'name' => 'Education Monitor User Without Organization',
        'username' => 'education.monitor.user.without.organization',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ];

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertSessionHasErrors('education_monitor_id');

    expect(User::query()->where('username', $payload['username'])->exists())->toBeFalse();
});

test('store requires an education monitor and office when office fields are omitted', function () {
    $user = createUserAdminUser();
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_SERVICES_OFFICE->value);
    $payload = [
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE->value,
        'name' => 'Education Services Office User Without Organization',
        'username' => 'education.services.office.user.without.organization',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ];

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertSessionHasErrors(['education_monitor_id', 'education_services_office_id']);

    expect(User::query()->where('username', $payload['username'])->exists())->toBeFalse();
});

test('store requires an education monitor and school when school fields are omitted', function () {
    $user = createUserAdminUser();
    $role = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);
    $payload = [
        'scope' => UserScope::SCHOOL->value,
        'name' => 'School User Without Organization',
        'username' => 'school.user.without.organization',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ];

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertSessionHasErrors(['education_monitor_id', 'school_id']);

    expect(User::query()->where('username', $payload['username'])->exists())->toBeFalse();
});

test('authenticated users can store an education monitor user', function () {
    $user = createUserAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $payload = educationMonitorUserPayload($monitor, [
        'username' => 'education.monitor.user.store',
    ]);

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertRedirect();

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::EDUCATION_MONITOR)
        ->and($createdUser->organization_id)->toBe($monitor->id)
        ->and($createdUser->organization_type)->toBe(EducationMonitor::class);
});

test('authenticated users can store an education services office user', function () {
    $user = createUserAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $payload = educationServicesOfficeUserPayload($monitor, $office, [
        'username' => 'education.services.office.user.store',
    ]);

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertRedirect();

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::EDUCATION_SERVICES_OFFICE)
        ->and($createdUser->organization_id)->toBe($office->id)
        ->and($createdUser->organization_type)->toBe(EducationServicesOffice::class);
});

test('authenticated users can store a school user', function () {
    $user = createUserAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $payload = schoolUserPayload($monitor, $school, [
        'username' => 'school.user.store',
    ]);

    $this->actingAs($user, 'administration')
        ->post(route('administration.users.store'), $payload)
        ->assertRedirect();

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::SCHOOL)
        ->and($createdUser->organization_id)->toBe($school->id)
        ->and($createdUser->organization_type)->toBe(School::class);
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
            ->has('can.stateUpdate')
            ->has('can.updatePassword')
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

test('authenticated users can update a user account state', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->patch(route('administration.users.state.update', ['user' => $target]), [
            'state' => 'deactivated',
        ])
        ->assertRedirect(route('administration.users.show', ['user' => $target]));

    expect($target->fresh()->state)->toBeInstanceOf(Deactivated::class);
});

test('authenticated users can update a user request state', function () {
    $user = createUserAdminUser();
    $target = User::factory()
        ->withRequestState(Pending::class)
        ->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->patch(route('administration.users.request-state.update', ['user' => $target]), [
            'request_state' => 'approved',
        ])
        ->assertRedirect(route('administration.users.show', ['user' => $target]));

    expect($target->fresh()->request_state)->toBeInstanceOf(Approved::class);
});

test('state update validates the selected state', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->patch(route('administration.users.state.update', ['user' => $target]), [])
        ->assertSessionHasErrors('state');
});

test('request state update validates the selected state', function () {
    $user = createUserAdminUser();
    $target = User::factory()
        ->withRequestState(Pending::class)
        ->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->patch(route('administration.users.request-state.update', ['user' => $target]), [])
        ->assertSessionHasErrors('request_state');
});

test('users without state update permission cannot update account state', function () {
    $user = User::factory()->create();
    Permission::findOrCreate('user:view', UserScope::ADMINISTRATION->value);
    $user->givePermissionTo('user:view');

    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->patch(route('administration.users.state.update', ['user' => $target]), [
            'state' => 'deactivated',
        ])
        ->assertForbidden();

    expect($target->fresh()->state)->toBeInstanceOf(Activated::class);
});

test('authenticated administrators can update a user password', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.users.password.update', ['user' => $target]), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertRedirect(route('administration.users.show', ['user' => $target]));

    expect(Hash::check('new-password-123', $target->fresh()->password))->toBeTrue();
});

test('password update validates required fields', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.users.password.update', ['user' => $target]), [])
        ->assertSessionHasErrors('password');
});

test('password update requires confirmation to match', function () {
    $user = createUserAdminUser();
    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.users.password.update', ['user' => $target]), [
            'password' => 'new-password-123',
            'password_confirmation' => 'different-password',
        ])
        ->assertSessionHasErrors('password');
});

test('non-administrators cannot update a user password', function () {
    $user = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    Permission::findOrCreate('user:update', UserScope::ADMINISTRATION->value);
    $user->givePermissionTo('user:update');

    $target = User::factory()->create(['role' => UserRole::EMPLOYEE]);
    $originalPassword = $target->password;

    $this->actingAs($user, 'administration')
        ->put(route('administration.users.password.update', ['user' => $target]), [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertForbidden();

    expect($target->fresh()->password)->toBe($originalPassword);
});
