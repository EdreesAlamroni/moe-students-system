<?php

use App\Enums\Gender;
use App\Enums\SchoolType;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;

function createEducationServicesOfficeDashboardUser(EducationServicesOffice $office): User
{
    return User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);
}

function createSchoolForOffice(EducationServicesOffice $office, array $attributes = []): SchoolPeriod
{
    $school = School::factory()
        ->for($office->monitor, 'monitor')
        ->for($office, 'office')
        ->create($attributes);

    return SchoolPeriod::factory()->for($school)->create();
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-services-office/dashboard', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests are redirected to the panel login page', function () {
    $this->get(route('education-services-office.dashboard'))
        ->assertRedirect(route('education-services-office.login'));
});

test('the dashboard renders without statistics in the initial payload', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->missingAll([
                'summary',
                'schoolDistribution',
                'gradeLevelDistribution',
                'nationalityDistribution',
                'schoolTypeDistribution',
            ]));
});

test('the summary reports aggregate counts scoped to the current education services office', function () {
    $office = EducationServicesOffice::factory()->create();
    $otherOffice = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    $schoolPeriod = createSchoolForOffice($office);
    $otherSchoolPeriod = createSchoolForOffice($otherOffice);

    $gradeLevel = GradeLevel::factory()->create();
    $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    Classroom::factory()->create([
        'school_period_id' => $otherSchoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    [$libyan, $foreign] = Nationality::factory()->count(2)->create();

    $males = Student::factory()->count(2)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $schoolPeriod->id,
        'gender' => Gender::MALE,
        'nationality_id' => $libyan->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $schoolPeriod->id,
        'gender' => Gender::FEMALE,
        'nationality_id' => $foreign->id,
    ]);

    // Only students enrolled in a grade level count as enrolled.
    StudentEnrollment::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'student_id' => $males->first()->id,
    ]);

    // Students not assigned to a school must not be counted.
    Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => null,
        'nationality_id' => $libyan->id,
    ]);

    // Students under another office must not be counted.
    Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_period_id' => $otherSchoolPeriod->id,
        'gender' => Gender::MALE,
        'nationality_id' => $libyan->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->loadDeferredProps('summary', fn (Assert $page) => $page
                ->where('summary.students', 3)
                ->where('summary.males', 2)
                ->where('summary.females', 1)
                ->where('summary.nationalities', 2)
                ->where('summary.schools', 1)
                ->where('summary.grade_levels', 1)
                ->where('summary.classrooms', 1)
                ->where('summary.students_unenrolled_in_grade_level', 2)));
});

test('the school distribution reports student and classroom counts for the largest schools first', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    $largest = createSchoolForOffice($office, ['name' => 'المدرسة الكبرى']);
    $smallest = createSchoolForOffice($office, ['name' => 'المدرسة الصغرى']);

    // Schools under another office must not appear.
    $otherOffice = EducationServicesOffice::factory()->create();
    createSchoolForOffice($otherOffice);

    $gradeLevel = GradeLevel::factory()->create();
    Classroom::factory()->count(2)->create([
        'school_period_id' => $largest->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    Student::factory()->count(2)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $largest->id,
    ]);

    Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $smallest->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 2)
                ->where('schoolDistribution.0.name', 'المدرسة الكبرى')
                ->where('schoolDistribution.0.students', 2)
                ->where('schoolDistribution.0.classrooms', 2)
                ->where('schoolDistribution.1.name', 'المدرسة الصغرى')
                ->where('schoolDistribution.1.students', 1)
                ->where('schoolDistribution.1.classrooms', 0)));
});

test('the school distribution is limited to the ten largest schools', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    School::factory()
        ->count(11)
        ->for($office->monitor, 'monitor')
        ->for($office, 'office')
        ->has(SchoolPeriod::factory(), 'periods')
        ->create();

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->loadDeferredProps('schools', fn (Assert $page) => $page
                ->count('schoolDistribution', 10)));
});

