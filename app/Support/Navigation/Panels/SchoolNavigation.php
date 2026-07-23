<?php

namespace App\Support\Navigation\Panels;

use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\Student;
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
            [
                'title' => 'الفصول الدراسية',
                'href' => route('school.classrooms.index'),
                'icon' => 'PresentationIcon',
                'activeRoutes' => 'school.classrooms.*',
                'can' => $this->user?->canAny(['viewAny'], Classroom::class),
            ],
            [
                'title' => 'الطلاب',
                'href' => route('school.students.index'),
                'icon' => 'UsersIcon',
                'activeRoutes' => 'school.students.*',
                'excludedRoutes' => ['school.students.unenrolled-from-grade-level.*', 'school.students.unenrolled-from-classroom.*'],
                'can' => $this->user?->canAny(['viewAny'], Student::class),
            ],
            [
                'title' => 'الطلاب غير المسجّلين في صفوف دراسية',
                'href' => route('school.students.unenrolled-from-grade-level.index'),
                'icon' => 'UserXIcon',
                'activeRoutes' => 'school.students.unenrolled-from-grade-level.*',
                'can' => $this->user?->canAny(['viewAny'], Student::class),
            ],
            [
                'title' => 'الطلاب غير المسجّلين في فصول دراسية',
                'href' => route('school.students.unenrolled-from-classroom.index'),
                'icon' => 'UserXIcon',
                'activeRoutes' => 'school.students.unenrolled-from-classroom.*',
                'can' => $this->user?->canAny(['viewAny'], Student::class),
            ],
            [
                'title' => 'توزيع الطلاب على الفصول',
                'href' => '#',
                'icon' => 'TableOfContentsIcon',
                'activeRoutes' => 'school.classroom-distribution.*',
                'can' => true,
            ],
            [
                'title' => 'توزيع الكُتب المدرسية',
                'href' => '#',
                'icon' => 'BookTextIcon',
                'activeRoutes' => 'school.book-distributions.*',
                'can' => true,
            ],
        ];
    }

    protected function reports(): array
    {
        return [
            [
                'title' => 'تقرير الطلاب حسب الصفوف',
                'href' => '#',
                'icon' => 'ClipboardList',
                'routeIs' => 'school.reports.students-by-grade-level.*',
                'can' => true,
            ],
            [
                'title' => 'تقرير الطلاب حسب الفصول',
                'href' => '#',
                'icon' => 'ClipboardList',
                'routeIs' => 'school.reports.students-by-classroom.*',
                'can' => true,
            ],
            [
                'title' => 'تقرير الغياب',
                'href' => '#',
                'icon' => 'ClipboardList',
                'routeIs' => 'school.reports.attendance.*',
                'can' => true,
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
                'can' => $this->user?->can(['viewAny'], User::class),
            ],
        ];
    }
}
