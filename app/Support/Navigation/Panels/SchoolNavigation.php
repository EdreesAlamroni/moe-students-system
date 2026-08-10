<?php

namespace App\Support\Navigation\Panels;

use App\Authorization\School\AttendanceReport;
use App\Authorization\School\ClassroomDistribution;
use App\Authorization\School\StudentByClassroomReport;
use App\Authorization\School\StudentByGradeLevelReport;
use App\Models\BookDistribution;
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
                'title' => 'الطلبة',
                'href' => route('school.students.index'),
                'icon' => 'UsersIcon',
                'activeRoutes' => 'school.students.*',
                'excludedRoutes' => ['school.students.unenrolled-from-grade-level.*', 'school.students.unenrolled-from-classroom.*'],
                'can' => $this->user?->canAny(['viewAny'], Student::class),
            ],
            [
                'title' => 'الطلبة غير المسجّلين في صفوف دراسية',
                'href' => route('school.students.unenrolled-from-grade-level.index'),
                'icon' => 'UserXIcon',
                'activeRoutes' => 'school.students.unenrolled-from-grade-level.*',
                'can' => $this->user?->canAny(['viewAny'], Student::class),
            ],
            [
                'title' => 'الطلبة غير المسجّلين في فصول دراسية',
                'href' => route('school.students.unenrolled-from-classroom.index'),
                'icon' => 'UserXIcon',
                'activeRoutes' => 'school.students.unenrolled-from-classroom.*',
                'can' => $this->user?->canAny(['viewAny'], Student::class),
            ],
            [
                'title' => 'توزيع الطلبة على الفصول',
                'href' => route('school.classroom-distribution.index'),
                'icon' => 'TableOfContentsIcon',
                'activeRoutes' => 'school.classroom-distribution.*',
                'can' => $this->user?->canAny(['view'], ClassroomDistribution::class),
            ],
            [
                'title' => 'توزيع الكُتب المدرسية',
                'href' => route('school.book-distributions.index'),
                'icon' => 'BookTextIcon',
                'activeRoutes' => 'school.book-distributions.*',
                'can' => $this->user?->canAny(['view'], BookDistribution::class),
            ],
        ];
    }

    protected function reports(): array
    {
        return [
            [
                'title' => 'تقرير الطلبة حسب الصفوف',
                'href' => route('school.reports.students-by-grade-level.index'),
                'icon' => 'ClipboardList',
                'activeRoutes' => 'school.reports.students-by-grade-level.*',
                'can' => $this->user?->canAny(['view'], StudentByGradeLevelReport::class),
            ],
            [
                'title' => 'تقرير الطلبة حسب الفصول',
                'href' => route('school.reports.students-by-classroom.index'),
                'icon' => 'ClipboardList',
                'activeRoutes' => 'school.reports.students-by-classroom.*',
                'can' => $this->user?->canAny(['view'], StudentByClassroomReport::class),
            ],
            [
                'title' => 'تقرير الغياب',
                'href' => route('school.reports.attendance.index'),
                'icon' => 'ClipboardList',
                'activeRoutes' => 'school.reports.attendance.*',
                'can' => $this->user?->canAny(['view'], AttendanceReport::class),
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
