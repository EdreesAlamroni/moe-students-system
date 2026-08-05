<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createEducationMonitorAcademicRecordContext(): array
{
    $monitor = EducationMonitor::factory()->create();
    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($monitor, 'monitor'), 'school')->create();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    foreach (['student:view', 'student:view-academic-record'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-academic-record',
    ]);

    return compact('monitor', 'schoolPeriod', 'user');
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-monitor/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot view education monitor academic records', function () {
    $student = Student::factory()->create();

    $this->get(route('education-monitor.students.academic-record.show', ['student' => $student]))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without permission cannot view education monitor academic records', function () {
    ['monitor' => $monitor, 'schoolPeriod' => $schoolPeriod] = createEducationMonitorAcademicRecordContext();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_period_id' => $schoolPeriod->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can view education monitor academic records', function () {
    ['monitor' => $monitor, 'schoolPeriod' => $schoolPeriod, 'user' => $user] = createEducationMonitorAcademicRecordContext();

    foreach ([GradeLevelEnum::GRADE_1, GradeLevelEnum::GRADE_2, GradeLevelEnum::GRADE_3] as $gradeLevelEnum) {
        GradeLevel::query()->firstOrCreate(
            ['code' => $gradeLevelEnum->value],
            [
                'name' => $gradeLevelEnum->label(),
                'educational_stage' => $gradeLevelEnum->stage(),
                'order' => $gradeLevelEnum->order(),
            ],
        );
    }

    $gradeLevel = GradeLevel::query()->where('code', GradeLevelEnum::GRADE_3->value)->firstOrFail();

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_period_id' => $schoolPeriod->id,
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.academic-record.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/academic-record/show')
            ->where('student.uuid', $student->uuid)
            ->where('requiresAcademicRecord', true)
            ->has('groupedRecords')
        );
});

test('users cannot view academic records for students outside their education monitor', function () {
    ['user' => $user] = createEducationMonitorAcademicRecordContext();
    $otherMonitor = EducationMonitor::factory()->create();
    $otherSchoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($otherMonitor, 'monitor'), 'school')->create();

    $student = Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_period_id' => $otherSchoolPeriod->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});
