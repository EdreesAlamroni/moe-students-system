<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
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
function createEducationServicesOfficeManager(EducationServicesOffice $office, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ], $attributes));

    foreach (['user:view-any', 'user:view', 'user:create', 'user:update', 'user:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_SERVICES_OFFICE->value);
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
function educationServicesOfficeDashboardUserPayload(array $overrides = []): array
{
    $scope = $overrides['scope'] ?? UserScope::EDUCATION_SERVICES_OFFICE->value;
    $role = Role::findOrCreate('user:role:view', $scope);

    return array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE->value,
        'name' => 'New Education Services Office User',
        'username' => 'education.services.office.dashboard.user',
        'email' => 'education.services.office.dashboard.user@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'roles' => [$role->id],
    ], $overrides);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationServicesOfficePeer(EducationServicesOffice $office, array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ], $attributes));
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-services-office/users', 'GET'));
});

test('guests are redirected from the education services office users page', function () {
    $this->get(route('education-services-office.users.index'))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without user permissions cannot visit the create user page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.create', ['scope' => UserScope::EDUCATION_SERVICES_OFFICE->value]))
        ->assertForbidden();
});

test('authenticated education services office users can visit the users index', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $peer = createEducationServicesOfficePeer($office, [
        'name' => 'Peer User',
        'username' => 'peer.user',
    ]);
    $otherOffice = EducationServicesOffice::factory()->create();
    createEducationServicesOfficePeer($otherOffice, [
        'name' => 'Other Office User',
        'username' => 'other.office.user',
    ]);
    $school = School::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'education_services_office_id' => $office->id,
    ]);
    $schoolUser = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => School::class,
        'organization_id' => $school->id,
        'name' => 'School User',
        'username' => 'school.user',
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/users/index')
            ->has('users.data', 3)
            ->has('scopes', 2)
            ->has('can.create')
        );
});

test('authenticated education services office users can visit the create office user page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.create', ['scope' => UserScope::EDUCATION_SERVICES_OFFICE->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/users/create')
            ->where('scope.id', UserScope::EDUCATION_SERVICES_OFFICE->value)
            ->where('office.id', $office->id)
            ->where('office.name', $office->name)
            ->where('schools', [])
            ->has('groupedRoles')
        );
});

test('create school user page loads schools for the current office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $school = School::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'education_services_office_id' => $office->id,
    ]);
    $otherOffice = EducationServicesOffice::factory()->create();
    School::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'education_services_office_id' => $otherOffice->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.create', ['scope' => UserScope::SCHOOL->value]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/users/create')
            ->where('scope.id', UserScope::SCHOOL->value)
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
        );
});

test('create page rejects inaccessible scopes', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.create', ['scope' => UserScope::WAREHOUSE->value]))
        ->assertForbidden();
});

test('create page rejects education monitor scope', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.create', ['scope' => UserScope::EDUCATION_MONITOR->value]))
        ->assertForbidden();
});

test('authenticated education services office users can store a user for their office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $payload = educationServicesOfficeDashboardUserPayload();

    $response = $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::EDUCATION_SERVICES_OFFICE)
        ->and($createdUser->role)->toBe(UserRole::EMPLOYEE)
        ->and($createdUser->request_state)->toBeInstanceOf(Pending::class)
        ->and($createdUser->organization_id)->toBe($office->id)
        ->and($createdUser->organization_type)->toBe(EducationServicesOffice::class)
        ->and($createdUser->hasRole($payload['roles'][0]))->toBeTrue();

    $response->assertRedirect(route('education-services-office.users.show', ['user' => $createdUser]));
});

test('authenticated education services office users can store a school user under their office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $school = School::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'education_services_office_id' => $office->id,
    ]);
    $payload = educationServicesOfficeDashboardUserPayload([
        'scope' => UserScope::SCHOOL->value,
        'school_id' => $school->id,
        'username' => 'school.dashboard.user',
        'email' => 'school.dashboard.user@example.com',
    ]);

    $response = $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.users.store'), $payload);

    $createdUser = User::query()->where('username', $payload['username'])->first();

    expect($createdUser)->not->toBeNull()
        ->and($createdUser->scope)->toBe(UserScope::SCHOOL)
        ->and($createdUser->request_state)->toBeInstanceOf(Pending::class)
        ->and($createdUser->organization_id)->toBe($school->id)
        ->and($createdUser->organization_type)->toBe(School::class);

    $response->assertRedirect(route('education-services-office.users.show', ['user' => $createdUser]));
});

test('store rejects schools that do not belong to the current office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $foreignSchool = School::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'education_services_office_id' => $otherOffice->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.users.store'), educationServicesOfficeDashboardUserPayload([
            'scope' => UserScope::SCHOOL->value,
            'school_id' => $foreignSchool->id,
            'username' => 'foreign.school.user',
        ]))
        ->assertSessionHasErrors('school_id');
});

