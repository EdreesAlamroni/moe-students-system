<?php

namespace App\Http\Requests\EducationServicesOffice\User;

use App\Enums\UserScope;
use App\Http\Requests\Concerns\InteractsWithSchoolPeriodAssignment;
use App\Models\School;
use App\Models\SchoolPeriod;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRequest extends FormRequest
{
    use InteractsWithSchoolPeriodAssignment;

    public function authorize(): bool
    {
        return auth('education_services_office')->check();
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        $scopeIsSchool = $user->scope === UserScope::SCHOOL;
        $officeId = $this->currentOfficeId();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'sometimes',
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($user),
            ],
            'school_id' => Rule::when($scopeIsSchool, [
                'required',
                'integer',
                Rule::exists(School::class, 'id')
                    ->where('education_services_office_id', $officeId),
            ]),
            'school_period_ids' => Rule::when($scopeIsSchool, $this->schoolPeriodAssignmentRules($this->integer('school_id') ?: null)),
            'school_period_ids.*' => [
                'integer',
                'distinct',
            ],
            'roles' => [
                'required',
                'array',
                'min:1',
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id')->where('guard_name', $user->scope->value),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $roles = $this->input('roles', []);

        $this->merge([
            'email' => $this->filled('email') ? $this->input('email') : null,
            'roles' => is_array($roles) ? $roles : json_decode($roles, true) ?? [],
        ]);
    }

    public function getAttributes(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        $attributes = Arr::except($this->validated(), [
            'roles',
            'school_id',
            'school_period_ids',
        ]);

        if ($user->scope === UserScope::SCHOOL) {
            $schoolPeriodIds = $this->validatedSchoolPeriodIdsForUser($user);
            $activeSchoolPeriodId = in_array((int) $user->organization_id, $schoolPeriodIds, true)
                ? (int) $user->organization_id
                : User::resolveDefaultActiveSchoolPeriodId($schoolPeriodIds);

            $attributes['organization_id'] = $activeSchoolPeriodId;
            $attributes['organization_type'] = SchoolPeriod::class;
        }

        return $attributes;
    }

    private function currentOfficeId(): ?int
    {
        /** @var User|null $user */
        $user = $this->user('education_services_office');

        return $user?->organization_id;
    }
}
