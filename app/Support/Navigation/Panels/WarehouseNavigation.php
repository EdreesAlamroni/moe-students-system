<?php

namespace App\Support\Navigation\Panels;

use App\Models\BookDistribution;
use App\Models\EducationMonitor;
use App\Models\School;
use App\Models\User;
use App\Support\Navigation\NavigationPanel;

class WarehouseNavigation extends NavigationPanel
{
    protected function main(): array
    {
        return [
            [
                'title' => 'الرئيسية',
                'href' => route('warehouse.dashboard'),
                'icon' => 'LayoutGridIcon',
                'activeRoutes' => 'warehouse.dashboard',
                'can' => true,
            ],
            [
                'title' => 'المُراقبات',
                'href' => route('warehouse.education-monitors.index'),
                'icon' => 'LandmarkIcon',
                'activeRoutes' => 'warehouse.education-monitors.*',
                'can' => $this->user?->canAny(['viewAny'], EducationMonitor::class),
            ],
            [
                'title' => 'المدارس',
                'href' => route('warehouse.schools.index'),
                'icon' => 'SchoolIcon',
                'activeRoutes' => 'warehouse.schools.*',
                'can' => $this->user?->canAny(['viewAny'], School::class),
            ],
            [
                'title' => 'توزيع الكُتب المدرسية',
                'href' => route('warehouse.book-distributions.index'),
                'icon' => 'BookTextIcon',
                'activeRoutes' => 'warehouse.book-distributions.*',
                'excludedRoutes' => ['warehouse.book-distributions.statistics', 'warehouse.book-distributions.students'],
                'can' => $this->user?->canAny(['view'], BookDistribution::class),
            ],
            [
                'title' => 'إحصائيات توزيع الكُتب المدرسية',
                'href' => route('warehouse.book-distributions.statistics'),
                'icon' => 'BarChart3Icon',
                'activeRoutes' => 'warehouse.book-distributions.statistics',
                'can' => $this->user?->can('viewStatistics', BookDistribution::class),
            ],
            [
                'title' => 'حالة توزيع الكُتب المدرسية للطلاب',
                'href' => route('warehouse.book-distributions.students'),
                'icon' => 'SearchIcon',
                'activeRoutes' => 'warehouse.book-distributions.students',
                'can' => $this->user?->can('view', BookDistribution::class),
            ],
        ];
    }

    protected function reports(): array
    {
        return [
            [
                'title' => 'تقرير إحصائيات توزيع الكُتب المدرسية',
                'href' => route('warehouse.reports.book-distributions.index'),
                'icon' => 'ClipboardList',
                'activeRoutes' => 'warehouse.reports.book-distributions.*',
                'can' => $this->user?->can('viewStatistics', BookDistribution::class),
            ],
        ];
    }

    protected function settings(): array
    {
        return [
            [
                'title' => 'المُستخدمين',
                'href' => route('warehouse.users.index'),
                'icon' => 'UserRoundCogIcon',
                'activeRoutes' => 'warehouse.users.*',
                'can' => $this->user?->canAny(['viewAny'], User::class),
            ],
        ];
    }
}
