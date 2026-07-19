<?php

namespace App\Support\Navigation\Panels;

use App\Models\School;
use App\Models\Student;
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
            [
                'title' => 'المدارس',
                'href' => route('education-services-office.schools.index'),
                'icon' => 'SchoolIcon',
                'activeRoutes' => 'education-services-office.schools.*',
                'can' => $this->user?->canAny(['viewAny'], School::class),
            ],
            [
                'title' => 'الطلاب',
                'href' => route('education-services-office.students.index'),
                'icon' => 'UsersIcon',
                'activeRoutes' => 'education-services-office.students.*',
                'can' => $this->user?->canAny(['viewAny'], Student::class),
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
