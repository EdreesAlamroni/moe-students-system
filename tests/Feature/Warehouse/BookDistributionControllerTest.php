<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createWarehouseBookDistributionUser(Warehouse $warehouse, array $attributes = [], array $permissions = ['book-distribution:view', 'book-distribution:distribute']): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, UserScope::WAREHOUSE->value);
    }

    $user->givePermissionTo($permissions);

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/book-distributions', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the warehouse book distributions page', function () {
    $this->get(route('warehouse.book-distributions.index'))
        ->assertRedirect(route('warehouse.login'));
});

test('users without book distribution permissions cannot view the index', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.index'))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the book distributions index', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/index')
            ->has('monitors', 1)
            ->where('monitors.0.id', $monitor->id)
            ->where('monitors.0.name', $monitor->name)
            ->has('schools', 0)
            ->has('gradeLevels', 0)
            ->where('selected.education_monitor_id', null)
            ->where('selected.school_id', null)
            ->where('can.distribute', true)
        );
});

test('selecting a monitor loads its warehouse schools', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $otherMonitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $school = School::factory()->for($monitor, 'monitor')->create(['name' => 'مدرسة الأمل']);
    School::factory()->for($otherMonitor, 'monitor')->create(['name' => 'مدرسة أخرى']);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.index', [
            'education_monitor_id' => $monitor->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/index')
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('schools.0.name', $school->name)
            ->missing('schools.0.serial_number')
            ->has('gradeLevels', 0)
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_id', null)
        );
});

test('selecting a school loads grade level distribution checklist', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    BookDistribution::factory()->create([
        'academic_year_id' => $academicYearId,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'warehouse_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.index', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/index')
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.id', $gradeLevel->id)
            ->where('gradeLevels.0.name', $gradeLevel->name)
            ->where('gradeLevels.0.students_count', 0)
            ->where('gradeLevels.0.already_distributed', true)
            ->missing('gradeLevels.0.distributed_count')
            ->missing('gradeLevels.0.pending_count')
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_id', $school->id)
        );
});

test('users without distribute permission cannot store book distributions', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionUser($warehouse, permissions: ['book-distribution:view']);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.book-distributions.store'), [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'grade_level_ids' => [$gradeLevel->id],
        ])
        ->assertForbidden();
});

test('warehouse users can confirm book distribution for eligible grade levels', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.book-distributions.store'), [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'grade_level_ids' => [$gradeLevel->id],
        ])
        ->assertRedirect(route('warehouse.book-distributions.index', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
        ]));

    $this->assertDatabaseHas('book_distributions', [
        'academic_year_id' => $academicYearId,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'warehouse_id' => $warehouse->id,
    ]);
});

test('confirming already distributed grade levels does not create duplicates', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    BookDistribution::factory()->create([
        'academic_year_id' => $academicYearId,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'warehouse_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->post(route('warehouse.book-distributions.store'), [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'grade_level_ids' => [$gradeLevel->id],
        ])
        ->assertRedirect(route('warehouse.book-distributions.index', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
        ]));

    expect(BookDistribution::query()
        ->where('school_id', $school->id)
        ->where('grade_level_id', $gradeLevel->id)
        ->count())->toBe(1);
});
