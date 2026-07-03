<?php

namespace App\Support\Navigation\Panels;

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
}
