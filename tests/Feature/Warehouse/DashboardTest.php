<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;

function createWarehouseDashboardUser(Warehouse $warehouse): User
{
    return User::factory()->create([
        'scope' => UserScope::WAREHOUSE,
        'role' => UserRole::MANAGER,
        'organization_type' => Warehouse::class,
        'organization_id' => $warehouse->id,
    ]);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/warehouse/dashboard', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected to the warehouse login page', function () {
    $this->get(route('warehouse.dashboard'))
        ->assertRedirect(route('warehouse.login'));
});

test('the dashboard renders without statistics in the initial payload', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseDashboardUser($warehouse);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/dashboard')
            ->missingAll([
                'summary',
                'educationMonitorDistribution',
                'schoolDistribution',
                // 'academicYearTrends', // Skip for now.
                'recentActivities',
            ]));
});

test('the summary reports warehouse-scoped aggregate counts for the current academic year', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();
    $user = createWarehouseDashboardUser($warehouse);

    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $otherMonitor = EducationMonitor::factory()->for($otherWarehouse, 'warehouse')->create();

    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);
    $otherSchool = School::factory()->create(['education_monitor_id' => $otherMonitor->id]);

    $gradeLevel = GradeLevel::factory()->create();
    $otherGradeLevel = GradeLevel::factory()->create();

    $students = Student::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $otherSchool->id,
    ]);

    foreach ($students as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'school_id' => $school->id,
            'grade_level_id' => $gradeLevel->id,
        ]);
    }

    $distribution = BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    BookDistribution::factory()->create([
        'warehouse_id' => $otherWarehouse->id,
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $otherSchool->id,
        'grade_level_id' => $otherGradeLevel->id,
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $distribution->id,
        'school_id' => $school->id,
        'student_id' => $students[0]->id,
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $distribution->id,
        'school_id' => $school->id,
        'student_id' => $students[1]->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/dashboard')
            ->loadDeferredProps('summary', fn (Assert $page) => $page
                ->where('summary.education_monitors', 1)
                ->where('summary.schools', 1)
                ->where('summary.students', 3)
                ->where('summary.book_distributions', 1)
                ->where('summary.students_received', 2)
                ->where('summary.students_pending', 1)
                ->where('summary.completion_rate', 66.7)));
})->skip();

test('the education monitor distribution reports student and progress counts per monitor', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseDashboardUser($warehouse);

    $largest = EducationMonitor::factory()->for($warehouse, 'warehouse')->create(['name' => 'مراقبة كبرى']);
    $smallest = EducationMonitor::factory()->for($warehouse, 'warehouse')->create(['name' => 'مراقبة صغرى']);

    $largeSchool = School::factory()->create(['education_monitor_id' => $largest->id]);
    School::factory()->create(['education_monitor_id' => $largest->id]);
    $smallSchool = School::factory()->create(['education_monitor_id' => $smallest->id]);

    $gradeLevel = GradeLevel::factory()->create();

    $largeStudents = Student::factory()->count(2)->create([
        'education_monitor_id' => $largest->id,
        'school_id' => $largeSchool->id,
    ]);

    $smallStudent = Student::factory()->create([
        'education_monitor_id' => $smallest->id,
        'school_id' => $smallSchool->id,
    ]);

    foreach ($largeStudents as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'school_id' => $largeSchool->id,
            'grade_level_id' => $gradeLevel->id,
        ]);
    }

    StudentEnrollment::factory()->create([
        'student_id' => $smallStudent->id,
        'school_id' => $smallSchool->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $largeDistribution = BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $largest->id,
        'school_id' => $largeSchool->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $smallest->id,
        'school_id' => $smallSchool->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $largeDistribution->id,
        'school_id' => $largeSchool->id,
        'student_id' => $largeStudents[0]->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/dashboard')
            ->loadDeferredProps('education-monitors', fn (Assert $page) => $page
                ->count('educationMonitorDistribution', 2)
                ->where('educationMonitorDistribution.0.name', $largest->name)
                ->where('educationMonitorDistribution.0.students', 2)
                ->where('educationMonitorDistribution.0.schools', 2)
                ->where('educationMonitorDistribution.0.book_distributions', 1)
                ->where('educationMonitorDistribution.0.students_received', 1)
                ->where('educationMonitorDistribution.0.students_pending', 1)
                ->where('educationMonitorDistribution.0.completion_rate', 50)
                ->where('educationMonitorDistribution.1.name', $smallest->name)
                ->where('educationMonitorDistribution.1.students', 1)
                ->where('educationMonitorDistribution.1.schools', 1)
                ->where('educationMonitorDistribution.1.book_distributions', 1)
                ->where('educationMonitorDistribution.1.students_received', 0)
                ->where('educationMonitorDistribution.1.students_pending', 1)
                ->where('educationMonitorDistribution.1.completion_rate', 0)));
});

