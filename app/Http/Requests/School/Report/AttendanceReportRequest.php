<?php

namespace App\Http\Requests\School\Report;

use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttendanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('school')->check();
    }

    public function rules(): array
    {
        return [
            'classroom_id' => [
                'required',
                'integer',
                Rule::exists(Classroom::class, 'id')
                    ->where('academic_year_id', AcademicYear::currentId())
                    ->where('school_period_id', auth('school')->user()->organization_id),
            ],
            'month' => [
                'required',
                'integer',
                'between:1,12',
            ],
        ];
    }

    public function getAttributes(): array
    {
        $classroom = Classroom::query()
            ->forCurrentSchoolAndAcademicYear()
            ->with(['gradeLevel:id,name'])
            ->where('id', '=', $this->integer('classroom_id'))
            ->first();

        return [
            'classroom' => $classroom,
            'classroom_id' => $classroom->id,
            'month' => $this->integer('month'),
        ];
    }
}
