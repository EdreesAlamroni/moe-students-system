<?php

use App\Enums\GradeLevelEnum;
use App\Enums\UserRole;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\EducationServicesOffice;
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

function createEducationServicesOfficeAcademicRecordContext(): array
{
    $office = EducationServicesOffice::factory()->create();
    $schoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($office->monitor, 'monitor')->for($office, 'office'), 'school')->create();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::MANAGER,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    foreach (['student:view', 'student:view-academic-record'] as $permission) {
        Permission::findOrCreate($permission, UserScope::EDUCATION_SERVICES_OFFICE->value);
    }

    $user->givePermissionTo([
        'student:view',
        'student:view-academic-record',
    ]);

    return compact('office', 'schoolPeriod', 'user');
}

beforeEach(function () {
    PolicyRegistrar::register(Request::create('/education-services-office/students', 'GET'));

    AcademicYear::clearCachedCurrent();

    AcademicYear::query()->create([
        'name' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
        'is_active' => true,
    ]);
});

test('guests cannot view education services office academic records', function () {
    $student = Student::factory()->create();

    $this->get(route('education-services-office.students.academic-record.show', ['student' => $student]))
        ->assertRedirect(route('education-services-office.login'));
});

test('users without permission cannot view education services office academic records', function () {
    ['office' => $office, 'schoolPeriod' => $schoolPeriod] = createEducationServicesOfficeAcademicRecordContext();

    $user = User::factory()->create([
        'scope' => UserScope::EDUCATION_SERVICES_OFFICE,
        'role' => UserRole::EMPLOYEE,
        'organization_type' => EducationServicesOffice::class,
        'organization_id' => $office->id,
    ]);

    $student = Student::factory()->create([
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $schoolPeriod->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});

test('authorized users can view education services office academic records', function () {
    ['office' => $office, 'schoolPeriod' => $schoolPeriod, 'user' => $user] = createEducationServicesOfficeAcademicRecordContext();

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
        'education_monitor_id' => $office->education_monitor_id,
        'school_period_id' => $schoolPeriod->id,
    ]);

    StudentEnrollment::factory()->create([
        'student_id' => $student->id,
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
        'classroom_id' => null,
        'academic_year_id' => AcademicYear::currentId(),
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.academic-record.show', ['student' => $student]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('education-services-office/students/academic-record/show')
            ->where('student.uuid', $student->uuid)
            ->where('requiresAcademicRecord', true)
            ->has('groupedRecords')
        );
});

test('users cannot view academic records for students outside their education services office', function () {
    ['user' => $user] = createEducationServicesOfficeAcademicRecordContext();
    $otherOffice = EducationServicesOffice::factory()->create();
    $otherSchoolPeriod = SchoolPeriod::factory()->for(School::factory()->for($otherOffice->monitor, 'monitor')->for($otherOffice, 'office'), 'school')->create();

    $student = Student::factory()->create([
        'education_monitor_id' => $otherOffice->education_monitor_id,
        'school_period_id' => $otherSchoolPeriod->id,
    ]);

    $this->actingAs($user, 'education_services_office')
        ->get(route('education-services-office.students.academic-record.show', ['student' => $student]))
        ->assertForbidden();
});
