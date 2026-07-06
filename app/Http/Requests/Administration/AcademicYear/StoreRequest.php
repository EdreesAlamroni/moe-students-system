<?php

namespace App\Http\Requests\Administration\AcademicYear;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('administration')->check();
    }

    public function rules(): array
    {
        $defaults = AcademicYear::defaultsForCreateForm();

        $minStartDate = $defaults['min_start_date'];
        $maxEndDate = $defaults['max_end_date'];

        return [
            'start_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                sprintf('after_or_equal:%s', $minStartDate),
            ],
            'end_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after:start_date',
                sprintf('before_or_equal:%s', $maxEndDate),
            ],
        ];
    }

    public function getAttributes(): array
    {
        $startDate = $this->validated('start_date');
        $endDate = $this->validated('end_date');

        $startYear = Carbon::parse($startDate)->year;
        $endYear = Carbon::parse($endDate)->year;

        return [
            'name' => sprintf('%d/%d', $startYear + 1, $endYear),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
        ];
    }
}
