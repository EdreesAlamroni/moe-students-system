<?php

namespace App\Http\Requests\EducationMonitor\School;

use App\Enums\SchoolBranchType;
use App\Enums\SchoolBuildingType;
use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('education_monitor')->check();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'educational_company_name' => [
                'nullable',
                'string',
                'max:255',
                ...($this->school()->isPrivate() ? ['required'] : []),
            ],
            'branch_type' => [
                'nullable',
                ...($this->school()->isPrivate() ? ['required'] : []),
                Rule::enum(SchoolBranchType::class),
            ],
            'building_type' => [
                'nullable',
                ...($this->school()->isPrivate() ? ['required'] : []),
                Rule::enum(SchoolBuildingType::class),
            ],
        ];
    }

    public function getAttributes(): array
    {
        $validated = $this->validated();

        if (! $this->school()->isPrivate()) {
            unset($validated['educational_company_name'], $validated['branch_type'], $validated['building_type']);
        }

        return $validated;
    }

    private function school(): School
    {
        /** @var School $school */
        $school = $this->route('school');

        return $school;
    }
}
