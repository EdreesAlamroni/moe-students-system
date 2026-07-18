<?php

namespace App\Support\Navigation\Panels;

use App\Models\EducationServicesOffice;
use App\Models\School;
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
            [
                'title' => 'مكاتب الخدمات التعليمية',
                'href' => route('education-monitor.education-services-offices.index'),
                'icon' => 'BuildingIcon',
                'activeRoutes' => 'education-monitor.education-services-offices.*',
                'can' => $this->user?->canAny(['viewAny'], EducationServicesOffice::class),
            ],
            [
                'title' => 'المدارس',
                'href' => route('education-monitor.schools.index'),
                'icon' => 'SchoolIcon',
                'activeRoutes' => 'education-monitor.schools.*',
                'can' => $this->user?->canAny(['viewAny'], School::class),
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
