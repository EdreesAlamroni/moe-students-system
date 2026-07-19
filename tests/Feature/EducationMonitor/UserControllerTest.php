<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\User;
use App\ModelStates\User\RequestState\Pending;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationMonitorManager(EducationMonitor $monitor, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ], $attributes));

    foreach (['user:view-any', 'user:view', 'user:create', 'user:update', 'user:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
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
function educationMonitorDashboardUserPayload(array $overrides = []): array
{
    $scope = $overrides['scope'] ?? UserScope::EDUCATION_MONITOR->value;
    $role = Role::findOrCreate('user:role:view', $scope);

    return array_merge([
        'scope' => UserScope::EDUCATION_MONITOR->value,
        'name' => 'New Education Monitor User',
        'username' => 'education.monitor.dashboard.user',
        'email' => 'education.monitor.dashboard.user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationMonitorPeer(EducationMonitor $monitor, array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ], $attributes));
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/users', 'GET'));
});

test('guests are redirected from the education monitor users page', function () {
    $this->get(route('education-monitor.users.index'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without user permissions cannot visit the create user page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.create', ['scope' => UserScope::EDUCATION_MONITOR->value]))
        ->assertForbidden();
});

test('authenticated education monitor users can visit the users index', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $peer = createEducationMonitorPeer($monitor, [
        'name' => 'Peer User',
        'username' => 'peer.user',
    ]);
    $otherMonitor = EducationMonitor::factory()->create();
    createEducationMonitorPeer($otherMonitor, [
        'name' => 'Other Monitor User',
        'username' => 'other.monitor.user',
    ]);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $officeUser = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
        'name' => 'Office User',
        'username' => 'office.user',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/users/index')
            ->has('users.data', 3)
            ->has('scopes', 3)
            ->has('can.create')
        );
});

test('authenticated education monitor users can visit the create education monitor user page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.create', ['scope' => UserScope::EDUCATION_MONITOR->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/users/create')
            ->where('scope.id', UserScope::EDUCATION_MONITOR->value)
            ->where('monitor.id', $monitor->id)
            ->where('monitor.name', $monitor->name)
            ->where('offices', [])
            ->where('schools', [])
            ->has('groupedRoles')
        );
});

test('create education services office user page loads offices for the current monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $otherMonitor = EducationMonitor::factory()->create();
    EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.create', ['scope' => UserScope::EDUCATION_SERVICES_OFFICE->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/users/create')
            ->where('scope.id', UserScope::EDUCATION_SERVICES_OFFICE->value)
            ->has('offices', 1)
            ->where('offices.0.id', $office->id)
            ->where('schools', [])
        );
});

test('create school user page loads schools for the current monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $school = School::factory()->for($monitor, 'monitor')->create();
    $otherMonitor = EducationMonitor::factory()->create();
    School::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.create', ['scope' => UserScope::SCHOOL->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/users/create')
            ->where('scope.id', UserScope::SCHOOL->value)
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('offices', [])
        );
});

test('create page rejects inaccessible scopes', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.create', ['scope' => UserScope::WAREHOUSE->value]))
        ->assertForbidden();
});

test('authenticated education monitor users can store a user for their monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $payload = educationMonitorDashboardUserPayload();

    $response = $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::EDUCATION_MONITOR)
        ->and($createdUser->role)->toBe(UserRole::EMPLOYEE)
        ->and($createdUser->request_state)->toBeInstanceOf(Pending::class)
        ->and($createdUser->organization_id)->toBe($monitor->id)
        ->and($createdUser->organization_type)->toBe(EducationMonitor::class)
        ->and($createdUser->hasRole($payload['roles'][0]))->toBeTrue();

    $response->assertRedirect(route('education-monitor.users.show', ['user' => $createdUser]));
});

test('authenticated education monitor users can store an office user under their monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $payload = educationMonitorDashboardUserPayload([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE->value,
        'education_services_office_id' => $office->id,
        'username' => 'office.dashboard.user',
        'email' => 'office.dashboard.user@example.com',
    ]);

    $response = $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::EDUCATION_SERVICES_OFFICE)
        ->and($createdUser->request_state)->toBeInstanceOf(Pending::class)
        ->and($createdUser->organization_id)->toBe($office->id)
        ->and($createdUser->organization_type)->toBe(EducationServicesOffice::class);

    $response->assertRedirect(route('education-monitor.users.show', ['user' => $createdUser]));
});

test('store rejects offices that do not belong to the current monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $foreignOffice = EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.users.store'), educationMonitorDashboardUserPayload([
            'scope' => UserScope::EDUCATION_SERVICES_OFFICE->value,
            'education_services_office_id' => $foreignOffice->id,
            'username' => 'foreign.office.user',
        ]))
        ->assertSessionHasErrors('education_services_office_id');
});

test('store validates required fields', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);

    $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.users.store'), [])
        ->assertSessionHasErrors(['name', 'username', 'password', 'roles', 'scope']);
});

test('store rejects roles that do not belong to the selected scope', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $foreignRole = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);

    $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.users.store'), educationMonitorDashboardUserPayload([
            'roles' => [$foreignRole->id],
        ]))
        ->assertSessionHasErrors('roles.0');
});