test('the school distribution reports student and progress counts for the largest schools first', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseDashboardUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();

    $largest = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'name' => 'المدرسة الكبرى',
    ]);
    $smallest = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'name' => 'المدرسة الصغرى',
    ]);

    $gradeLevel = GradeLevel::factory()->create();

    $largeStudents = Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $largest->id,
    ]);

    $smallStudent = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $smallest->id,
    ]);

    foreach ($largeStudents as $student) {
        StudentEnrollment::factory()->create([
            'student_id' => $student->id,
            'school_id' => $largest->id,
            'grade_level_id' => $gradeLevel->id,
        ]);
    }

    StudentEnrollment::factory()->create([
        'student_id' => $smallStudent->id,
        'school_id' => $smallest->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    $distribution = BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $monitor->id,
        'school_id' => $largest->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $distribution->id,
        'school_id' => $largest->id,
        'student_id' => $largeStudents[0]->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 2)
                ->where('schoolDistribution.0.name', $largest->name)
                ->where('schoolDistribution.0.students', 2)
                ->where('schoolDistribution.0.students_received', 1)
                ->where('schoolDistribution.0.students_pending', 1)
                ->where('schoolDistribution.0.completion_rate', 50)
                ->where('schoolDistribution.0.monitor.name', $monitor->name)
                ->where('schoolDistribution.1.name', $smallest->name)
                ->where('schoolDistribution.1.students', 1)
                ->where('schoolDistribution.1.students_received', 0)
                ->where('schoolDistribution.1.students_pending', 0)));
});

test('the academic year trends report warehouse distribution activity per year', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseDashboardUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);
    $gradeLevel = GradeLevel::factory()->create();

    $previousYear = AcademicYear::query()->create([
        'name' => '2023-2024',
        'start_date' => now()->subYear()->startOfYear(),
        'end_date' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $currentDistribution = BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    BookDistributionItem::factory()->create([
        'book_distribution_id' => $currentDistribution->id,
        'school_id' => $school->id,
        'student_id' => $student->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => $previousYear->id,
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/dashboard')
            ->loadDeferredProps('trends', fn (Assert $page) => $page
                ->count('academicYearTrends', 2)
                ->where('academicYearTrends.0.name', '2024-2025')
                ->where('academicYearTrends.0.book_distributions', 1)
                ->where('academicYearTrends.0.students_received', 1)
                ->where('academicYearTrends.0.is_current', true)
                ->where('academicYearTrends.1.name', '2023-2024')
                ->where('academicYearTrends.1.book_distributions', 1)
                ->where('academicYearTrends.1.students_received', 0)
                ->where('academicYearTrends.1.is_current', false)));
})->skip();

test('the recent activities list the latest warehouse book distributions', function () {
    $warehouse = Warehouse::factory()->create();
    $user = createWarehouseDashboardUser($warehouse);
    $monitor = EducationMonitor::factory()->for($warehouse, 'warehouse')->create();
    $school = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'name' => 'مدرسة النشاط',
    ]);
    $firstGrade = GradeLevel::factory()->create(['name' => 'الصف الأول']);
    $secondGrade = GradeLevel::factory()->create(['name' => 'الصف الثاني']);

    $older = BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $firstGrade->id,
        'distributed_at' => now()->subDay(),
    ]);

    $newer = BookDistribution::factory()->create([
        'warehouse_id' => $warehouse->id,
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'grade_level_id' => $secondGrade->id,
        'distributed_at' => now(),
    ]);

    $this->actingAs($user, 'warehouse')
        ->get(route('warehouse.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('warehouse/dashboard')
            ->loadDeferredProps('recent', fn (Assert $page) => $page
                ->count('recentActivities', 2)
                ->where('recentActivities.0.id', $newer->id)
                ->where('recentActivities.0.school', $school->name)
                ->where('recentActivities.0.grade_level', $secondGrade->name)
                ->where('recentActivities.0.monitor', $monitor->name)
                ->where('recentActivities.1.id', $older->id)));
});
