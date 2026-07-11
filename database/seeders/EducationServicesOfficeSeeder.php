<?php

namespace Database\Seeders;

use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use Illuminate\Database\Seeder;
use RuntimeException;

class EducationServicesOfficeSeeder extends Seeder
{
    public function run(): void
    {
        $monitor = EducationMonitor::query()
            ->whereHas('municipal', function ($query): void {
                $query->where('name', '=', 'بنغازي');
            })
            ->first();

        if ($monitor === null) {
            throw new RuntimeException('Unable to seed education services offices: missing بنغازي education monitor.');
        }

        foreach ($this->offices() as $name) {
            EducationServicesOffice::query()->updateOrCreate(
                [
                    'education_monitor_id' => $monitor->id,
                    'name' => $name,
                ],
                [],
            );
        }
    }

    protected function offices(): array
    {
        return [
            'بنغازي المركز',
            'السلاوي',
            'غرب بنغازي',
        ];
    }
}