test('the grade level distribution reports gender counts per grade level in order', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    $schoolPeriod = createSchoolForOffice($office);

    $firstGrade = GradeLevel::factory()->create(['name' => 'الصف الأول', 'order' => 1]);
    $secondGrade = GradeLevel::factory()->create(['name' => 'الصف الثاني', 'order' => 2]);

    Student::factory()
        ->count(2)
        ->create([
            'education_monitor_id' => $office->education_monitor_id,
            'school_period_id' => $schoolPeriod->id,
            'gender' => Gender::MALE,
        ])
        ->each(function (Student $student) use ($schoolPeriod, $secondGrade) {
            StudentEnrollment::factory()->create([
                'school_period_id' => $schoolPeriod->id,
                'grade_level_id' => $secondGrade->id,
                'classroom_id' => null,
                'student_id' => $student->id,
            ]);
        });

    $student = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $schoolPeriod->id,
        'gender' => Gender::FEMALE,
    ]);

    StudentEnrollment::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $firstGrade->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
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

test('the nationality distribution merges the tail into a single segment', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    $schoolPeriod = createSchoolForOffice($office);

    Nationality::factory()
        ->count(6)
        ->create()
        ->each(function (Nationality $nationality, int $index) use ($office, $schoolPeriod) {
            Student::factory()->count(6 - $index)->create([
                'education_monitor_id' => $office->education_monitor_id,
                'school_period_id' => $schoolPeriod->id,
                'nationality_id' => $nationality->id,
            ]);
        });

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->loadDeferredProps('nationalities', fn (Assert $page) => $page
                ->count('nationalityDistribution', 5)
                ->where('nationalityDistribution.0.students', 6)
                ->where('nationalityDistribution.1.students', 5)
                ->where('nationalityDistribution.2.students', 4)
                ->where('nationalityDistribution.3.students', 3)
                ->where('nationalityDistribution.4.name', 'أخرى')
                ->where('nationalityDistribution.4.students', 3)));
});

test('the school type distribution reports counts and the largest school of each type', function () {
    $office = EducationServicesOffice::factory()->create();
    $otherOffice = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    $largestPublicSchool = createSchoolForOffice($office, [
        'type' => SchoolType::PUBLIC,
        'name' => 'المدرسة العامة الكبرى',
    ]);
    $privateSchool = createSchoolForOffice($office, [
        'type' => SchoolType::PRIVATE,
        'name' => 'المدرسة الخاصة',
    ]);
    createSchoolForOffice($office, ['type' => SchoolType::PUBLIC]);

    $otherPublicSchool = createSchoolForOffice($otherOffice, ['type' => SchoolType::PUBLIC]);

    Student::factory()->count(3)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $largestPublicSchool->id,
    ]);

    Student::factory()->count(2)->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $privateSchool->id,
    ]);

    // Students under another office must not be counted.
    Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_period_id' => $otherPublicSchool->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->loadDeferredProps('school-types', fn (Assert $page) => $page
                ->where('schoolTypeDistribution.public_schools', 2)
                ->where('schoolTypeDistribution.private_schools', 1)
                ->where('schoolTypeDistribution.public_students', 3)
                ->where('schoolTypeDistribution.private_students', 2)
                ->where('schoolTypeDistribution.largest_public_school.name', 'المدرسة العامة الكبرى')
                ->where('schoolTypeDistribution.largest_public_school.students', 3)
                ->where('schoolTypeDistribution.largest_private_school.name', 'المدرسة الخاصة')
                ->where('schoolTypeDistribution.largest_private_school.students', 2)));
});

test('the largest school of a type is null when no school of that type has students', function () {
    $office = EducationServicesOffice::factory()->create();
    $user = createEducationServicesOfficeDashboardUser($office);

    createSchoolForOffice($office, ['type' => SchoolType::PRIVATE]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/dashboard')
            ->loadDeferredProps('school-types', fn (Assert $page) => $page
                ->where('schoolTypeDistribution.largest_public_school', null)
                ->where('schoolTypeDistribution.largest_private_school', null)));
});
