<?php

namespace App\Support\Organization\Contexts;

use App\Enums\SchoolStudentsGender;
use App\Models\School;
use App\Support\Organization\OrganizationContext;
use Illuminate\Database\Eloquent\Model;

final class SchoolOrganizationContext extends OrganizationContext
{
    protected function organizationType(): string
    {
        return School::class;
    }

    protected function columns(): array
    {
        return ['id', 'name', 'students_gender'];
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

        return $context;
    }
}
