<?php

use App\Enums\Gender;
use App\Enums\GradeLevelEnum;
use App\Enums\SchoolEducationalStageEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Nationality;
use App\Models\School;
use App\Models\SchoolEducationalStage;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createSchoolDashboardUser(SchoolPeriod $schoolPeriod): User
{
    return User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'role' => UserRole::MANAGER,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $schoolPeriod->id,
    ]);
}

function createSchoolDashboardGradeLevelCreator(SchoolPeriod $schoolPeriod): User
{
    $user = createSchoolDashboardUser($schoolPeriod);

    Permission::findOrCreate('grade-level:create', UserScope::SCHOOL->value);

    $user->givePermissionTo('grade-level:create');

    return $user;
}

function createSchoolWithKindergartenStage(): SchoolPeriod
{
    $schoolPeriod = SchoolPeriod::factory()->create();

    SchoolEducationalStage::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'academic_year_id' => AcademicYear::currentId(),
        'stage' => SchoolEducationalStageEnum::KINDERGARTEN,
    ]);

    return $schoolPeriod;
}

function createKindergartenGradeLevels(): Collection
{
    return GradeLevelEnum::filteredByStage(SchoolEducationalStageEnum::KINDERGARTEN)
        ->map(fn (GradeLevelEnum $grade): GradeLevel => GradeLevel::factory()->create([
            'code' => $grade->value,
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ]));
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/school/dashboard', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot access the school dashboard', function () {
    $this->get(route('school.dashboard'))
        ->assertRedirect(route('school.login'));
});

test('the dashboard renders without statistics in the initial payload', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolDashboardUser($schoolPeriod);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/dashboard')
            ->missingAll([
                'summary',
                'gradeLevelDistribution',
                'classroomOccupancy',
                'nationalityDistribution',
            ]));
});

test('every available grade level is shared while the school has none configured', function () {
    $schoolPeriod = createSchoolWithKindergartenStage();
    $user = createSchoolDashboardGradeLevelCreator($schoolPeriod);
    $gradeLevels = createKindergartenGradeLevels();

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('organization.available_grade_levels', $gradeLevels->count())
            ->where('organization.available_grade_levels.0.id', $gradeLevels->first()->id)
            ->where('organization.available_grade_levels.0.name', $gradeLevels->first()->name));
});

test('no available grade levels are shared once the school has any grade level configured', function () {
    $schoolPeriod = createSchoolWithKindergartenStage();
    $user = createSchoolDashboardGradeLevelCreator($schoolPeriod);
    $gradeLevels = createKindergartenGradeLevels();

    $schoolPeriod->allGradeLevels()->attach($gradeLevels->first()->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('organization.available_grade_levels', 0));

    expect($schoolPeriod->availableGradeLevels()->pluck('id')->all())->toBe([$gradeLevels->last()->id]);
});

test('no available grade levels are shared for users who cannot add them', function () {
    $schoolPeriod = createSchoolWithKindergartenStage();
    $user = createSchoolDashboardUser($schoolPeriod);
    createKindergartenGradeLevels();

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('organization.available_grade_levels', 0));
});

test('the summary reports aggregate counts scoped to the current school', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolDashboardUser($schoolPeriod);

    $gradeLevel = GradeLevel::factory()->create();
    $schoolPeriod->allGradeLevels()->attach($gradeLevel->id, [
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
    ]);

    [$libyan, $foreign] = Nationality::factory()->count(2)->create();

    Student::factory()->count(2)->create([
        'school_period_id' => $schoolPeriod->id,
        'gender' => Gender::MALE,
        'nationality_id' => $libyan->id,
    ]);

    Student::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'gender' => Gender::FEMALE,
        'nationality_id' => $foreign->id,
    ]);

    // Student in another school must not be counted.
    Student::factory()->create(['gender' => Gender::MALE]);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/dashboard')
            ->loadDeferredProps('summary', fn (Assert $page) => $page
                ->where('summary.students', 3)
                ->where('summary.males', 2)
                ->where('summary.females', 1)
                ->where('summary.grade_levels', 1)
                ->where('summary.classrooms', 1)
                ->where('summary.nationalities', 2)));
});

test('the grade level distribution reports gender counts per grade level in order', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolDashboardUser($schoolPeriod);

    $firstGrade = GradeLevel::factory()->create(['name' => 'الصف الأول', 'order' => 1]);
    $secondGrade = GradeLevel::factory()->create(['name' => 'الصف الثاني', 'order' => 2]);

    Student::factory()
        ->count(2)
        ->create(['school_period_id' => $schoolPeriod->id, 'gender' => Gender::MALE])
        ->each(function (Student $student) use ($schoolPeriod, $secondGrade) {
            StudentEnrollment::factory()->create([
                'school_period_id' => $schoolPeriod->id,
                'grade_level_id' => $secondGrade->id,
                'classroom_id' => null,
                'student_id' => $student->id,
            ]);
        });

    $student = Student::factory()->create(['school_period_id' => $schoolPeriod->id, 'gender' => Gender::FEMALE]);
    StudentEnrollment::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $firstGrade->id,
        'classroom_id' => null,
        'student_id' => $student->id,
    ]);

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/dashboard')
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

test('the classroom occupancy reports student counts and capacity per classroom', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolDashboardUser($schoolPeriod);

    $gradeLevel = GradeLevel::factory()->create(['name' => 'الصف الأول']);

    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'name' => '1',
        'capacity' => 30,
    ]);

    Student::factory()
        ->count(2)
        ->create(['school_period_id' => $schoolPeriod->id])
        ->each(function (Student $student) use ($schoolPeriod, $gradeLevel, $classroom) {
            StudentEnrollment::factory()->create([
                'school_period_id' => $schoolPeriod->id,
                'grade_level_id' => $gradeLevel->id,
                'classroom_id' => $classroom->id,
                'student_id' => $student->id,
            ]);
        });

    // Classroom in another school must not be included.
    Classroom::factory()->create();

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/dashboard')
            ->loadDeferredProps('classrooms', fn (Assert $page) => $page
                ->count('classroomOccupancy', 1)
                ->where('classroomOccupancy.0.name', '1')
                ->where('classroomOccupancy.0.grade_level', 'الصف الأول')
                ->where('classroomOccupancy.0.students', 2)
                ->where('classroomOccupancy.0.capacity', 30)));
});

test('the nationality distribution merges the tail into a single segment', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $user = createSchoolDashboardUser($schoolPeriod);

    Nationality::factory()
        ->count(6)
        ->create()
        ->each(function (Nationality $nationality, int $index) use ($schoolPeriod) {
            Student::factory()->count(6 - $index)->create([
                'school_period_id' => $schoolPeriod->id,
                'nationality_id' => $nationality->id,
            ]);
        });

    $this->actingAs($user, 'school')
        ->get(route('school.dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('school/dashboard')
            ->loadDeferredProps('nationalities', fn (Assert $page) => $page
                ->count('nationalityDistribution', 5)
                ->where('nationalityDistribution.0.students', 6)
                ->where('nationalityDistribution.1.students', 5)
                ->where('nationalityDistribution.2.students', 4)
                ->where('nationalityDistribution.3.students', 3)
                ->where('nationalityDistribution.4.name', 'أخرى')
                ->where('nationalityDistribution.4.students', 3)));
});
