<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\School;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @param  array<string, mixed>  $attributes
 */
function createSchoolManager(School $school, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ], $attributes));

    foreach (['user:view-any', 'user:view', 'user:create', 'user:update', 'user:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::SCHOOL->value);
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
function schoolDashboardUserPayload(array $overrides = []): array
{
    $role = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);

    return array_merge([
        'name' => 'New School User',
        'username' => 'school.dashboard.user',
        'email' => 'school.dashboard.user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createSchoolPeer(School $school, array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ], $attributes));
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/users', 'GET'));
});

test('guests are redirected from the school users page', function () {
    $this->get(route('school.users.index'))
        ->assertRedirect(route('school.login'));
});

test('users without user permissions cannot visit the create user page', function () {
    $school = School::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.users.create'))
        ->assertForbidden();
});

test('authenticated school users can visit the users index', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $peer = createSchoolPeer($school, [
        'name' => 'Peer User',
        'username' => 'peer.user',
    ]);
    $otherSchool = School::factory()->create();
    createSchoolPeer($otherSchool, [
        'name' => 'Other School User',
        'username' => 'other.school.user',
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('school/users/index')
            ->has('users.data', 2)
            ->where('users.data.0.username', $user->username)
            ->where('users.data.1.username', $peer->username)
            ->has('can.create')
        );
});

test('authenticated school users can visit the create user page', function () {
    $school = School::factory()->create(['name' => 'Central School']);
    $user = createSchoolManager($school);

    $this->actingAs($user, 'school')
        ->get(route('school.users.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('school/users/create')
            ->where('scope.id', UserScope::SCHOOL->value)
            ->where('school.id', $school->id)
            ->where('school.name', 'Central School')
            ->has('groupedRoles')
        );
});

test('authenticated school users can store a user for their school', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $payload = schoolDashboardUserPayload();

    $response = $this->actingAs($user, 'school')
        ->post(route('school.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::SCHOOL)
        ->and($createdUser->role)->toBe(UserRole::EMPLOYEE)
        ->and($createdUser->request_state)->toBeInstanceOf(Pending::class)
        ->and($createdUser->organization_id)->toBe($school->id)
        ->and($createdUser->organization_type)->toBe(School::class)
        ->and($createdUser->hasRole($payload['roles'][0]))->toBeTrue();

    $response->assertRedirect(route('school.users.show', ['user' => $createdUser]));
});

test('store validates required fields', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);

    $this->actingAs($user, 'school')
        ->post(route('school.users.store'), [])
        ->assertSessionHasErrors(['name', 'username', 'password', 'roles']);
});

test('store rejects roles that do not belong to the school scope', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $foreignRole = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);

    $this->actingAs($user, 'school')
        ->post(route('school.users.store'), schoolDashboardUserPayload([
            'roles' => [$foreignRole->id],
        ]))
        ->assertSessionHasErrors('roles.0');
});

test('store accepts roles submitted as json', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $role = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);
    $payload = schoolDashboardUserPayload([
        'username' => 'json.roles.school.user',
        'email' => 'json.roles.school.user@example.com',
        'roles' => json_encode([$role->id]),
    ]);

    $this->actingAs($user, 'school')
        ->post(route('school.users.store'), $payload)
        ->assertRedirect();

    $createdUser = User::query()->where('username', 'json.roles.school.user')->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->hasRole($role))->toBeTrue();
});

test('authenticated school users can visit the show user page', function () {
    $school = School::factory()->create(['name' => 'Show School']);
    $user = createSchoolManager($school);
    $target = createSchoolPeer($school, [
        'name' => 'Shown User',
        'username' => 'shown.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);
    $target->assignRole($role);

    $this->actingAs($user, 'school')
        ->get(route('school.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('school/users/show')
            ->where('user.name', 'Shown User')
            ->where('user.username', 'shown.user')
            ->where('user.scope.id', UserScope::SCHOOL->value)
            ->where('user.organization.type', 'school')
            ->where('user.organization.organization.school.name', 'Show School')
            ->has('roles')
            ->has('availableStates')
            ->has('can.update')
            ->has('can.delete')
        );
});

test('school users cannot view users from another school', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $otherSchool = School::factory()->create();
    $target = createSchoolPeer($otherSchool);

    $this->actingAs($user, 'school')
        ->get(route('school.users.show', ['user' => $target]))
        ->assertForbidden();
});

test('authenticated school users can visit the edit user page', function () {
    $school = School::factory()->create(['name' => 'Edit School']);
    $user = createSchoolManager($school);
    $target = createSchoolPeer($school, [
        'name' => 'Editable User',
        'username' => 'editable.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);
    $target->assignRole($role);

    $this->actingAs($user, 'school')
        ->get(route('school.users.edit', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('school/users/edit')
            ->where('user.name', 'Editable User')
            ->where('user.username', 'editable.user')
            ->where('user.scope.id', UserScope::SCHOOL->value)
            ->where('user.organization.type', 'school')
            ->where('user.organization.organization.school.name', 'Edit School')
            ->has('groupedRoles')
            ->has('user.role_ids', 1)
        );
});

test('authenticated school users can update a user', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $target = createSchoolPeer($school, [
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);
    $existingRole = Role::findOrCreate('user:role:view', UserScope::SCHOOL->value);
    $newRole = Role::findOrCreate('user:role:update', UserScope::SCHOOL->value);
    $target->assignRole($existingRole);

    $this->actingAs($user, 'school')
        ->put(route('school.users.update', ['user' => $target]), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => [$newRole->id],
        ])
        ->assertRedirect(route('school.users.show', ['user' => $target]));

    $target->refresh();

    expect($target->name)->toBe('Updated Name')
        ->and($target->email)->toBe('updated@example.com')
        ->and($target->hasRole($newRole))->toBeTrue()
        ->and($target->hasRole($existingRole))->toBeFalse();
});

test('update validates required fields', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $target = createSchoolPeer($school);

    $this->actingAs($user, 'school')
        ->put(route('school.users.update', ['user' => $target]), [])
        ->assertSessionHasErrors(['name', 'roles']);
});

test('authenticated school users can delete a user', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $target = createSchoolPeer($school);

    $this->actingAs($user, 'school')
        ->delete(route('school.users.destroy', ['user' => $target]))
        ->assertRedirect(route('school.users.index'));

    $this->assertSoftDeleted($target);
});

test('school users cannot delete users from another school', function () {
    $school = School::factory()->create();
    $user = createSchoolManager($school);
    $otherSchool = School::factory()->create();
    $target = createSchoolPeer($otherSchool);

    $this->actingAs($user, 'school')
        ->delete(route('school.users.destroy', ['user' => $target]))
        ->assertForbidden();

    $this->assertNotSoftDeleted($target);
});
