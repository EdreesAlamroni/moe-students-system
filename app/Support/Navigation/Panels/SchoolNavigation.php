<?php

namespace App\Support\Navigation\Panels;

use App\Models\GradeLevel;
use App\Models\User;
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
            [
                'title' => 'الصفوف الدراسية',
                'href' => route('school.grade-levels.index'),
                'icon' => 'GraduationCapIcon',
                'activeRoutes' => 'school.grade-levels.*',
                'can' => $this->user?->canAny(['viewAny'], GradeLevel::class),
            ],
        ];
    }

    protected function settings(): array
    {
        return [
            [
                'title' => 'المُستخدمين',
                'href' => route('school.users.index'),
                'icon' => 'UserRoundCogIcon',
                'activeRoutes' => 'school.users.*',
                'can' => $this->user?->canAny(['viewAny'], User::class),
            ],
        ];
    }
}
