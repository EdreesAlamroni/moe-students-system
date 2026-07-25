<?php

use App\Enums\Gender;
use App\Enums\SchoolType;
use App\Enums\UserRole;
use App\Enums\UserScope;
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
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;

function createEducationMonitorDashboardUser(EducationMonitor $monitor): User
{
    return User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/dashboard', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected to the panel login page', function () {
    $this->get(route('education-monitor.dashboard'))
        ->assertRedirect(route('education-monitor.login'));
});

test('the dashboard renders without statistics in the initial payload', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->missingAll([
                'summary',
                'officeDistribution',
                'schoolDistribution',
                'gradeLevelDistribution',
                'nationalityDistribution',
                'schoolTypeDistribution',
            ]));
});

test('the summary reports aggregate counts scoped to the current education monitor', function () {
    $monitor = EducationMonitor::factory()->create();
    $otherMonitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $office = EducationServicesOffice::factory()->create(['education_monitor_id' => $monitor->id]);
    EducationServicesOffice::factory()->create(['education_monitor_id' => $otherMonitor->id]);

    $school = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $office->id,
    ]);

    $otherSchool = School::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
    ]);

    $gradeLevel = GradeLevel::factory()->create();
    $school->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    Classroom::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    Classroom::factory()->create([
        'school_id' => $otherSchool->id,
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
        'school_id' => null,
        'gender' => Gender::FEMALE,
        'nationality_id' => $foreign->id,
    ]);

    // Students under another monitor must not be counted.
    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $otherSchool->id,
        'gender' => Gender::MALE,
        'nationality_id' => $libyan->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps('summary', fn (Assert $page) => $page
                ->where('summary.students', 3)
                ->where('summary.males', 2)
                ->where('summary.females', 1)
                ->where('summary.nationalities', 2)
                ->where('summary.education_services_offices', 1)
                ->where('summary.schools', 1)
                ->where('summary.grade_levels', 1)
                ->where('summary.classrooms', 1)
                ->where('summary.students_unassigned_to_school', 1)));
});

test('the office distribution reports student and school counts per office, largest first', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $largestOffice = EducationServicesOffice::factory()->create([
        'education_monitor_id' => $monitor->id,
        'name' => 'مكتب الخدمات الكبرى',
    ]);
    $smallestOffice = EducationServicesOffice::factory()->create([
        'education_monitor_id' => $monitor->id,
        'name' => 'مكتب الخدمات الصغرى',
    ]);

    $largestSchool = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $largestOffice->id,
    ]);
    School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $largestOffice->id,
    ]);
    $smallestSchool = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $smallestOffice->id,
    ]);

    Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $largestSchool->id,
        'gender' => Gender::MALE,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $smallestSchool->id,
        'gender' => Gender::FEMALE,
    ]);

    // Unassigned students must not appear in office totals.
    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => null,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps('offices', fn (Assert $page) => $page
                ->count('officeDistribution', 2)
                ->where('officeDistribution.0.name', 'مكتب الخدمات الكبرى')
                ->where('officeDistribution.0.males', 2)
                ->where('officeDistribution.0.females', 0)
                ->where('officeDistribution.0.students', 2)
                ->where('officeDistribution.0.schools', 2)
                ->where('officeDistribution.1.name', 'مكتب الخدمات الصغرى')
                ->where('officeDistribution.1.males', 0)
                ->where('officeDistribution.1.females', 1)
                ->where('officeDistribution.1.students', 1)
                ->where('officeDistribution.1.schools', 1)));
});

