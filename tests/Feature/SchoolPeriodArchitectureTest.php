<?php

use App\Actions\School\CreateSchools;
use App\Enums\SchoolAcademicPeriod;
use App\Enums\SchoolStudentsGender;
use App\Enums\SchoolType;
use App\Enums\UserScope;
use App\Models\AcademicYear;
use App\Models\BookDistribution;
use App\Models\BookDistributionItem;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\EducationMonitor;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\Student;
use App\Models\StudentTransfer;
use App\Models\Subject;
use App\Models\User;

beforeEach(function () {
    AcademicYear::clearCachedCurrent();

    AcademicYear::factory()->create(['is_active' => true]);
});

test('school periods synchronize denormalized fields from their canonical school', function () {
    $school = School::factory()->create(['name' => 'الاسم الأصلي']);

    $schoolPeriod = SchoolPeriod::factory()->for($school)->create([
        'education_monitor_id' => EducationMonitor::factory(),
        'name' => 'قيمة غير معتمدة',
    ]);

    expect($schoolPeriod)
        ->education_monitor_id->toBe($school->education_monitor_id)
        ->education_services_office_id->toBe($school->education_services_office_id)
        ->name->toBe('الاسم الأصلي');

    $newMonitor = EducationMonitor::factory()->create();
    $school->update([
        'education_monitor_id' => $newMonitor->id,
        'education_services_office_id' => null,
        'name' => 'الاسم المحدث',
    ]);

    expect($schoolPeriod->refresh())
        ->education_monitor_id->toBe($newMonitor->id)
        ->education_services_office_id->toBeNull()
        ->name->toBe('الاسم المحدث');
});

test('students synchronize denormalized education monitor from their canonical school', function () {
    $school = School::factory()->create(['name' => 'الاسم الأصلي']);
    $schoolPeriod = SchoolPeriod::factory()->for($school)->create();
    $student = Student::factory()->for($schoolPeriod)->create();

    expect($student->education_monitor_id)->toBe($school->education_monitor_id);

    $newMonitor = EducationMonitor::factory()->create();
    $school->update([
        'education_monitor_id' => $newMonitor->id,
        'education_services_office_id' => null,
        'name' => 'الاسم المحدث',
    ]);

    expect($student->refresh()->education_monitor_id)->toBe($newMonitor->id);
});

test('school creation supports the four accepted period scenarios', function (
    array $schoolAggregates,
    int $expectedSchools,
    array $expectedPeriods,
) {
    $createdPeriods = app(CreateSchools::class)->execute($schoolAggregates);

    expect(School::query()->count())->toBe($expectedSchools)
        ->and(SchoolPeriod::query()->count())->toBe(count($expectedPeriods))
        ->and($createdPeriods)->toHaveCount(count($expectedPeriods))
        ->and(SchoolPeriod::query()->orderBy('academic_period')->pluck('academic_period')->map->value->all())
        ->toEqualCanonicalizing($expectedPeriods);
})->with([
    'morning only' => fn () => [
        [[
            'school' => [
                'education_monitor_id' => EducationMonitor::factory()->create()->id,
                'type' => SchoolType::PUBLIC,
                'name' => 'مدرسة صباحية',
            ],
            'periods' => [
                SchoolAcademicPeriod::MORNING->value => [
                    'academic_period' => SchoolAcademicPeriod::MORNING,
                    'students_gender' => SchoolStudentsGender::MIXED,
                ],
            ],
        ]],
        1,
        [SchoolAcademicPeriod::MORNING->value],
    ],
    'evening only' => fn () => [
        [[
            'school' => [
                'education_monitor_id' => EducationMonitor::factory()->create()->id,
                'type' => SchoolType::PUBLIC,
                'name' => 'مدرسة مسائية',
            ],
            'periods' => [
                SchoolAcademicPeriod::EVENING->value => [
                    'academic_period' => SchoolAcademicPeriod::EVENING,
                    'students_gender' => SchoolStudentsGender::MIXED,
                ],
            ],
        ]],
        1,
        [SchoolAcademicPeriod::EVENING->value],
    ],
    'same dual-period school' => fn () => [
        [[
            'school' => [
                'education_monitor_id' => EducationMonitor::factory()->create()->id,
                'type' => SchoolType::PUBLIC,
                'name' => 'مدرسة موحدة',
            ],
            'periods' => [
                SchoolAcademicPeriod::MORNING->value => [
                    'academic_period' => SchoolAcademicPeriod::MORNING,
                    'students_gender' => SchoolStudentsGender::BOYS,
                ],
                SchoolAcademicPeriod::EVENING->value => [
                    'academic_period' => SchoolAcademicPeriod::EVENING,
                    'students_gender' => SchoolStudentsGender::GIRLS,
                ],
            ],
        ]],
        1,
        [SchoolAcademicPeriod::MORNING->value, SchoolAcademicPeriod::EVENING->value],
    ],
    'separate dual-period schools' => fn () => [
        [
            [
                'school' => [
                    'education_monitor_id' => EducationMonitor::factory()->create()->id,
                    'type' => SchoolType::PUBLIC,
                    'name' => 'مدرسة الصباح',
                ],
                'periods' => [
                    SchoolAcademicPeriod::MORNING->value => [
                        'academic_period' => SchoolAcademicPeriod::MORNING,
                        'students_gender' => SchoolStudentsGender::BOYS,
                    ],
                ],
            ],
            [
                'school' => [
                    'education_monitor_id' => EducationMonitor::factory()->create()->id,
                    'type' => SchoolType::PUBLIC,
                    'name' => 'مدرسة المساء',
                ],
                'periods' => [
                    SchoolAcademicPeriod::EVENING->value => [
                        'academic_period' => SchoolAcademicPeriod::EVENING,
                        'students_gender' => SchoolStudentsGender::GIRLS,
                    ],
                ],
            ],
        ],
        2,
        [SchoolAcademicPeriod::MORNING->value, SchoolAcademicPeriod::EVENING->value],
    ],
]);

