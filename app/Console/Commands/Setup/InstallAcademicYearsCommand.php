<?php

namespace App\Console\Commands\Setup;

use App\Models\AcademicYear;
use App\Support\AcademicYearCalendar;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('setup:install-academic-years')]
#[Description('Install academic year records')]
class InstallAcademicYearsCommand extends Command
{
    private const HISTORY_YEARS = 30;

    public function handle(): int
    {
        if (AcademicYear::query()->exists()) {
            $this->components->info('Academic years already installed. Skipping.');

            return self::SUCCESS;
        }

        if (! $this->input->isInteractive()) {
            $this->components->error('This command must be run interactively so you can choose the active academic year start year.');

            return self::FAILURE;
        }

        $startYear = (int) $this->components->ask('Start year for the active academic year (e.g. 2026 creates 2027/2026)');

        if ($startYear <= 0) {
            $this->components->error('The start year must be a valid number.');

            return self::FAILURE;
        }

        $created = 0;

        $historyYears = AcademicYearCalendar::historicalDefinitions(self::HISTORY_YEARS, $startYear);

        foreach ($historyYears as $attributes) {
            $year = AcademicYear::query()->firstOrCreate([
                'name' => $attributes['name'],
            ], $attributes);

            if ($year->wasRecentlyCreated) {
                $created++;
            }
        }

        $activeYear = AcademicYear::query()
            ->where('name', '=', AcademicYearCalendar::nameForStartYear($startYear))
            ->firstOrFail();

        AcademicYear::activate($activeYear);

        $info = sprintf(
            'Academic years installed successfully (%d created, %d total). Active year: %s.',
            $created,
            AcademicYear::query()->count(),
            $activeYear->name,
        );

        $this->components->info($info);

        return self::SUCCESS;
    }
}