test('the school distribution reports student and classroom counts for the largest schools first', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $office = EducationServicesOffice::factory()->create(['education_monitor_id' => $monitor->id]);

    $largest = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $office->id,
        'name' => 'المدرسة الكبرى',
    ]);
    $smallest = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'education_services_office_id' => $office->id,
        'name' => 'المدرسة الصغرى',
    ]);

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

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 2)
                ->where('schoolDistribution.0.name', 'المدرسة الكبرى')
                ->where('schoolDistribution.0.students', 2)
                ->where('schoolDistribution.0.classrooms', 2)
                ->where('schoolDistribution.0.office.name', $office->name)
                ->where('schoolDistribution.1.name', 'المدرسة الصغرى')
                ->where('schoolDistribution.1.students', 1)
                ->where('schoolDistribution.1.classrooms', 0)
                ->where('schoolDistribution.1.office.name', $office->name)));
});

test('the school distribution is limited to the ten largest schools', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    School::factory()->count(11)->create(['education_monitor_id' => $monitor->id]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 10)));
});

test('the grade level distribution reports gender counts per grade level in order', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);

    $firstGrade = GradeLevel::factory()->create(['name' => 'الصف الأول', 'order' => 1]);
    $secondGrade = GradeLevel::factory()->create(['name' => 'الصف الثاني', 'order' => 2]);

    Student::factory()
        ->count(2)
        ->create([
            'education_monitor_id' => $monitor->id,
            'school_id' => $school->id,
            'gender' => Gender::MALE,
        ])
        ->each(function (Student $student) use ($school, $secondGrade) {
            StudentEnrollment::factory()->create([
                'school_id' => $school->id,
                'grade_level_id' => $secondGrade->id,
                'classroom_id' => null,
                'student_id' => $student->id,
            ]);
        });

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
        'gender' => Gender::FEMALE,
    ]);

    StudentEnrollment::factory()->create([
        'school_id' => $school->id,
        'grade_level_id' => $firstGrade->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
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
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $school = School::factory()->create(['education_monitor_id' => $monitor->id]);
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

    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $school->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps(fn (Assert $page) => $page
                ->where('summary.classrooms', 1)
                ->where('schoolDistribution.0.classrooms', 1)));
});

test('the nationality distribution merges the tail into a single segment', function () {
    $monitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    Nationality::factory()
        ->count(6)
        ->create()
        ->each(function (Nationality $nationality, int $index) use ($monitor) {
            Student::factory()->count(6 - $index)->create([
                'education_monitor_id' => $monitor->id,
                'school_id' => null,
                'nationality_id' => $nationality->id,
            ]);
        });

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps('nationalities', fn (Assert $page) => $page
                ->count('nationalityDistribution', 5)
                ->where('nationalityDistribution.0.students', 6)
                ->where('nationalityDistribution.1.students', 5)
                ->where('nationalityDistribution.2.students', 4)
                ->where('nationalityDistribution.3.students', 3)
                ->where('nationalityDistribution.4.name', 'أخرى')
                ->where('nationalityDistribution.4.students', 3)));
});

test('the school type distribution reports public and private school and student counts', function () {
    $monitor = EducationMonitor::factory()->create();
    $otherMonitor = EducationMonitor::factory()->create();
    $user = createEducationMonitorDashboardUser($monitor);

    $publicSchool = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'type' => SchoolType::PUBLIC,
    ]);
    $privateSchool = School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'type' => SchoolType::PRIVATE,
    ]);
    School::factory()->create([
        'education_monitor_id' => $monitor->id,
        'type' => SchoolType::PUBLIC,
    ]);

    $otherPublicSchool = School::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'type' => SchoolType::PUBLIC,
    ]);

    Student::factory()->count(3)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $publicSchool->id,
    ]);

    Student::factory()->count(2)->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => $privateSchool->id,
    ]);

    // Unassigned students must not count toward school-type student totals.
    Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_id' => null,
    ]);

    // Students under another monitor must not be counted.
    Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_id' => $otherPublicSchool->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/dashboard')
            ->loadDeferredProps('school-types', fn (Assert $page) => $page
                ->where('schoolTypeDistribution.public_schools', 2)
                ->where('schoolTypeDistribution.private_schools', 1)
                ->where('schoolTypeDistribution.public_students', 3)
                ->where('schoolTypeDistribution.private_students', 2)));
});
