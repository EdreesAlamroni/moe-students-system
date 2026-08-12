<?php

namespace App\Support\Organization\Contexts;

use App\Enums\SchoolStudentsGender;
use App\Models\GradeLevel;
use App\Models\SchoolPeriod;
use App\Models\User;
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
            'display_name' => $organization->display_name,
            'academic_period' => [
                ...$organization->academic_period->toArray(),
                'display_name' => $organization->academic_period->displayName(),
            ],
            'students_gender' => $organization->students_gender?->toArray(),
        ];

        if ($organization->students_gender === null) {
            $context['students_gender_options'] = SchoolStudentsGender::optionsArray();
        }

        $context['available_grade_levels'] = $this->gradeLevelsToConfigure($organization);

        return $context;
    }

    public function resolve(User $user): ?array
    {
        $context = parent::resolve($user);

        if ($context === null) {
            return null;
        }

        $user->loadMissing(['schoolPeriods:id,school_id,name,academic_period']);

        if ($user->schoolPeriods->count() > 1) {
            $context['available_periods'] = $user->schoolPeriods
                ->sortBy(fn (SchoolPeriod $period): int => $period->academic_period->isMorning() ? 0 : 1)
                ->values()
                ->map(fn (SchoolPeriod $period): array => [
                    'id' => $period->id,
                    'name' => $period->display_name,
                    'academic_period' => [
                        ...$period->academic_period->toArray(),
                        'display_name' => $period->academic_period->displayName(),
                    ],
                ])->all();
        }

        return $context;
    }

    /**
     * Grade levels the school can add while it has none configured for the current academic year,
     * used to prompt the school to complete its grade level setup.
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