test('store validates required fields', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.users.store'), [])
        ->assertSessionHasErrors(['name', 'username', 'password', 'roles', 'scope']);
});

test('store rejects roles that do not belong to the selected scope', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $foreignRole = Role::findOrCreate('user:role:view', UserScope::ADMINISTRATION->value);

    $this->actingAs($user, 'education_services_office')
        ->post(route('education-services-office.users.store'), educationServicesOfficeDashboardUserPayload([
            'roles' => [$foreignRole->id],
        ]))
        ->assertSessionHasErrors('roles.0');
});

test('authenticated education services office users can visit the show user page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $target = createEducationServicesOfficePeer($office, [
        'name' => 'Shown User',
        'username' => 'shown.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_SERVICES_OFFICE->value);
    $target->assignRole($role);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.show', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/users/show')
            ->where('user.name', 'Shown User')
            ->where('user.username', 'shown.user')
            ->where('user.scope.id', UserScope::EDUCATION_SERVICES_OFFICE->value)
            ->where('user.organization.type', 'education_services_office')
            ->where('user.organization.organization.education_services_office.name', $office->name)
            ->has('roles')
            ->has('availableStates')
            ->has('can.update')
            ->has('can.delete')
        );
});

test('education services office users cannot view users from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $target = createEducationServicesOfficePeer($otherOffice);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.show', ['user' => $target]))
        ->assertForbidden();
});

test('authenticated education services office users can visit the edit user page', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $target = createEducationServicesOfficePeer($office, [
        'name' => 'Editable User',
        'username' => 'editable.user',
    ]);
    $role = Role::findOrCreate('user:role:view', UserScope::EDUCATION_SERVICES_OFFICE->value);
    $target->assignRole($role);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.edit', ['user' => $target]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-services-office/users/edit')
            ->where('user.name', 'Editable User')
            ->where('user.username', 'editable.user')
            ->where('user.scope.id', UserScope::EDUCATION_SERVICES_OFFICE->value)
            ->where('user.organization.type', 'education_services_office')
            ->where('user.organization.organization.education_services_office.name', $office->name)
            ->has('groupedRoles')
            ->has('user.role_ids', 1)
        );
});

test('authenticated education services office users can update a user', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $target = createEducationServicesOfficePeer($office, [
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);
    $existingRole = Role::findOrCreate('user:role:view', UserScope::EDUCATION_SERVICES_OFFICE->value);
    $newRole = Role::findOrCreate('user:role:update', UserScope::EDUCATION_SERVICES_OFFICE->value);
    $target->assignRole($existingRole);

    $this->actingAs($user, 'education_services_office')
        ->put(route('education-services-office.users.update', ['user' => $target]), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'roles' => [$newRole->id],
        ])
        ->assertRedirect(route('education-services-office.users.show', ['user' => $target]));

    $target->refresh();

    expect($target->name)->toBe('Updated Name')
        ->and($target->email)->toBe('updated@example.com')
        ->and($target->hasRole($newRole))->toBeTrue()
        ->and($target->hasRole($existingRole))->toBeFalse();
});

test('update validates required fields', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $target = createEducationServicesOfficePeer($office);

    $this->actingAs($user, 'education_services_office')
        ->put(route('education-services-office.users.update', ['user' => $target]), [])
        ->assertSessionHasErrors(['name', 'roles']);
});

test('authenticated education services office users can delete a user', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $target = createEducationServicesOfficePeer($office);

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.users.destroy', ['user' => $target]))
        ->assertRedirect(route('education-services-office.users.index'));

    $this->assertSoftDeleted($target);
});

test('education services office users cannot delete users from another office', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);
    $otherOffice = EducationServicesOffice::factory()->create();
    $target = createEducationServicesOfficePeer($otherOffice);

    $this->actingAs($user, 'education_services_office')
        ->delete(route('education-services-office.users.destroy', ['user' => $target]))
        ->assertForbidden();

    $this->assertNotSoftDeleted($target);
});

test('users index does not query schools per user when resolving view abilities', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeManager($office);

    $schools = School::factory()->count(5)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'education_services_office_id' => $office->id,
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

    $schoolEagerLoadQueries = 0;

    DB::listen(function ($query) use (&$schoolEagerLoadQueries): void {
        if (preg_match('/^select\b.+\bfrom\s+[`"]?schools[`"]?\s+where\s+[`"]?schools[`"]?[.][`"]?id[`"]?\s+in\s*\(/i', $query->sql) === 1) {
            $schoolEagerLoadQueries++;
        }
    });

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 6)
            ->where('users.data', fn ($data) => collect($data)->every(fn ($row) => $row['can']['view'] === true))
        );

    // Morph eager-load should issue a single schools lookup, not one per school user.
    expect($schoolEagerLoadQueries)->toBe(1);
});
