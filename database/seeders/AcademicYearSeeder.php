<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->academicYears() as $attributes) {
            AcademicYear::create($attributes);
        }
    }

    protected function academicYears(): array
    {
        $today = now();

        $currentStartYear = $today->month >= 9
            ? $today->year
            : $today->year - 1;

        $years = [];

        for ($offset = 20; $offset >= 0; $offset--) {
            $startYear = $currentStartYear - $offset;

            $start = Carbon::create($startYear, 9, 1);
            $end = Carbon::create($startYear + 1, 6, 30);

            $years[] = [
                'name' => sprintf('%d/%d', $startYear + 1, $startYear),
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_active' => $offset === 0,
            ];
        }

        return $years;
    }
}
