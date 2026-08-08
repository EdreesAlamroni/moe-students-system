<?php

use App\Enums\EntityNumberType;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\Municipal;
use App\Models\School;
use App\Models\Student;
use App\Models\Warehouse;

test('entity numbers are generated in the expected prefixed format', function (string $modelClass, EntityNumberType $type) {
    $model = $modelClass::factory()->create();

    expect($model->number)->toMatch($type->regex());
})->with([
    'warehouse' => [Warehouse::class, EntityNumberType::Warehouse],
    'education monitor' => [EducationMonitor::class, EntityNumberType::EducationMonitor],
    'education services office' => [EducationServicesOffice::class, EntityNumberType::EducationServicesOffice],
    'school' => [School::class, EntityNumberType::School],
    'student' => [Student::class, EntityNumberType::Student],
]);

test('education monitor numbers are assigned sequentially', function () {
    $firstMunicipal = Municipal::factory()->create();
    $secondMunicipal = Municipal::factory()->create();

    $firstMonitor = EducationMonitor::factory()->for($firstMunicipal, 'municipal')->create();
    $secondMonitor = EducationMonitor::factory()->for($secondMunicipal, 'municipal')->create();

    $firstSequence = (int) str_replace('EM-', '', $firstMonitor->number);
    $secondSequence = (int) str_replace('EM-', '', $secondMonitor->number);

    expect($secondSequence)->toBe($firstSequence + 1);
});

test('soft deleted entity numbers are not reused', function () {
    $firstMunicipal = Municipal::factory()->create();
    $secondMunicipal = Municipal::factory()->create();

    $deletedMonitor = EducationMonitor::factory()->for($firstMunicipal, 'municipal')->create();
    $deletedNumber = $deletedMonitor->number;

    $deletedMonitor->delete();

    $nextMonitor = EducationMonitor::factory()->for($secondMunicipal, 'municipal')->create();

    expect($nextMonitor->number)->not->toBe($deletedNumber);
});

test('student numbers are assigned sequentially', function () {
    $firstStudent = Student::factory()->create();
    $secondStudent = Student::factory()->create();

    $firstSequence = (int) str_replace('STU-', '', $firstStudent->number);
    $secondSequence = (int) str_replace('STU-', '', $secondStudent->number);

    expect($secondSequence)->toBe($firstSequence + 1);
});

test('soft deleted student numbers are not reused', function () {
    $deletedStudent = Student::factory()->create();
    $deletedNumber = $deletedStudent->number;

    $deletedStudent->delete();

    $nextStudent = Student::factory()->create();

    expect($nextStudent->number)->not->toBe($deletedNumber);
});

test('entity numbers cannot be changed through mass assignment', function () {
    $monitor = EducationMonitor::factory()->create();
    $originalNumber = $monitor->number;

    $monitor->update([
        'number' => 'EM-9999',
        'phone_number' => '0911111111',
    ]);

    expect($monitor->fresh()->number)->toBe($originalNumber);
});

test('student numbers cannot be changed through mass assignment', function () {
    $student = Student::factory()->create();
    $originalNumber = $student->number;

    $student->update([
        'number' => 'STU-99999999',
        'first_name' => 'Updated',
    ]);

    expect($student->fresh()->number)->toBe($originalNumber);
});
