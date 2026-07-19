<?php

use App\Enums\GradeLevelEnum;
use App\Enums\SchoolEducationalStageEnum;
use App\Models\GradeLevel;
use App\Models\User;

test('guests are redirected from the grade levels page', function () {
    $this->get(route('administration.grade-levels.index'))
        ->assertRedirect(route('administration.login'));
});

test('authenticated users can visit the grade levels page', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'administration')
        ->get(route('administration.grade-levels.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('administration/grade-levels/index')
            ->has('gradeLevels')
            ->has('educationalStages', count(SchoolEducationalStageEnum::cases()))
            ->where('filter', [])
        );
});

test('grade levels page can be filtered by educational stage', function () {
    $user = User::factory()->create();

    foreach (GradeLevelEnum::cases() as $grade) {
        GradeLevel::factory()->create([
            'code' => $grade->value,
            'name' => $grade->label(),
            'educational_stage' => $grade->stage(),
            'order' => $grade->order(),
        ]);
    }

    $kindergartenGrades = GradeLevel::query()
        ->where('educational_stage', SchoolEducationalStageEnum::KINDERGARTEN)
        ->count();

    $this->actingAs($user, 'administration')
        ->get(route('administration.grade-levels.index', [
            'filter' => ['educational_stage' => SchoolEducationalStageEnum::KINDERGARTEN->value],
        ]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('gradeLevels', $kindergartenGrades)
            ->where('filter.educational_stage', SchoolEducationalStageEnum::KINDERGARTEN->value)
        );
});
