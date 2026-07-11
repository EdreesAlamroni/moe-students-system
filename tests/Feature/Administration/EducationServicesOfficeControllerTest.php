<?php

use App\Enums\UserScope;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

function createEducationServicesOfficeAdminUser(): User
{
    $user = User::factory()->create();

    foreach (['education-services-office:view-any', 'education-services-office:view', 'education-services-office:create', 'education-services-office:update', 'education-services-office:delete'] as $permission) {
        Permission::findOrCreate($permission, UserScope::ADMINISTRATION->value);
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

function officePayload(EducationMonitor $monitor, array $overrides = []): array
{
    return array_merge([
        'education_monitor_id' => $monitor->id,
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
    PolicyRegistrar::register(Request::create('/administration/education-services-offices', 'GET'));
});

test('guests are redirected from the education services offices page', function () {
    $this->get(route('administration.education-services-offices.index'))
        ->assertRedirect(route('administration.login'));
});

test('users without education services office permissions cannot view education services offices', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-services-offices.index'))
        ->assertForbidden();
});

test('authenticated users can visit the education services offices page', function () {
    $user = createEducationServicesOfficeAdminUser();
    $monitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-services-offices.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-services-offices/index')
            ->has('offices.data', 1)
            ->where('offices.data.0.uuid', $office->uuid)
            ->where('offices.data.0.monitor.name', $monitor->name)
            ->where('offices.data.0.students_count', 0)
            ->has('monitors')
            ->where('filter', [])
        );
});

test('authenticated users can visit the create education services office page', function () {
    $user = createEducationServicesOfficeAdminUser();
    EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-services-offices.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-services-offices/create')
            ->has('monitors')
        );
});

test('authenticated users can store an education services office', function () {
    $user = createEducationServicesOfficeAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.education-services-offices.store'), officePayload($monitor))
        ->assertRedirect();

    $office = EducationServicesOffice::query()->firstOrFail();

    $this->assertDatabaseHas('education_services_offices', [
        'id' => $office->id,
        'education_monitor_id' => $monitor->id,
        'name' => 'مكتب الخدمات التعليمية بنغازي المركز',
    ]);
});

test('store validates education monitor as required', function () {
    $user = createEducationServicesOfficeAdminUser();
    $monitor = EducationMonitor::factory()->create();

    $this->actingAs($user, 'administration')
        ->post(route('administration.education-services-offices.store'), officePayload($monitor, [
            'education_monitor_id' => null,
        ]))
        ->assertSessionHasErrors('education_monitor_id');
});

test('authenticated users can visit the show education services office page', function () {
    $user = createEducationServicesOfficeAdminUser();
    $office = EducationServicesOffice::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-services-offices.show', ['office' => $office]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-services-offices/show')
            ->where('office.name', $office->name)
            ->where('office.students_count', 0)
        );
});

test('authenticated users can visit the edit education services office page', function () {
    $user = createEducationServicesOfficeAdminUser();
    $office = EducationServicesOffice::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.education-services-offices.edit', ['office' => $office]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/education-services-offices/edit')
            ->where('office.uuid', $office->uuid)
            ->has('monitors')
        );
});

test('authenticated users can update an education services office', function () {
    $user = createEducationServicesOfficeAdminUser();
    $oldMonitor = EducationMonitor::factory()->create();
    $newMonitor = EducationMonitor::factory()->create();
    $office = EducationServicesOffice::factory()->for($oldMonitor, 'monitor')->create([
        'name' => 'مكتب قديم',
        'phone_number' => '0911111111',
        'whatsapp_phone_number' => '0921111111',
    ]);

    $this->actingAs($user, 'administration')
        ->put(route('administration.education-services-offices.update', ['office' => $office]), officePayload($newMonitor, [
            'name' => 'مكتب جديد',
            'phone_number' => '0931234567',
            'whatsapp_phone_number' => '0941234567',
        ]))
        ->assertRedirect(route('administration.education-services-offices.show', ['office' => $office]));

    $office->refresh();

    expect($office->name)->toBe('مكتب جديد')
        ->and($office->education_monitor_id)->toBe($newMonitor->id);
});

test('authenticated users can delete an education services office', function () {
    $user = createEducationServicesOfficeAdminUser();
    $office = EducationServicesOffice::factory()->create();

    $this->actingAs($user, 'administration')
        ->delete(route('administration.education-services-offices.destroy', ['office' => $office]))
        ->assertRedirect(route('administration.education-services-offices.index'));

    $this->assertSoftDeleted($office);
});
