<?php

namespace App\Support\Navigation\Panels;

use App\Models\EducationMonitor;
use App\Models\School;
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
            [
                'title' => 'المُراقبات',
                'href' => route('warehouse.education-monitors.index'),
                'icon' => 'LandmarkIcon',
                'activeRoutes' => 'warehouse.education-monitors.*',
                'can' => $this->user?->canAny(['viewAny'], EducationMonitor::class),
            ],
            [
                'title' => 'المدارس',
                'href' => route('warehouse.schools.index'),
                'icon' => 'SchoolIcon',
                'activeRoutes' => 'warehouse.schools.*',
                'can' => $this->user?->canAny(['viewAny'], School::class),
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
