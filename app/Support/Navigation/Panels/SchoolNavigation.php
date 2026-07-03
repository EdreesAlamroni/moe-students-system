<?php

namespace App\Support\Navigation\Panels;

use App\Support\Navigation\NavigationPanel;

class SchoolNavigation extends NavigationPanel
{
    protected function main(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'href' => route('school.dashboard'),
                'icon' => 'LayoutGridIcon',
                'activeRoutes' => 'school.dashboard',
                'can' => true,
            ],
        ];
    }
}
