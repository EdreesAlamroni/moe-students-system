<?php

use App\Enums\SchoolAcademicPeriod;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Support\User\DefaultUserNameBuilder;

test('education monitor default user name uses monitor name', function () {
    $monitor = new EducationMonitor([
        'name' => 'مُراقبة التّربية والتّعليم بنغازي',
    ]);

    expect(app(DefaultUserNameBuilder::class)->forEducationMonitor($monitor))
        ->toBe('مستخدم مُراقبة التّربية والتّعليم بنغازي');
});

test('education services office default user name avoids duplicate office prefix', function () {
    $office = new EducationServicesOffice([
        'name' => 'مكتب تعليم بنغازي',
    ]);

    expect(app(DefaultUserNameBuilder::class)->forEducationServicesOffice($office))
        ->toBe('مستخدم مكتب تعليم بنغازي');
});

test('education services office default user name adds office prefix when missing', function () {
    $office = new EducationServicesOffice([
        'name' => 'تعليم بنغازي',
    ]);

    expect(app(DefaultUserNameBuilder::class)->forEducationServicesOffice($office))
        ->toBe('مستخدم مكتب تعليم بنغازي');
});

test('school period default user name avoids duplicate school prefix for single period school', function () {
    $school = School::factory()->create(['name' => 'مدرسة الوحدة']);
    $period = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);

    expect(app(DefaultUserNameBuilder::class)->forSchoolPeriod($period))
        ->toBe('مستخدم مدرسة الوحدة');
});

test('school period default user name appends academic period label for multi period schools', function () {
    $school = School::factory()->create(['name' => 'الوحدة']);

    SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::EVENING,
    ]);

    $period = SchoolPeriod::factory()->for($school)->create([
        'academic_period' => SchoolAcademicPeriod::MORNING,
    ]);

    expect(app(DefaultUserNameBuilder::class)->forSchoolPeriod($period))
        ->toBe('مستخدم مدرسة الوحدة ('.SchoolAcademicPeriod::MORNING->displayName().')');
});
