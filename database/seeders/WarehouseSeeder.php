<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->warehouses() as $name) {
            Warehouse::query()->updateOrCreate(
                ['name' => $name],
                [],
            );
        }
    }

    protected function warehouses(): array
    {
        return [
            'مخزن توزيع بنغازي',
            'مخزن توزيع البيضاء',
            'مخزن توزيع المرج',
            'مخزن توزيع طبرق',
            'مخزن توزيع درنة',
            'مخزن توزيع أجدابيا',
        ];
    }
}
