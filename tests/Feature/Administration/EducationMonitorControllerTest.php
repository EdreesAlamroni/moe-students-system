<?php

use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\Municipal;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

/**
 * Wrap a persisted monitor in a partial mock so route-model binding resolves it,
 * allowing the instance-level hasAnyRelations() check to be controlled in tests.
 */
function bindEducationMonitorBinding(EducationMonitor $monitor, bool $hasAnyRelations): EducationMonitor
{
    /** @var EducationMonitor&MockInterface $mock */
    $mock = Mockery::mock($monitor)->makePartial();
    $mock->shouldReceive('hasAnyRelations')->andReturn($hasAnyRelations);
    $mock->shouldReceive('resolveRouteBinding')->andReturn($mock);

    app()->instance(EducationMonitor::class, $mock);

    return $mock;
}

function createEducationMonitorAdminUser(): User
{
    $user = User::factory()->create();

    foreach (['education-monitor:view-any', 'education-monitor:view', 'education-monitor:create', 'education-monitor:update', 'education-monitor:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
    }

    $user->givePermissionTo([
        'education-monitor:view-any',
        'education-monitor:view',
        'education-monitor:create',
        'education-monitor:update',
        'education-monitor:delete',
    ]);

    return $user;
}

function monitorPayload(Municipal $municipal, array $overrides = []): array
{
    return array_merge([
        'municipal_id' => $municipal->id,
        'phone_number' => '0912345678',
        'whatsapp_phone_number' => '0921234567',
        'address' => 'Benghazi - Libya',
        'add_location_to_map' => false,
        'latitude' => null,
        'longitude' => null,
    ], $overrides);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/administration/education-monitors', 'GET'));
});

test('guests are redirected from the education monitors page', function () {
    $this->get(route('administration.education-monitors.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without education monitor permissions cannot view education monitors', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-monitors.index'))
        ->assertForbidden();
});

test('authenticated users can visit the education monitors page', function () {
    $user = createEducationMonitorAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-monitors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-monitors/index')
            ->has('monitors.data', 1)
            ->where('monitors.data.0.name', $monitor->name)
            ->where('monitors.data.0.students_count', 0)
            ->where('filter', [])
        );
});

test('authenticated users can visit the create education monitor page', function () {
    $user = createEducationMonitorAdminUser();
    Municipal::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-monitors.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-monitors/create')
            ->has('municipals')
        );
});

test('authenticated users can store an education monitor and name is generated from municipal', function () {
    $user = createEducationMonitorAdminUser();
    $municipal = Municipal::factory()->create(['name' => 'بنغازي']);

    $this->actingAs($user, 'administration')
        ->post(route('administration.education-monitors.store'), monitorPayload($municipal, [
            'name' => 'اسم مُرسل من الطلب',
        ]))
        ->assertRedirect();

    $monitor = EducationMonitor::query()->firstOrFail();

    expect($monitor->name)->toBe('مُراقبة التّربية والتّعليم بنغازي');

    $this->assertDatabaseHas('education_monitors', [
        'id' => $monitor->id,
        'municipal_id' => $municipal->id,
    ]);
});

test('store validates municipal uniqueness across education monitors', function () {
    $user = createEducationMonitorAdminUser();
    $municipal = Municipal::factory()->create();
    EducationMonitor::factory()->for($municipal, 'municipal')->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.education-monitors.store'), monitorPayload($municipal))
        ->assertSessionHasErrors('municipal_id');
});

test('authenticated users can visit the show education monitor page', function () {
    $user = createEducationMonitorAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-monitors.show', ['monitor' => $monitor]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-monitors/show')
            ->where('monitor.name', $monitor->name)
            ->where('monitor.students_count', 0)
            ->has('offices.data', 1)
            ->where('offices.data.0.uuid', $office->uuid)
        );
});

test('authenticated users can visit the edit education monitor page', function () {
    $user = createEducationMonitorAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-monitors.edit', ['monitor' => $monitor]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-monitors/edit')
            ->where('monitor.uuid', $monitor->uuid)
            ->has('municipals')
        );
});

test('authenticated users can update an education monitor and regenerated name follows selected municipal', function () {
    $user = createEducationMonitorAdminUser();
    $oldMunicipal = Municipal::factory()->create(['name' => 'طرابلس']);
    $newMunicipal = Municipal::factory()->create(['name' => 'بنغازي']);
    $monitor = EducationMonitor::factory()->for($oldMunicipal, 'municipal')->create([
        'phone_number' => '0911111111',
        'whatsapp_phone_number' => '0921111111',
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.education-monitors.update', ['monitor' => $monitor]), monitorPayload($newMunicipal, [
            'name' => 'اسم آخر',
            'phone_number' => '0931234567',
            'whatsapp_phone_number' => '0941234567',
        ]))
        ->assertRedirect(route('administration.education-monitors.show', ['monitor' => $monitor]));

    $monitor->refresh();

    expect($monitor->name)->toBe('مُراقبة التّربية والتّعليم بنغازي')
        ->and($monitor->municipal_id)->toBe($newMunicipal->id);
});

test('authenticated users can delete an education monitor without relations', function () {
    $user = createEducationMonitorAdminUser();
    $monitor = bindEducationMonitorBinding(EducationMonitor::factory()->create(), hasAnyRelations: false);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.education-monitors.destroy', ['monitor' => $monitor]))
        ->assertRedirect(route('administration.education-monitors.index'));

    $this->assertSoftDeleted('education_monitors', ['id' => $monitor->id]);
});

test('education monitors with relations cannot be deleted', function () {
    $user = createEducationMonitorAdminUser();
    $monitor = bindEducationMonitorBinding(EducationMonitor::factory()->create(), hasAnyRelations: true);

    $this->actingAs($user, 'administration')
        ->delete(route('administration.education-monitors.destroy', ['monitor' => $monitor]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('education_monitors', ['id' => $monitor->id]);
});
