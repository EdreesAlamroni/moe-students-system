<?php

namespace App\Support\Navigation\Panels;

use App\Models\User;
use App\Support\Navigation\NavigationPanel;

class EducationMonitorNavigation extends NavigationPanel
{
    protected function main(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'href' => route('education-monitor.dashboard'),
                'icon' => 'LayoutGridIcon',
                'activeRoutes' => 'education-monitor.dashboard',
                'can' => true,
            ],
        ];
    }

    protected function settings(): array
    {
        return [
            [
                'title' => 'المُستخدمين',
                'href' => route('education-monitor.users.index'),
                'icon' => 'UserRoundCogIcon',
                'activeRoutes' => 'education-monitor.users.*',
                'can' => $this->user?->canAny(['viewAny'], User::class),
            ],
        ];
    }
}
