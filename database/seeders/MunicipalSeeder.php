<?php

namespace Database\Seeders;

use App\Models\Municipal;
use Illuminate\Database\Seeder;

class MunicipalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->municipals() as $attributes) {
            Municipal::create($attributes);
        }
    }

    protected function municipals(): array
    {
        return [
            ['name' => 'اجدابيا'],
            ['name' => 'الأبيار'],
            ['name' => 'البركت'],
            ['name' => 'البيضاء'],
            ['name' => 'الجغبوب'],
            ['name' => 'الجميل'],
            ['name' => 'الحوامد'],
            ['name' => 'الخمس'],
            ['name' => 'الزاوية'],
            ['name' => 'الزنتان'],
            ['name' => 'الزهراء'],
            ['name' => 'العجيلات'],
            ['name' => 'الفجيج'],
            ['name' => 'الفقهاء'],
            ['name' => 'القربولي'],
            ['name' => 'الكفرة'],
            ['name' => 'المرج'],
            ['name' => 'ام الارانب'],
            ['name' => 'اوباري'],
            ['name' => 'بنغازي'],
            ['name' => 'بني وليد'],
            ['name' => 'تراغن'],
            ['name' => 'ترهونة'],
            ['name' => 'تمزاوة'],
            ['name' => 'تمسان'],
            ['name' => 'تهالة'],
            ['name' => 'جالو'],
            ['name' => 'درج'],
            ['name' => 'درنة'],
            ['name' => 'رقدالين'],
            ['name' => 'زلطن'],
            ['name' => 'زلة'],
            ['name' => 'زليتن'],
            ['name' => 'زوارة'],
            ['name' => 'زويلة'],
            ['name' => 'سبها'],
            ['name' => 'سرت'],
            ['name' => 'اوجلة'],
            ['name' => 'صبراتة'],
            ['name' => 'صرمان'],
            ['name' => 'طبرق'],
            ['name' => 'طرابلس'],
            ['name' => 'غات'],
            ['name' => 'غدامس'],
            ['name' => 'غريان'],
            ['name' => 'مرادة'],
            ['name' => 'مرزق'],
            ['name' => 'مسلاتة'],
            ['name' => 'مصراتة'],
            ['name' => 'نالوت'],
            ['name' => 'هون'],
            ['name' => 'ودان'],
            ['name' => 'يفرن'],
            ['name' => 'سوكنة'],
            ['name' => 'شحات'],
        ];
    }
}
