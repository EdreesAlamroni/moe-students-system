<?php

use App\Enums\Gender;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Models\Warehouse;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected to the panel login page', function () {
    $response = $this->get(route('administration.dashboard'));
    $response->assertRedirect(route('administration.login'));
});

test('the dashboard renders without statistics in the initial payload', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->missingAll([
                'summary',
                'educationMonitorDistribution',
                'schoolDistribution',
                'gradeLevelDistribution',
                'nationalityDistribution',
            ]));
});

test('the summary reports system-wide aggregate counts', function () {
    $user = User::factory()->create();

    $warehouse = Warehouse::factory()->create();
    $monitor = EducationMonitor::factory()->create(['warehouse_id' => $warehouse->id]);
    $office = EducationServicesOffice::factory()->create(['education_monitor_id' => $monitor->id]);
    $school = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $office->id,
    ]);

    $gradeLevel = GradeLevel::factory()->create();
    Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    [$libyan, $foreign] = Nationality::factory()->count(2)->create();

    Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'gender' => Gender::MALE,
        'nationality_id' => $libyan->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'gender' => Gender::FEMALE,
        'nationality_id' => $foreign->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps('summary', fn (Assert $page) => $page
                ->where('summary.students', 3)
                ->where('summary.males', 2)
                ->where('summary.females', 1)
                ->where('summary.nationalities', 2)
                ->where('summary.education_monitors', 1)
                ->where('summary.education_services_offices', 1)
                ->where('summary.schools', 1)
                ->where('summary.warehouses', 1)
                ->where('summary.classrooms', 1))
        );
});

test('the education monitor distribution reports student and school counts per monitor, largest first', function () {
    $user = User::factory()->create();

    $largest = EducationMonitor::factory()->create();
    $smallest = EducationMonitor::factory()->create();

    School::factory()->count(2)->create(['education_monitor_id' => $largest->id]);
    $school = School::factory()->create(['education_monitor_id' => $smallest->id]);

    Student::factory()->count(2)->create([
        'education_monitor_id' => $largest->id,
        'school_id' => null,
        'gender' => Gender::MALE,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $smallest->id,
        'school_id' => $school->id,
        'gender' => Gender::FEMALE,
    ]);

    // Students unassigned to a monitor must not be counted.
    Student::factory()->create([
        'education_monitor_id' => null,
        'school_id' => null,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps('education-monitors', fn (Assert $page) => $page
                ->count('educationMonitorDistribution', 2)
                ->where('educationMonitorDistribution.0.name', $largest->name)
                ->where('educationMonitorDistribution.0.males', 2)
                ->where('educationMonitorDistribution.0.females', 0)
                ->where('educationMonitorDistribution.0.students', 2)
                ->where('educationMonitorDistribution.0.schools', 2)
                ->where('educationMonitorDistribution.1.name', $smallest->name)
                ->where('educationMonitorDistribution.1.males', 0)
                ->where('educationMonitorDistribution.1.females', 1)
                ->where('educationMonitorDistribution.1.students', 1)
                ->where('educationMonitorDistribution.1.schools', 1)));
});

test('the school distribution reports student and classroom counts for the largest schools first', function () {
    $user = User::factory()->create();

    $monitor = EducationMonitor::factory()->create();
    $largest = School::factory()->create(['education_monitor_id' => $monitor->id, 'name' => 'المدرسة الكبرى']);
    $smallest = School::factory()->create(['education_monitor_id' => $monitor->id, 'name' => 'المدرسة الصغرى']);

    $gradeLevel = GradeLevel::factory()->create();
    Classroom::factory()->count(2)->create([
        'school_id' => $largest->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $largest->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $smallest->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 2)
                ->where('schoolDistribution.0.name', 'المدرسة الكبرى')
                ->where('schoolDistribution.0.students', 2)
                ->where('schoolDistribution.0.classrooms', 2)
                ->where('schoolDistribution.0.monitor.name', $monitor->name)
                ->where('schoolDistribution.1.name', 'المدرسة الصغرى')
                ->where('schoolDistribution.1.students', 1)
                ->where('schoolDistribution.1.classrooms', 0)
                ->where('schoolDistribution.1.monitor.name', $monitor->name)));
});

test('the school distribution is limited to the ten largest schools', function () {
    $user = User::factory()->create();

    $monitor = EducationMonitor::factory()->create();
    School::factory()->count(11)->create(['education_monitor_id' => $monitor->id]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 10)));
});

test('the grade level distribution reports gender counts per grade level in order', function () {
    $user = User::factory()->create();

    $school = School::factory()->create();

    $firstGrade = GradeLevel::factory()->create(['name' => 'الصف الأول', 'order' => 1]);
    $secondGrade = GradeLevel::factory()->create(['name' => 'الصف الثاني', 'order' => 2]);

    Student::factory()
        ->count(2)
        ->create(['school_id' => $school->id, 'gender' => Gender::MALE])
        ->each(function (Student $student) use ($school, $secondGrade) {
            StudentEnrollment::factory()->create([
                'school_id' => $school->id,
                'grade_level_id' => $secondGrade->id,
                'classroom_id' => null,
                'student_id' => $student->id,
            ]);
        });

    $student = Student::factory()->create(['school_id' => $school->id, 'gender' => Gender::FEMALE]);
    StudentEnrollment::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $firstGrade->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps('grade-levels', fn (Assert $page) => $page
                ->count('gradeLevelDistribution', 2)
                ->where('gradeLevelDistribution.0.name', 'الصف الأول')
                ->where('gradeLevelDistribution.0.males', 0)
                ->where('gradeLevelDistribution.0.females', 1)
                ->where('gradeLevelDistribution.0.students', 1)
                ->where('gradeLevelDistribution.1.name', 'الصف الثاني')
                ->where('gradeLevelDistribution.1.males', 2)
                ->where('gradeLevelDistribution.1.females', 0)
                ->where('gradeLevelDistribution.1.students', 2)));
});

test('classroom counts exclude classrooms from previous academic years', function () {
    $user = User::factory()->create();

    $school = School::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();

    $previousYear = AcademicYear::factory()->create([
        'name' => '2023-2024',
        'start_date' => now()->subYear()->startOfYear(),
        'end_date' => now()->subYear()->endOfYear(),
        'is_active' => false,
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
        'academic_year_id' => $previousYear->id,
    ]);

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps(fn (Assert $page) => $page
                ->where('summary.classrooms', 1)
                ->where('schoolDistribution.0.classrooms', 1)));
});

test('the nationality distribution merges the tail into a single segment', function () {
    $user = User::factory()->create();

    Nationality::factory()
        ->count(6)
        ->create()
        ->each(function (Nationality $nationality, int $index) {
            Student::factory()->count(6 - $index)->create([
                'education_monitor_id' => null,
                'school_id' => null,
                'nationality_id' => $nationality->id,
            ]);
        });

    $this->actingAs($user, 'administration')
        ->get(route('administration.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('administration/dashboard')
            ->loadDeferredProps('nationalities', fn (Assert $page) => $page
                ->count('nationalityDistribution', 5)
                ->where('nationalityDistribution.0.students', 6)
                ->where('nationalityDistribution.1.students', 5)
                ->where('nationalityDistribution.2.students', 4)
                ->where('nationalityDistribution.3.students', 3)
                ->where('nationalityDistribution.4.name', 'أخرى')
                ->where('nationalityDistribution.4.students', 3)));
});
