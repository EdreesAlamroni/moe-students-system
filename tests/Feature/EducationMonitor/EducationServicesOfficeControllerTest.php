<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

/**
 * Wrap a persisted office in a partial mock so route-model binding resolves it,
 * allowing the instance-level hasAnyRelations() check to be controlled in tests.
 */
function bindEducationMonitorEducationServicesOfficeBinding(EducationServicesOffice $office, bool $hasAnyRelations): EducationServicesOffice
{
    /** @var EducationServicesOffice&MockInterface $mock */
    $mock = Mockery::mock($office)->makePartial();
    $mock->shouldReceive('hasAnyRelations')->andReturn($hasAnyRelations);
    $mock->shouldReceive('resolveRouteBinding')->andReturn($mock);

    app()->instance(EducationServicesOffice::class, $mock);

    return $mock;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createEducationMonitorOfficeManager(EducationMonitor $monitor, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ], $attributes));

    foreach (['education-services-office:view-any', 'education-services-office:view', 'education-services-office:create', 'education-services-office:update', 'education-services-office:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
    }

    $user->givePermissionTo([
        'education-services-office:view-any',
        'education-services-office:view',
        'education-services-office:create',
        'education-services-office:update',
        'education-services-office:delete',
    ]);

    return $user;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function educationMonitorOfficePayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'مكتب الخدمات التعليمية بنغازي المركز',
        'phone_number' => '0912345678',
        'whatsapp_phone_number' => '0921234567',
        'address' => 'Benghazi - Libya',
        'add_location_to_map' => false,
        'latitude' => null,
        'longitude' => null,
    ], $overrides);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/education-services-offices', 'GET'));
});

test('guests are redirected from the education services offices page', function () {
    $this->get(route('education-monitor.education-services-offices.index'))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without education services office permissions cannot view education services offices', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.index'))
        ->assertForbidden();
});

test('authenticated users can visit the education services offices page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $otherMonitor = EducationMonitor::factory()->create();
    EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/education-services-offices/index')
            ->has('offices.data', 1)
            ->where('offices.data.0.uuid', $office->uuid)
            ->where('offices.data.0.students_count', 0)
            ->where('filter', [])
        );
});

test('authenticated users can visit the create education services office page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/education-services-offices/create')
        );
});

test('authenticated users can store an education services office', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);

    $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.education-services-offices.store'), educationMonitorOfficePayload())
        ->assertRedirect();

    $office = EducationServicesOffice::query()->firstOrFail();

    $this->assertDatabaseHas('education_services_offices', [
        'id' => $office->id,
        'education_monitor_id' => $monitor->id,
        'name' => 'مكتب الخدمات التعليمية بنغازي المركز',
    ]);
});

test('store associates the office with the current education monitor even if another monitor id is submitted', function () {
    $monitor = EducationMonitor::factory()->create();
    $otherMonitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);

    $this->actingAs($user, 'education_monitor')
        ->post(route('education-monitor.education-services-offices.store'), educationMonitorOfficePayload([
            'education_monitor_id' => $otherMonitor->id,
        ]))
        ->assertRedirect();

    $office = EducationServicesOffice::query()->firstOrFail();

    expect($office->education_monitor_id)->toBe($monitor->id);
});

test('authenticated users can visit the show education services office page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.show', ['office' => $office]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/education-services-offices/show')
            ->where('office.name', $office->name)
            ->where('office.monitor.name', $monitor->name)
            ->where('office.students_count', 0)
        );
});

test('users cannot view education services offices from another monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.show', ['office' => $office]))
        ->assertForbidden();
});

test('authenticated users can visit the edit education services office page', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.edit', ['office' => $office]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('education-monitor/education-services-offices/edit')
            ->where('office.uuid', $office->uuid)
        );
});

test('users cannot edit education services offices from another monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.education-services-offices.edit', ['office' => $office]))
        ->assertForbidden();
});

test('authenticated users can update an education services office', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create([
        'name' => 'مكتب قديم',
        'phone_number' => '0911111111',
        'whatsapp_phone_number' => '0921111111',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->put(route('education-monitor.education-services-offices.update', ['office' => $office]), educationMonitorOfficePayload([
            'name' => 'مكتب جديد',
            'phone_number' => '0931234567',
            'whatsapp_phone_number' => '0941234567',
        ]))
        ->assertRedirect(route('education-monitor.education-services-offices.show', ['office' => $office]));

    $office->refresh();

    expect($office->name)->toBe('مكتب جديد')
        ->and($office->education_monitor_id)->toBe($monitor->id);
});

test('update does not allow moving an office to another education monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $otherMonitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create([
        'name' => 'مكتب قديم',
    ]);

    $this->actingAs($user, 'education_monitor')
        ->put(route('education-monitor.education-services-offices.update', ['office' => $office]), educationMonitorOfficePayload([
            'name' => 'مكتب جديد',
            'education_monitor_id' => $otherMonitor->id,
        ]))
        ->assertRedirect(route('education-monitor.education-services-offices.show', ['office' => $office]));

    $office->refresh();

    expect($office->name)->toBe('مكتب جديد')
        ->and($office->education_monitor_id)->toBe($monitor->id);
});

test('authenticated users can delete an education services office without relations', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = bindEducationMonitorEducationServicesOfficeBinding(
        EducationServicesOffice::factory()->for($monitor, 'monitor')->create(),
        hasAnyRelations: false,
    );

    $this->actingAs($user, 'education_monitor')
        ->delete(route('education-monitor.education-services-offices.destroy', ['office' => $office]))
        ->assertRedirect(route('education-monitor.education-services-offices.index'));

    $this->assertSoftDeleted('education_services_offices', ['id' => $office->id]);
});

test('education services offices with relations cannot be deleted', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $office = bindEducationMonitorEducationServicesOfficeBinding(
        EducationServicesOffice::factory()->for($monitor, 'monitor')->create(),
        hasAnyRelations: true,
    );

    $this->actingAs($user, 'education_monitor')
        ->delete(route('education-monitor.education-services-offices.destroy', ['office' => $office]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('education_services_offices', ['id' => $office->id]);
});

test('users cannot delete education services offices from another monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorOfficeManager($monitor);
    $otherMonitor = EducationMonitor::factory()->create();
    $office = bindEducationMonitorEducationServicesOfficeBinding(
        EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create(),
        hasAnyRelations: false,
    );

    $this->actingAs($user, 'education_monitor')
        ->delete(route('education-monitor.education-services-offices.destroy', ['office' => $office]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('education_services_offices', ['id' => $office->id]);
});
