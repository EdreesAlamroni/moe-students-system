<?php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->nationalities() as $attributes) {
            Nationality::create($attributes);
        }
    }

    protected function nationalities(): array
    {
        return [
            [
                'name' => 'ليبي',
                'code' => Nationality::LIBYA_CODE,
            ],
            [
                'name' => 'أردني',
                'code' => 'JO',
            ],
            [
                'name' => 'إماراتي',
                'code' => 'AE',
            ],
            [
                'name' => 'بحريني',
                'code' => 'BH',
            ],
            [
                'name' => 'تونسي',
                'code' => 'TN',
            ],
            [
                'name' => 'جزائري',
                'code' => 'DZ',
            ],
            [
                'name' => 'جزر القمر',
                'code' => 'KM',
            ],
            [
                'name' => 'جيبوتي',
                'code' => 'DJ',
            ],
            [
                'name' => 'سعودي',
                'code' => 'SA',
            ],
            [
                'name' => 'سوداني',
                'code' => 'SD',
            ],
            [
                'name' => 'سوري',
                'code' => 'SY',
            ],
            [
                'name' => 'صومالي',
                'code' => 'SO',
            ],
            [
                'name' => 'عماني',
                'code' => 'OM',
            ],
            [
                'name' => 'عراقي',
                'code' => 'IQ',
            ],
            [
                'name' => 'فلسطيني',
                'code' => 'PS',
            ],
            [
                'name' => 'قطري',
                'code' => 'QA',
            ],
            [
                'name' => 'كويتي',
                'code' => 'KW',
            ],
            [
                'name' => 'لبناني',
                'code' => 'LB',
            ],
            [
                'name' => 'مصري',
                'code' => 'EG',
            ],
            [
                'name' => 'مغربي',
                'code' => 'MA',
            ],
            [
                'name' => 'موريتاني',
                'code' => 'MR',
            ],
            [
                'name' => 'يمني',
                'code' => 'YE',
            ],
        ];
    }
}
