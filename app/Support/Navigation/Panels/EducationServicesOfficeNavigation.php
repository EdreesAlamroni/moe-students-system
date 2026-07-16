<?php

namespace App\Support\Navigation\Panels;

use App\Models\User;
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

    protected function settings(): array
    {
        return [
            [
                'title' => 'المُستخدمين',
                'href' => route('education-services-office.users.index'),
                'icon' => 'UserRoundCogIcon',
                'activeRoutes' => 'education-services-office.users.*',
                'can' => $this->user?->canAny(['viewAny'], User::class),
            ],
        ];
    }
}
