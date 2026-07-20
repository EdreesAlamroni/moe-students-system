<?php

namespace App\Http\Requests\School\ClassSchedule;

use App\Enums\DayOfWeek;
use App\Models\AcademicYear;
use App\Models\ClassPeriod;
use App\Models\Classroom;
use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BulkUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'items' => [
                'required',
                'array',
            ],
            'items.*.class_period_id' => [
                'required',
                Rule::exists(ClassPeriod::class, 'id')
                    ->where('academic_year_id', AcademicYear::currentId()),
            ],
            'items.*.day_of_week' => [
                'required',
                Rule::enum(DayOfWeek::class),
            ],
            'items.*.subject_id' => [
                'required',
                Rule::exists(Subject::class, 'id')
                    ->where('grade_level_id', $this->route('classroom.grade_level_id')),
            ],
            'items.*.notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => __('validation.custom.class_schedule.items_required'),
            'items.array' => __('validation.custom.class_schedule.items_array'),
            'items.*.class_period_id.required' => __('validation.custom.class_schedule.class_period_id_required'),
            'items.*.class_period_id.exists' => __('validation.custom.class_schedule.class_period_id_invalid'),
            'items.*.day_of_week.required' => __('validation.custom.class_schedule.day_of_week_required'),
            'items.*.day_of_week.enum' => __('validation.custom.class_schedule.day_of_week_invalid'),
            'items.*.subject_id.required' => __('validation.custom.class_schedule.subject_id_required'),
            'items.*.subject_id.exists' => __('validation.custom.class_schedule.subject_id_invalid'),
            'items.*.notes.string' => __('validation.custom.class_schedule.notes_string'),
            'items.*.notes.max' => __('validation.custom.class_schedule.notes_max'),
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Classroom $classroom */
            $classroom = $this->route('classroom');

            $hasSubjects = Subject::query()
                ->where('grade_level_id', '=', $classroom->grade_level_id)
                ->exists();

            if (! $hasSubjects) {
                $validator->errors()->add('items', __('validation.custom.class_schedule.no_subjects_available'));
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items', []);

        $this->merge([
            'items' => json_decode($items, true),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getScheduleItems(): array
    {
        $items = [];

        foreach ($this->input('items', []) as $item) {
            $items[] = [
                'class_period_id' => (int) $item['class_period_id'],
                'day_of_week' => DayOfWeek::from((int) $item['day_of_week']),
                'subject_id' => (int) $item['subject_id'],
                'notes' => $item['notes'] ?? null,
            ];
        }

        return $items;
    }
}
