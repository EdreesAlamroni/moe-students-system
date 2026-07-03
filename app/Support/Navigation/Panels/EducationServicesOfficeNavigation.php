<?php

namespace App\Support\Navigation\Panels;

use App\Support\Navigation\NavigationPanel;

class EducationServicesOfficeNavigation extends NavigationPanel
{
    protected function main(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'href' => route('education-services-office.dashboard'),
                'icon' => 'LayoutGridIcon',
                'activeRoutes' => 'education-services-office.dashboard',
                'can' => true,
            ],
        ];
    }
}