test('authenticated education monitor users can visit the show user page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $target = createEducationMonitorPeer($monitor, [
        'name' => 'Shown User',
        'username' => 'shown.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_MONITOR->value);
    $target->assignRole($role);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/users/show')
            ->where('user.name', 'Shown User')
            ->where('user.username', 'shown.user')
            ->where('user.scope.id', UserScope::EDUCATION_MONITOR->value)
            ->where('user.organization.type', 'education_monitor')
            ->where('user.organization.organization.education_monitor.name', $monitor->name)
            ->has('roles')
            ->has('availableStates')
            ->has('can.update')
            ->has('can.delete')
        );
});

test('education monitor users cannot view users from another monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $target = createEducationMonitorPeer($otherMonitor);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.show', ['user' => $target]))
        ->assertForbidden();
});

test('authenticated education monitor users can visit the edit user page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $target = createEducationMonitorPeer($monitor, [
        'name' => 'Editable User',
        'username' => 'editable.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_MONITOR->value);
    $target->assignRole($role);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.edit', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/users/edit')
            ->where('user.name', 'Editable User')
            ->where('user.username', 'editable.user')
            ->where('user.scope.id', UserScope::EDUCATION_MONITOR->value)
            ->where('user.organization.type', 'education_monitor')
            ->where('user.organization.organization.education_monitor.name', $monitor->name)
            ->has('groupedRoles')
            ->has('user.role_ids', 1)
        );
});

test('authenticated education monitor users can update a user', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $target = createEducationMonitorPeer($monitor, [
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);
    $existingRole = Role::findOrCreate('user:role:view', UserScope::EDUCATION_MONITOR->value);
    $newRole = Role::findOrCreate('user:role:update', UserScope::EDUCATION_MONITOR->value);
    $target->assignRole($existingRole);

    $this->actingAs($user, 'education_monitor')
        ->put(route('education-monitor.users.update', ['user' => $target]), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => [$newRole->id],
        ])
        ->assertRedirect(route('education-monitor.users.show', ['user' => $target]));

    $target->refresh();

    expect($target->name)->toBe('Updated Name')
        ->and($target->email)->toBe('updated@example.com')
        ->and($target->hasRole($newRole))->toBeTrue()
        ->and($target->hasRole($existingRole))->toBeFalse();
});

test('update validates required fields', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $target = createEducationMonitorPeer($monitor);

    $this->actingAs($user, 'education_monitor')
        ->put(route('education-monitor.users.update', ['user' => $target]), [])
        ->assertSessionHasErrors(['name', 'roles']);
});

test('authenticated education monitor users can delete a user', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $target = createEducationMonitorPeer($monitor);

    $this->actingAs($user, 'education_monitor')
        ->delete(route('education-monitor.users.destroy', ['user' => $target]))
        ->assertRedirect(route('education-monitor.users.index'));

    $this->assertSoftDeleted($target);
});

test('education monitor users cannot delete users from another monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $target = createEducationMonitorPeer($otherMonitor);

    $this->actingAs($user, 'education_monitor')
        ->delete(route('education-monitor.users.destroy', ['user' => $target]))
        ->assertForbidden();

    $this->assertNotSoftDeleted($target);
});

test('users index does not query organizations per user when resolving view abilities', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorManager($monitor);

    $offices = EducationServicesOffice::factory()->count(3)->for($monitor, 'monitor')->create();

    foreach ($offices as $index => $office) {
        User::factory()->create([
            'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
            'role' => UserRole::EMPLOYEE,
            'organization_type' => EducationServicesOffice::class,
            'organization_id' => $office->id,
            'name' => "Office User {$index}",
            'username' => "office.user.{$index}",
        ]);
    }

    $schools = School::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $offices->first()->id,
    ]);

    foreach ($schools as $index => $school) {
        User::factory()->create([
            'scope' => UserScope::SCHOOL,
            'role' => UserRole::EMPLOYEE,
            'organization_type' => School::class,
            'organization_id' => $school->id,
            'name' => "School User {$index}",
            'username' => "school.user.{$index}",
        ]);
    }

    $officeEagerLoadQueries = 0;
    $schoolEagerLoadQueries = 0;

    DB::listen(function ($query) use (&$officeEagerLoadQueries, &$schoolEagerLoadQueries): void {
        if (preg_match('/^select\b.+\bfrom\s+[`"]?education_services_offices[`"]?\s+where\s+[`"]?education_services_offices[`"]?[.][`"]?id[`"]?\s+in\s*\(/i', $query->sql) === 1) {
            $officeEagerLoadQueries++;
        }

        if (preg_match('/^select\b.+\bfrom\s+[`"]?schools[`"]?\s+where\s+[`"]?schools[`"]?[.][`"]?id[`"]?\s+in\s*\(/i', $query->sql) === 1) {
            $schoolEagerLoadQueries++;
        }
    });

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 7)
            ->where('users.data', fn ($data) => collect($data)->every(fn ($row) => $row['can']['view'] === true))
        );

    // Morph eager-load should issue one lookup per organization type, not one per user.
    expect($officeEagerLoadQueries)->toBe(1)
        ->and($schoolEagerLoadQueries)->toBe(1);
});
