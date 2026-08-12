<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createWarehouseBookDistributionReportUser(Warehouse $warehouse, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));

    Permission::findOrCreate('book-distribution:view-statistics', UserScope::WAREHOUSE->value);
    $user->givePermissionTo('book-distribution:view-statistics');

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/book-distributions/report', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the warehouse book distribution report page', function () {
    $this->get(route('warehouse.reports.book-distributions.index'))
        ->assertRedirect(route('warehouse.login'));
});

test('users without book distribution statistics permissions cannot view the report page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.index'))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the book distribution report page', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionReportUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/report')
            ->has('monitors', 1)
            ->where('monitors.0.id', $monitor->id)
            ->where('monitors.0.name', $monitor->name)
            ->has('schools', 0)
            ->has('statistics', 0)
            ->where('selected.education_monitor_id', null)
            ->where('selected.school_period_id', null)
            ->where('canPrint', false)
        );
});

test('selecting a monitor loads its warehouse schools on the report page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionReportUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $otherMonitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($monitor, 'monitor')->state(['name' => 'مدرسة الأمل']), 'school')->create();
    SchoolPeriod::factory()->for(School::factory()->for($otherMonitor, 'monitor')->state(['name' => 'مدرسة أخرى']), 'school')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.index', [
            'education_monitor_id' => $monitor->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/report')
            ->has('schools', 1)
            ->where('schools.0.id', $schoolPeriod->id)
            ->where('schools.0.name', $schoolPeriod->display_name)
            ->has('statistics', 0)
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_period_id', null)
            ->where('canPrint', false)
        );
});

test('selecting a school loads grade level statistics on the report page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionReportUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($monitor, 'monitor'), 'school')->create();
    $confirmedGradeLevel = GradeLevel::factory()->create();
    $pendingGradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $schoolPeriod->allGradeLevels()->attach($confirmedGradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);
    $schoolPeriod->allGradeLevels()->attach($pendingGradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $confirmedStudent = Student::factory()->for($schoolPeriod)->create();
    $distributedStudent = Student::factory()->for($schoolPeriod)->create();
    $pendingStudent = Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $confirmedGradeLevel->id,
        'student_id' => $confirmedStudent->id,
        'classroom_id' => null,
    ]);
    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $confirmedGradeLevel->id,
        'student_id' => $distributedStudent->id,
        'classroom_id' => null,
    ]);
    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $pendingGradeLevel->id,
        'student_id' => $pendingStudent->id,
        'classroom_id' => null,
    ]);

    $bookDistribution = BookDistribution::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $confirmedGradeLevel->id,
        'warehouse_id' => $warehouse->id,
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $bookDistribution->id,
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'student_id' => $distributedStudent->id,
    ]);

    $response = $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.index', [
            'education_monitor_id' => $monitor->id,
            'school_period_id' => $schoolPeriod->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/report')
            ->has('statistics', 2)
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_period_id', $schoolPeriod->id)
            ->where('canPrint', true)
        );

    $statistics = collect($response->original->getData()['page']['props']['statistics']);
    $confirmedStatistics = $statistics->firstWhere('id', $confirmedGradeLevel->id);
    $pendingStatistics = $statistics->firstWhere('id', $pendingGradeLevel->id);

    expect($confirmedStatistics)->not->toBeNull()
        ->and($confirmedStatistics['students_count'])->toBe(2)
        ->and($confirmedStatistics['distributed_count'])->toBe(1)
        ->and($confirmedStatistics['pending_count'])->toBe(1)
        ->and($confirmedStatistics['already_distributed'])->toBeTrue()
        ->and($pendingStatistics)->not->toBeNull()
        ->and($pendingStatistics['students_count'])->toBe(1)
        ->and($pendingStatistics['distributed_count'])->toBe(0)
        ->and($pendingStatistics['pending_count'])->toBe(1)
        ->and($pendingStatistics['already_distributed'])->toBeFalse();
});

test('guests are redirected from the book distribution report print page', function () {
    $this->get(route('warehouse.reports.book-distributions.print', [
        'education_monitor_id' => 1,
        'school_period_id' => 1,
    ]))
        ->assertRedirect(route('warehouse.login'));
});

test('users without book distribution statistics permissions cannot print the report', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($monitor, 'monitor'), 'school')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.print', [
            'education_monitor_id' => $monitor->id,
            'school_period_id' => $schoolPeriod->id,
        ]))
        ->assertForbidden();
});

test('print page requires a selected school', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionReportUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.print', [
            'education_monitor_id' => $monitor->id,
        ]))
        ->assertSessionHasErrors('school_period_id');
});

test('authenticated warehouse users can print the book distribution report for a selected school', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionReportUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($monitor, 'monitor')->state(['name' => 'مدرسة الأمل']), 'school')->create();
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $academicYearId = AcademicYear::currentId();

    $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $student = Student::factory()->for($schoolPeriod)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $student->id,
        'classroom_id' => null,
    ]);

    BookDistribution::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'warehouse_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.reports.book-distributions.print', [
            'education_monitor_id' => $monitor->id,
            'school_period_id' => $schoolPeriod->id,
        ]))
        ->assertOk()
        ->assertViewIs('print.warehouse.reports.book-distributions')
        ->assertSee('تقرير إحصائيات توزيع الكُتب المدرسية')
        ->assertSee('مدرسة الأمل')
        ->assertSee('الصف الأول')
        ->assertSee('المجموع')
        ->assertSee($user->name);
});
