<?php

namespace App\Support\Navigation\Panels;

use App\Models\User;
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

    protected function settings(): array
    {
        return [
            [
                'title' => 'المُستخدمين',
                'href' => route('warehouse.users.index'),
                'icon' => 'UserRoundCogIcon',
                'activeRoutes' => 'warehouse.users.*',
                'can' => $this->user?->canAny(['viewAny'], User::class),
            ],
        ];
    }
}
