import React from 'react';

import { Head, Link, usePage } from '@inertiajs/react';

import type { CanPermissions, ClassScheduleGrid, ClassScheduleGridItem, Enum, Subject } from '@/types';

import { Card, CardContent, CardHeader, CardTableContent, CardTitle } from '@/components/ui/structure/card';
import MainContainer from '@/components/ui/structure/main-container';
import ActionsSection from '@/components/ui/structure/actions-section';

import EmptyState from '@/components/ui/display/empty-state';
import { TableCellNullableValue } from '@/components/ui/display/table';

import { Button } from '@/components/ui/actions/button';

import { hasScheduleItems, ScheduleGridTable } from '@/components/features/school/class-schedules/schedule-grid-table';

import { CalendarDaysIcon, PrinterIcon, SquarePenIcon } from 'lucide-react';

import { index as classroomIndex, show as classroomShow } from '@/routes/school/classrooms';
import { show, edit, print } from '@/routes/school/classrooms/class-schedules';

type PageProps = {
    classroomName: string;
    schedule: ClassScheduleGrid;
    subjects: Subject[];
    days: Enum[];
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({ classroomName, schedule, subjects, days, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const hasPeriods = schedule.periods.length > 0;
    const hasSubjects = subjects.length > 0;
    const hasSchedule = hasScheduleItems(schedule);
    const canEditSchedule = hasPeriods && hasSubjects;

    return (
        <>
            <Head title="عرض الجدول الدراسي" />

            <MainContainer changeAcademicYearNotice>
                {canAny && currentAcademicYear?.is_active && (
                    <ActionsSection>
                        {can.update && (
                            <Button variant="default" disabled={!canEditSchedule} asChild>
                                <Link href={edit.url({ classroom: schedule.classroom })}>
                                    <SquarePenIcon />
                                    <span>تعديل الجدول</span>
                                </Link>
                            </Button>
                        )}

                        {can.print && (
                            <Button variant="outline" disabled={!hasSchedule} asChild>
                                {hasSchedule ? (
                                    <a
                                        href={print.url({ classroom: schedule.classroom })}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <PrinterIcon />
                                        <span>طباعة</span>
                                    </a>
                                ) : (
                                    <span className="flex cursor-not-allowed items-center gap-1 opacity-50">
                                        <PrinterIcon />
                                        <span>طباعة</span>
                                    </span>
                                )}
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <CalendarDaysIcon />
                                <span>الجدول الدراسي — {classroomName}</span>
                            </CardTitle>
                        </CardHeader>

                        {hasPeriods && hasSchedule ? (
                            <CardTableContent>
                                <ScheduleGridTable
                                    periods={schedule.periods}
                                    days={days}
                                    renderCell={(period, day) => {
                                        const item = schedule.grid[period.id]?.[parseInt(day.id, 10)];

                                        return renderScheduleCell(period, item);
                                    }}
                                />
                            </CardTableContent>
                        ) : (
                            <CardContent>
                                <EmptyState
                                    text={
                                        hasPeriods
                                            ? 'لم يتم إعداد الجدول الدراسي لهذا الفصل الدراسي بعد.'
                                            : 'لا توجد حصص دراسية مُعرَّفة للسنة الدراسية الحالية.'
                                    }
                                />
                            </CardContent>
                        )}
                    </Card>
                </section>
            </MainContainer>
        </>
    );
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الفصول الدراسية',
            href: classroomIndex.url(),
        },
        {
            title: 'عرض بيانات الفصل الدراسي',
            href: classroomShow.url({ classroom: props.schedule.classroom }),
        },
        {
            title: 'عرض الجدول الدراسي',
            href: show.url({ classroom: props.schedule.classroom }),
        },
    ],
});

function renderScheduleCell(
    period: ClassScheduleGrid['periods'][number],
    item: ClassScheduleGridItem | null | 'break' | undefined,
): React.ReactNode {
    if (period.is_break) {
        return <span className="text-sm text-muted-foreground">استراحة</span>;
    }

    if (!item || item === 'break') {
        return <TableCellNullableValue />;
    }

    return <span className="text-sm font-medium">{item.subject?.name ?? '—'}</span>;
}
