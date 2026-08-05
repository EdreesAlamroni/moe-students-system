<?php

namespace App\Support\Organization\Contexts;

use App\Enums\SchoolStudentsGender;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Support\Organization\OrganizationContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

final class SchoolOrganizationContext extends OrganizationContext
{
    protected function organizationType(): string
    {
        return SchoolPeriod::class;
    }

    protected function columns(): array
    {
        return [
            'id',
            'school_id',
            'name',
            'academic_period',
            'students_gender',
        ];
    }

    protected function query(): Builder
    {
        return SchoolPeriod::query()->whereHas('school', function (Builder $query): void {
            $query->whereNull('schools.deleted_at');
        });
    }

    protected function build(Model $organization): array
    {
        /** @var SchoolPeriod $organization */
        $context = [
            'type' => 'school',
            'id' => $organization->id,
            'school_id' => $organization->school_id,
            'name' => $organization->name,
            'academic_period' => $organization->academic_period->toArray(),
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
    private function gradeLevelsToConfigure(SchoolPeriod $schoolPeriod): Collection
    {
        if ($schoolPeriod->gradeLevels()->exists()) {
            return collect([]);
        }

        if (Gate::denies('create', GradeLevel::class)) {
            return collect([]);
        }

        return $schoolPeriod->availableGradeLevels();
    }
}
