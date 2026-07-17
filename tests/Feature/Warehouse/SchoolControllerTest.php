<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createWarehouseSchoolUser(Warehouse $warehouse, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));

    foreach (['school:view-any', 'school:view'] as $permission) {
        Permission::findOrCreate($permission, UserScope::WAREHOUSE->value);
    }

    $user->givePermissionTo([
        'school:view-any',
        'school:view',
    ]);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/schools', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the warehouse schools page', function () {
    $this->get(route('warehouse.schools.index'))
        ->assertRedirect(route('warehouse.login'));
});

test('users without school permissions cannot view schools', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.index'))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the schools index', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseSchoolUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $otherMonitor = EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    School::factory()->for($otherMonitor, 'monitor')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/schools/index')
            ->has('schools.data', 1)
            ->where('schools.data.0.uuid', $school->uuid)
            ->where('schools.data.0.monitor.name', $monitor->name)
            ->where('schools.data.0.students_count', 0)
            ->has('monitors', 1)
            ->has('types')
            ->where('filter', [])
            ->missing('can.create')
        );
});

test('authenticated warehouse users can filter schools by education monitor', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseSchoolUser($warehouse);
    $monitorA = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $monitorB = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();

    School::factory()->for($monitorA, 'monitor')->create(['name' => 'مدرسة أ']);
    School::factory()->for($monitorB, 'monitor')->create(['name' => 'مدرسة ب']);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.index', ['filter' => ['education_monitor_id' => $monitorA->id]]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/schools/index')
            ->has('schools.data', 1)
            ->where('schools.data.0.monitor.name', $monitorA->name)
            ->where('filter.education_monitor_id', (string) $monitorA->id)
        );
});

test('authenticated warehouse users can filter schools by name', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseSchoolUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();

    School::factory()->for($monitor, 'monitor')->create(['name' => 'مدرسة الشهداء']);
    School::factory()->for($monitor, 'monitor')->create(['name' => 'مدرسة النصر']);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.index', ['filter' => ['name' => 'الشهداء']]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/schools/index')
            ->has('schools.data', 1)
            ->where('schools.data.0.name', 'مدرسة الشهداء')
            ->where('filter.name', 'الشهداء')
        );
});

test('authenticated warehouse users can visit the show school page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseSchoolUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.show', ['school' => $school]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/schools/show')
            ->where('school.uuid', $school->uuid)
            ->where('school.name', $school->name)
            ->where('school.monitor.name', $monitor->name)
            ->where('school.students_count', 0)
            ->missing('can.update')
            ->missing('can.delete')
        );
});

test('warehouse users cannot view schools from another warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseSchoolUser($warehouse);
    $monitor = EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.show', ['school' => $school]))
        ->assertForbidden();
});

test('warehouse users cannot view schools from another warehouse on the index', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseSchoolUser($warehouse);
    $monitor = EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();
    School::factory()->for($monitor, 'monitor')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.schools.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('warehouse/schools/index')
            ->has('schools.data', 0)
        );
});
