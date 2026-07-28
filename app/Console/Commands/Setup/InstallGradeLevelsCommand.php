<?php

namespace App\Console\Commands\Setup;

use App\Enums\GradeLevelEnum;
use App\Models\GradeLevel;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('setup:install-grade-levels')]
#[Description('Install grade level records')]
class InstallGradeLevelsCommand extends Command
{
    public function handle(): int
    {
        if (GradeLevel::query()->exists()) {
            $this->components->info('Grade levels already installed. Skipping.');

            return self::SUCCESS;
        }

        foreach (GradeLevelEnum::cases() as $grade) {
            GradeLevel::firstOrCreate([
                'code' => $grade->value,
            ], [
                'name' => $grade->label(),
                'educational_stage' => $grade->stage(),
                'order' => $grade->order(),
            ]);
        }

        $this->components->info('Grade levels installed successfully.');

        return self::SUCCESS;
    }
}
