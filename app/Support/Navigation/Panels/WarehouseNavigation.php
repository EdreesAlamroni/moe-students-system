<?php

namespace App\Support\Navigation\Panels;

use App\Support\Navigation\NavigationPanel;

class WarehouseNavigation extends NavigationPanel
{
    protected function main(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'href' => route('warehouse.dashboard'),
                'icon' => 'LayoutGridIcon',
                'activeRoutes' => 'warehouse.dashboard',
                'can' => true,
            ],
        ];
    }
}
