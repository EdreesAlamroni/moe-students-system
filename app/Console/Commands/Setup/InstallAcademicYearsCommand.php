<?php

namespace App\Console\Commands\Setup;

use App\Models\AcademicYear;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('setup:install-academic-years')]
#[Description('Install academic year records')]
class InstallAcademicYearsCommand extends Command
{
    private const HISTORY_YEARS = 20;

    public function handle(): int
    {
        if (AcademicYear::query()->exists()) {
            $this->components->info('Academic years already installed. Skipping.');

            return self::SUCCESS;
        }

        foreach ($this->definitions() as $attributes) {
            AcademicYear::create($attributes);
        }

        $this->components->info('Academic years installed successfully.');

        return self::SUCCESS;
    }

    private function definitions(): array
    {
        $today = now();

        $currentStartYear = $today->month >= 9
            ? $today->year
            : $today->year - 1;

        $years = [];

        for ($offset = self::HISTORY_YEARS; $offset >= 0; $offset--) {
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
