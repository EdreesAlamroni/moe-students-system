<?php

namespace Database\Seeders;

use App\Enums\GradeLevelEnum;
use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class GradeLevelSeeder extends Seeder
{
    public function run(): void
    {
        foreach (GradeLevelEnum::cases() as $grade) {
            GradeLevel::firstOrCreate([
                'code' => $grade->value,
            ], [
                'name' => $grade->label(),
                'educational_stage' => $grade->stage(),
                'order' => $grade->order(),
            ]);
        }
    }
}
