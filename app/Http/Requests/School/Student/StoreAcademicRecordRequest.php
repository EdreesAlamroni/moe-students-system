<?php

namespace App\Http\Requests\School\Student;

use App\Enums\AcademicRecordRating;
use App\Enums\AcademicRecordStatus;
use App\Models\AcademicYear;
use App\Models\GradeLevel;
use App\Models\Student;
use App\Services\School\AcademicRecordService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAcademicRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists(AcademicYear::class, 'id'),
            ],
            'grade_level_id' => [
                'required',
                'integer',
                Rule::exists(GradeLevel::class, 'id'),
            ],
            'status' => [
                'required',
                Rule::in([
                    AcademicRecordStatus::PASSED->value,
                    AcademicRecordStatus::FAILED->value,
                ]),
            ],
            'rating' => [
                'nullable',
                Rule::requiredIf(function (): bool {
                    return $this->enum('status', AcademicRecordStatus::class) === AcademicRecordStatus::PASSED;
                }),
                Rule::prohibitedIf(function (): bool {
                    return $this->enum('status', AcademicRecordStatus::class) === AcademicRecordStatus::FAILED;
                }),
                Rule::enum(AcademicRecordRating::class),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Student $student */
                $student = $this->route('student');
                $gradeLevel = GradeLevel::query()->find($this->integer('grade_level_id'));

                if (! $gradeLevel instanceof GradeLevel) {
                    return;
                }

                $service = app(AcademicRecordService::class);

                if (! $service->canAddAttempt($student, $gradeLevel)) {
                    $validator->errors()->add(
                        'grade_level_id',
                        __('validation.custom.academic_record.invalid_grade_level')
                    );
                }

                if ($service->studentHasAcademicYearRecord($student, $this->integer('academic_year_id'))) {
                    $validator->errors()->add(
                        'academic_year_id',
                        __('validation.custom.academic_record.duplicate_academic_year')
                    );
                }
            },
        ];
    }

    /**
     * @return array{grade_level_id: int, academic_year_id: int, status: AcademicRecordStatus, rating: AcademicRecordRating|null}
     */
    public function getAttributes(): array
    {
        $validated = $this->validated();
        $submittedStatus = AcademicRecordStatus::from($validated['status']);

        /** @var Student $student */
        $student = $this->route('student');
        $gradeLevel = GradeLevel::query()->findOrFail(intval($validated['grade_level_id']));

        $service = app(AcademicRecordService::class);
        $status = $service->resolveAttemptStatus($student, $gradeLevel, $submittedStatus);

        return [
            'grade_level_id' => intval($validated['grade_level_id']),
            'academic_year_id' => intval($validated['academic_year_id']),
            'status' => $status,
            'rating' => $status->isPassing() && isset($validated['rating'])
                ? AcademicRecordRating::from($validated['rating'])
                : null,
        ];
    }
}
