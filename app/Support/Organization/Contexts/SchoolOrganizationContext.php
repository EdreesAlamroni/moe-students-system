<?php

namespace App\Support\Organization\Contexts;

use App\Enums\SchoolStudentsGender;
use App\Models\GradeLevel;
use App\Models\School;
use App\Support\Organization\OrganizationContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

final class SchoolOrganizationContext extends OrganizationContext
{
    protected function organizationType(): string
    {
        return School::class;
    }

    protected function columns(): array
    {
        return ['id', 'name', 'same_school_uuid', 'students_gender'];
    }

    protected function build(Model $organization): array
    {
        /** @var School $organization */
        $context = [
            'type' => 'school',
            'id' => $organization->id,
            'name' => $organization->name,
            'students_gender' => $organization->students_gender?->toArray(),
        ];

        if ($organization->students_gender === null) {
            $context['students_gender_options'] = SchoolStudentsGender::optionsArray();
        }

        $context['available_grade_levels'] = $this->gradeLevelsToConfigure($organization);

        return $context;
    }

    /**
     * Grade levels the school can add while it has none configured for the current academic year,
     * used to prompt the school to complete its grade level setup.
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    private function gradeLevelsToConfigure(School $school): Collection
    {
        if ($school->gradeLevels()->exists()) {
            return collect([]);
        }

        if (! Gate::allows('create', GradeLevel::class)) {
            return collect([]);
        }

        return $school->availableGradeLevels();
    }
}
