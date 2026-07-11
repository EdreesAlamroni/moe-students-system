<?php

namespace Database\Seeders;

use App\Models\EducationMonitor;
use App\Models\Municipal;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use RuntimeException;

class EducationMonitorSeeder extends Seeder
{
    public function run(): void
    {
        $municipals = Municipal::query()
            ->select(['id', 'name'])
            ->get()
            ->keyBy('name');

        $warehouses = Warehouse::query()
            ->select(['id', 'name'])
            ->whereIn('name', array_column($this->monitors(), 'warehouse_name'))
            ->get()
            ->keyBy('name');

        foreach ($this->monitors() as $attributes) {
            $municipalName = $attributes['municipal_name'];
            $warehouseName = $attributes['warehouse_name'];

            $municipal = $municipals->get($municipalName) ?? $municipals->get($this->normalizeMunicipalName($municipalName));
            $warehouse = $warehouses->get($warehouseName);

            if ($municipal === null) {
                throw new RuntimeException("Unable to seed education monitor: missing municipal [{$municipalName}].");
            }

            if ($warehouse === null) {
                throw new RuntimeException("Unable to seed education monitor: missing warehouse [{$warehouseName}].");
            }

            EducationMonitor::query()->updateOrCreate(
                ['municipal_id' => $municipal->id],
                ['warehouse_id' => $warehouse->id],
            );
        }
    }

    protected function normalizeMunicipalName(string $municipalName): string
    {
        return str_replace(['أ', 'إ', 'آ'], 'ا', $municipalName);
    }

    protected function monitors(): array
    {
        return [
            [
                'municipal_name' => 'بنغازي',
                'warehouse_name' => 'مخزن توزيع بنغازي',
            ],
            [
                'municipal_name' => 'البيضاء',
                'warehouse_name' => 'مخزن توزيع البيضاء',
            ],
            [
                'municipal_name' => 'المرج',
                'warehouse_name' => 'مخزن توزيع المرج',
            ],
            [
                'municipal_name' => 'طبرق',
                'warehouse_name' => 'مخزن توزيع طبرق',
            ],
            [
                'municipal_name' => 'درنة',
                'warehouse_name' => 'مخزن توزيع درنة',
            ],
            [
                'municipal_name' => 'أجدابيا',
                'warehouse_name' => 'مخزن توزيع أجدابيا',
            ],
        ];
    }
}