test('sibling periods remain isolated operational tenants', function () {
    $school = School::factory()->create();
    $morning = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $evening = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    $morningStudent = Student::factory()->for($morning)->create();
    Student::factory()->for($evening)->create();

    $user = User::factory()->create([
        'scope' => UserScope::SCHOOL,
        'organization_type' => SchoolPeriod::class,
        'organization_id' => $morning->id,
    ]);

    $this->actingAs($user, 'school');

    expect(Student::query()->forCurrentSchool()->pluck('students.id')->all())
        ->toBe([$morningStudent->id])
        ->and(School::query()->count())->toBe(1)
        ->and(SchoolPeriod::query()->count())->toBe(2);
});

test('transfers preserve cross-period history including sibling periods', function () {
    $school = School::factory()->create();
    $morning = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);
    $evening = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);
    $student = Student::factory()->for($evening)->create();

    $transfer = StudentTransfer::factory()->create([
        'student_id' => $student->id,
        'from_school_period_id' => $morning->id,
        'to_school_period_id' => $evening->id,
    ]);

    expect($transfer->fromSchoolPeriod->is($morning))->toBeTrue()
        ->and($transfer->toSchoolPeriod->is($evening))->toBeTrue()
        ->and($transfer->fromSchoolPeriod->school_id)->toBe($transfer->toSchoolPeriod->school_id);
});

test('schedules and book distributions stay attached to one school period', function () {
    $schoolPeriod = SchoolPeriod::factory()->create();
    $gradeLevel = GradeLevel::factory()->create();
    $classroom = Classroom::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
    ]);
    $classPeriod = ClassPeriod::factory()->create([
        'academic_period' => $schoolPeriod->academic_period,
    ]);

    $schedule = ClassSchedule::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'classroom_id' => $classroom->id,
        'class_period_id' => $classPeriod->id,
        'subject_id' => Subject::factory()->create(['grade_level_id' => $gradeLevel->id])->id,
    ]);

    $student = Student::factory()->for($schoolPeriod)->create();
    $distribution = BookDistribution::factory()->create([
        'school_period_id' => $schoolPeriod->id,
        'grade_level_id' => $gradeLevel->id,
    ]);
    $item = BookDistributionItem::factory()->create([
        'book_distribution_id' => $distribution->id,
        'school_period_id' => $schoolPeriod->id,
        'student_id' => $student->id,
    ]);

    expect($schedule->school_period_id)->toBe($classroom->school_period_id)
        ->and($distribution->school_period_id)->toBe($schoolPeriod->id)
        ->and($item->school_period_id)->toBe($distribution->school_period_id)
        ->and($item->student->school_period_id)->toBe($schoolPeriod->id);
});
