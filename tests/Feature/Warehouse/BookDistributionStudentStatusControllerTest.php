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
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

/**
 * @param  array<string, mixed>  $attributes
 */
function createWarehouseBookDistributionStudentStatusUser(Warehouse $warehouse, array $attributes = []): User
{
    $user = User::factory()->create(array_merge([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ], $attributes));

    Permission::findOrCreate('book-distribution:view', UserScope::WAREHOUSE->value);
    $user->givePermissionTo('book-distribution:view');

    return $user;
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/book-distributions/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected from the warehouse book distribution student status page', function () {
    $this->get(route('warehouse.book-distributions.students'))
        ->assertRedirect(route('warehouse.login'));
});

test('users without book distribution permissions cannot view the student status page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.students'))
        ->assertForbidden();
});

test('authenticated warehouse users can visit the book distribution student status page', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionStudentStatusUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.students'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/student-status')
            ->has('monitors', 1)
            ->where('monitors.0.id', $monitor->id)
            ->where('monitors.0.name', $monitor->name)
            ->has('schools', 0)
            ->has('gradeLevels', 0)
            ->missing('students')
            ->missing('registrationStatuses')
            ->missing('nationalities')
            ->where('selected.education_monitor_id', null)
            ->where('selected.school_id', null)
            ->where('selected.grade_level_id', null)
        );
});

test('selecting a monitor loads its warehouse schools on the student status page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionStudentStatusUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $otherMonitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    EducationServicesOffice::factory()->for($otherMonitor, 'monitor')->create();

    $school = School::factory()->for($monitor, 'monitor')->create(['name' => 'مدرسة الأمل']);
    School::factory()->for($otherMonitor, 'monitor')->create(['name' => 'مدرسة أخرى']);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.students', [
            'education_monitor_id' => $monitor->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/student-status')
            ->has('schools', 1)
            ->where('schools.0.id', $school->id)
            ->where('schools.0.name', $school->name)
            ->has('gradeLevels', 0)
            ->missing('students')
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_id', null)
            ->where('selected.grade_level_id', null)
        );
});

test('selecting a school loads its grade levels on the student status page', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionStudentStatusUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.students', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/student-status')
            ->has('gradeLevels', 1)
            ->where('gradeLevels.0.id', $gradeLevel->id)
            ->where('gradeLevels.0.name', $gradeLevel->name)
            ->missing('students')
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_id', $school->id)
            ->where('selected.grade_level_id', null)
        );
});

test('selecting a grade level loads students with distribution status', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionStudentStatusUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $distributedStudent = Student::factory()->for($school)->create();
    $pendingStudent = Student::factory()->for($school)->create();

    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $distributedStudent->id,
    ]);
    StudentEnrollment::factory()->create([
        'academic_year_id' => $academicYearId,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'student_id' => $pendingStudent->id,
    ]);

    $bookDistribution = BookDistribution::factory()->create([
        'academic_year_id' => $academicYearId,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'warehouse_id' => $warehouse->id,
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $bookDistribution->id,
        'school_id' => $school->id,
        'student_id' => $distributedStudent->id,
        'academic_year_id' => $academicYearId,
    ]);

    $response = $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.students', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'grade_level_id' => $gradeLevel->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/student-status')
            ->has('students.data', 2)
            ->has('registrationStatuses')
            ->has('nationalities')
            ->where('selected.education_monitor_id', $monitor->id)
            ->where('selected.school_id', $school->id)
            ->where('selected.grade_level_id', $gradeLevel->id)
        );

    $students = collect($response->original->getData()['page']['props']['students']['data']);
    $distributedRow = $students->firstWhere('uuid', $distributedStudent->uuid);
    $pendingRow = $students->firstWhere('uuid', $pendingStudent->uuid);

    expect($distributedRow)->not->toBeNull()
        ->and($distributedRow['already_distributed'])->toBeTrue()
        ->and($pendingRow)->not->toBeNull()
        ->and($pendingRow['already_distributed'])->toBeFalse();
});

test('student status page filters students by name', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseBookDistributionStudentStatusUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    EducationServicesOffice::factory()->for($monitor, 'monitor')->create();
    $school = School::factory()->for($monitor, 'monitor')->create();
    $gradeLevel = GradeLevel::factory()->create();
    $academicYearId = AcademicYear::currentId();

    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => $academicYearId,
    ]);

    $matchingStudent = Student::factory()->for($school)->create([
        'first_name' => 'أحمد',
        'father_name' => 'محمد',
        'grandfather_name' => 'علي',
        'surname' => 'السالم',
    ]);
    $otherStudent = Student::factory()->for($school)->create([
        'first_name' => 'خالد',
        'father_name' => 'سالم',
        'grandfather_name' => 'عمر',
        'surname' => 'الجبل',
    ]);

    foreach ([$matchingStudent, $otherStudent] as $student) {
        StudentEnrollment::factory()->create([
            'academic_year_id' => $academicYearId,
            'school_id' => $school->id,
            'grade_level_id' => $gradeLevel->id,
            'student_id' => $student->id,
        ]);
    }

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.book-distributions.students', [
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'grade_level_id' => $gradeLevel->id,
            'filter' => [
                'name' => 'أحمد',
            ],
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/book-distributions/student-status')
            ->has('students.data', 1)
            ->where('students.data.0.uuid', $matchingStudent->uuid)
        );
});
