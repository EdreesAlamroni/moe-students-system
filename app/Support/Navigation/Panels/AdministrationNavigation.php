<?php

namespace App\Support\Navigation\Panels;

use App\Models\AcademicYear;
use App\Models\EducationMonitor;
use App\Models\EducationServicesOffice;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\Navigation\NavigationPanel;

class AdministrationNavigation extends NavigationPanel
{
    protected function main(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'href' => route('administration.dashboard'),
                'icon' => 'LayoutGridIcon',
                'activeRoutes' => 'administration.dashboard',
                'can' => true,
            ],
            [
                'title' => 'السنوات الدراسية',
                'href' => route('administration.academic-years.index'),
                'icon' => 'CalendarRangeIcon',
                'activeRoutes' => 'administration.academic-years.*',
                'can' => $this->user?->canAny(['viewAny'], AcademicYear::class),
            ],
            [
                'title' => 'الصفوف الدراسية',
                'href' => route('administration.grade-levels.index'),
                'icon' => 'GraduationCapIcon',
                'activeRoutes' => 'administration.grade-levels.*',
                'can' => $this->user?->canAny(['viewAny'], GradeLevel::class),
            ],
            [
                'title' => 'المقررات الدراسية',
                'href' => route('administration.subjects.index'),
                'icon' => 'BookTextIcon',
                'activeRoutes' => 'administration.subjects.*',
                'can' => $this->user?->canAny(['viewAny'], Subject::class),
            ],
            [
                'title' => 'الحصص الدراسية',
                'href' => '#',
                'icon' => 'ClockIcon',
                'activeRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'المخازن',
                'href' => route('administration.warehouses.index'),
                'icon' => 'WarehouseIcon',
                'activeRoutes' => 'administration.warehouses.*',
                'can' => $this->user?->canAny(['viewAny'], Warehouse::class),
            ],
            [
                'title' => 'المُراقبات',
                'href' => route('administration.education-monitors.index'),
                'icon' => 'LandmarkIcon',
                'activeRoutes' => 'administration.education-monitors.*',
                'can' => $this->user?->canAny(['viewAny'], EducationMonitor::class),
            ],
            [
                'title' => 'مكاتب الخدمات التعليمية',
                'href' => route('administration.education-services-offices.index'),
                'icon' => 'BuildingIcon',
                'activeRoutes' => 'administration.education-services-offices.*',
                'can' => $this->user?->canAny(['viewAny'], EducationServicesOffice::class),
            ],
            [
                'title' => 'المدارس',
                'href' => '#',
                'icon' => 'SchoolIcon',
                'activeRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'الطلاب',
                'href' => '#',
                'icon' => 'UsersIcon',
                'activeRoutes' => false,
                'excludedRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'الطلاب غير المسجّلين في مُراقبات',
                'href' => '#',
                'icon' => 'UserXIcon',
                'activeRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'الطلاب غير المسجّلين في مدارس',
                'href' => '#',
                'icon' => 'UserXIcon',
                'activeRoutes' => false,
                'can' => true,
            ],
        ];
    }

    protected function reports(): array
    {
        return [
            [
                'title' => 'تقرير المُراقبات',
                'href' => '#',
                'icon' => 'ClipboardList',
                'activeRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'تقرير مكاتب الخدمات التعليمية',
                'href' => '#',
                'icon' => 'ClipboardList',
                'activeRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'تقرير المدارس',
                'href' => '#',
                'icon' => 'ClipboardList',
                'activeRoutes' => false,
                'can' => true,
            ],
        ];
    }

    protected function settings(): array
    {
        return [
            [
                'title' => 'تصنيفات المقررات الدراسية',
                'href' => '#',
                'icon' => 'LibraryBigIcon',
                'activeRoutes' => false,
                'can' => true,
            ],
            [
                'title' => 'المُستخدمين',
                'href' => route('administration.users.index'),
                'icon' => 'UserRoundCogIcon',
                'activeRoutes' => 'administration.users.*',
                'can' => $this->user?->canAny(['viewAny'], User::class),
            ],
            [
                'title' => 'البلديات',
                'href' => route('administration.municipals.index'),
                'icon' => 'MapPinnedIcon',
                'activeRoutes' => 'administration.municipals.*',
                'can' => true,
            ],
        ];
    }
}
