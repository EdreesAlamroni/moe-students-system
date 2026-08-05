<?php

use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentPsychosocialCard;
use App\Models\User;
use App\Support\PolicyRegistrar;
use Illuminate\Http\Request;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

function createEducationMonitorPsychosocialCardContext(): array
{
    $monitor = EducationMonitor::factory()->create();
    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($monitor, 'monitor'), 'school')->create();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_MONITOR,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationMonitor::class,
        'organization_id' => $monitor->id,
    ]);

    foreach (['student:view', 'student:view-psychosocial-card'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_MONITOR->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-psychosocial-card',
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

test('guests cannot view education monitor psychosocial cards', function () {
    $student = Student::factory()->create();

    $this->get(route('education-monitor.students.psychosocial-card.show', ['student' => $student]))
        ->assertRedirect(route('education-monitor.login'));
});

test('users without permission cannot view education monitor psychosocial cards', function () {
    ['monitor' => $monitor, 'schoolPeriod' => $schoolPeriod] = createEducationMonitorPsychosocialCardContext();

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
        ->get(route('education-monitor.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can view education monitor psychosocial cards', function () {
    ['monitor' => $monitor, 'schoolPeriod' => $schoolPeriod, 'user' => $user] = createEducationMonitorPsychosocialCardContext();

    $student = Student::factory()->create([
        'education_monitor_id' => $monitor->id,
        'school_period_id' => $schoolPeriod->id,
    ]);

    StudentPsychosocialCard::factory()->create([
        'student_id' => $student->id,
        'behavioral_problems' => [],
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.psychosocial-card.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-monitor/students/psychosocial-cards/show')
            ->where('student.uuid', $student->uuid)
            ->has('psychosocialCard')
        );
});

test('users cannot view psychosocial cards for students outside their education monitor', function () {
    ['user' => $user] = createEducationMonitorPsychosocialCardContext();
    $otherMonitor = EducationMonitor::factory()->create();
    $otherSchoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($otherMonitor, 'monitor'), 'school')->create();

    $student = Student::factory()->create([
        'education_monitor_id' => $otherMonitor->id,
        'school_period_id' => $otherSchoolPeriod->id,
    ]);

    $this->actingAs($user, 'education_monitor')
        ->get(route('education-monitor.students.psychosocial-card.show', ['student' => $student]))
        ->assertForbidden();
});
