<?php

namespace App\Support\Navigation\Panels;

use App\Authorization\EducationServicesOffice\SchoolReport;
use App\Authorization\EducationServicesOffice\StudentCountByGradeLevelReport;
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

    protected function reports(): array
    {
        return [
            [
                'title' => 'تقرير المدارس',
                'href' => route('education-services-office.reports.schools.index'),
                'icon' => 'ClipboardList',
                'activeRoutes' => 'education-services-office.reports.schools.*',
                'can' => $this->user?->canAny(['view'], SchoolReport::class),
            ],
            [
                'title' => 'إحصائية الطلاب حسب الصفوف الدراسية',
                'href' => route('education-services-office.reports.student-count-by-grade-level.index'),
                'icon' => 'ClipboardList',
                'activeRoutes' => 'education-services-office.reports.student-count-by-grade-level.*',
                'can' => $this->user?->canAny(['view'], StudentCountByGradeLevelReport::class),
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
