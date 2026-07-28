<?php

namespace App\Console\Commands\Setup;

use App\Models\Nationality;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('setup:install-nationalities')]
#[Description('Install nationality records')]
class InstallNationalitiesCommand extends Command
{
    public function handle(): int
    {
        if (Nationality::query()->exists()) {
            $this->components->info('Nationalities already installed. Skipping.');

            return self::SUCCESS;
        }

        foreach ($this->definitions() as $attributes) {
            Nationality::create($attributes);
        }

        $this->components->info('Nationalities installed successfully.');

        return self::SUCCESS;
    }

    private function definitions(): array
    {
        return [
            ['name' => 'ليبي', 'code' => Nationality::LIBYA_CODE],
            ['name' => 'أردني', 'code' => 'JO'],
            ['name' => 'إماراتي', 'code' => 'AE'],
            ['name' => 'بحريني', 'code' => 'BH'],
            ['name' => 'تونسي', 'code' => 'TN'],
            ['name' => 'جزائري', 'code' => 'DZ'],
            ['name' => 'جزر القمر', 'code' => 'KM'],
            ['name' => 'جيبوتي', 'code' => 'DJ'],
            ['name' => 'سعودي', 'code' => 'SA'],
            ['name' => 'سوداني', 'code' => 'SD'],
            ['name' => 'سوري', 'code' => 'SY'],
            ['name' => 'صومالي', 'code' => 'SO'],
            ['name' => 'عماني', 'code' => 'OM'],
            ['name' => 'عراقي', 'code' => 'IQ'],
            ['name' => 'فلسطيني', 'code' => 'PS'],
            ['name' => 'قطري', 'code' => 'QA'],
            ['name' => 'كويتي', 'code' => 'KW'],
            ['name' => 'لبناني', 'code' => 'LB'],
            ['name' => 'مصري', 'code' => 'EG'],
            ['name' => 'مغربي', 'code' => 'MA'],
            ['name' => 'موريتاني', 'code' => 'MR'],
            ['name' => 'يمني', 'code' => 'YE'],
        ];
    }
}
