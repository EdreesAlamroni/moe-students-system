import React from 'react';

import { Form, Head, Link } from '@inertiajs/react';

import type { ClassScheduleGrid, Enum, Subject } from '@/types';

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/structure/card';
import MainContainer from '@/components/ui/structure/main-container';
import { FormLayout } from '@/components/ui/structure/form-layout';

import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';
import ValidationErrors from '@/components/ui/alerts/validation-errors';

import { Button } from '@/components/ui/actions/button';
import { UpdateButton } from '@/components/ui/actions/submit-button';

import { formatClassroomName, ScheduleGridTable } from '@/components/features/school/class-schedules/schedule-grid-table';

import { AlertTriangleIcon, CalendarDaysIcon, ReplyIcon } from 'lucide-react';

import { index as classroomIndex, show as classroomShow } from '@/routes/school/classrooms';
import { show, edit, update } from '@/routes/school/classrooms/class-schedules';

type ScheduleCell = {
    subject_id: number | null;
};

type ScheduleItem = {
    class_period_id: number;
    day_of_week: number;
    subject_id: number;
};

type PageProps = {
    schedule: ClassScheduleGrid;
    subjects: Subject[];
    days: Enum[];
};

export default function Edit({ schedule, subjects, days }: PageProps) {
    const classroomName = formatClassroomName(schedule.classroom);

    const hasSubjects = subjects.length > 0;
    const hasPeriods = schedule.periods.length > 0;
    const canEditSchedule = hasSubjects && hasPeriods;

    const [gridState, setGridState] = React.useState<Record<string, ScheduleCell>>(() =>
        initializeGridState(schedule, days),
    );

    const items = React.useMemo(
        () => buildScheduleItems(schedule, days, gridState),
        [schedule, days, gridState],
    );

    function handleSubjectChange(periodId: number, dayValue: string, value: string): void {
        const key = gridStateKey(periodId, dayValue);

        setGridState((prev) => ({
            ...prev,
            [key]: {
                subject_id: value ? parseInt(value, 10) : null,
            },
        }));
    }

    return (
        <>
            <Head title="تعديل الجدول الدراسي" />

            <MainContainer showAcademicYearNotice>
                {!hasSubjects && (
                    <section>
                        <Alert variant="warning">
                            <AlertTriangleIcon />
                            <AlertTitle>لا توجد مواد دراسية متاحة</AlertTitle>
                            <AlertDescription>
                                لا توجد مواد دراسية مُعرَّفة لهذا الصف الدراسي. يرجى إضافة المواد الدراسية أولاً قبل إعداد الجدول.
                            </AlertDescription>
                        </Alert>
                    </section>
                )}

                <Form
                    {...update.form({ classroom: schedule.classroom })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <input type="hidden" name="items" value={JSON.stringify(items)} />

                            <ValidationErrors errors={errors} />

                            <section>
                                <Card className="gap-0">
                                    <CardHeader className="border-b">
                                        <CardTitle>
                                            <CalendarDaysIcon />
                                            <span>تعديل الجدول الدراسي — {classroomName}</span>
                                        </CardTitle>
                                        <CardDescription>
                                            حدد المادة الدراسية لكل حصة في الجدول
                                        </CardDescription>
                                    </CardHeader>

                                    <CardContent className="p-0">
                                        <ScheduleGridTable
                                            periods={schedule.periods}
                                            days={days}
                                            renderCell={(period, day) => {
                                                const cell = gridState[gridStateKey(period.id, day.id)];

                                                if (period.is_break) {
                                                    return (
                                                        <span className="text-sm text-muted-foreground">استراحة</span>
                                                    );
                                                }

                                                return (
                                                    <Select
                                                        value={cell?.subject_id?.toString() ?? ''}
                                                        disabled={!canEditSchedule}
                                                        onValueChange={(value) =>
                                                            handleSubjectChange(period.id, day.id, value)
                                                        }
                                                    >
                                                        <SelectTrigger className="h-8 text-xs">
                                                            <SelectValue placeholder="المادة الدراسية" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectGroup>
                                                                {subjects.map((subject) => (
                                                                    <SelectItem
                                                                        key={subject.id}
                                                                        value={subject.id.toString()}
                                                                    >
                                                                        {subject.name}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectGroup>
                                                        </SelectContent>
                                                    </Select>
                                                );
                                            }}
                                        />
                                    </CardContent>

                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                            <Link href={show.url({ classroom: schedule.classroom })}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <UpdateButton processing={processing} disabled={!canEditSchedule || processing} />
                                    </CardFooter>
                                </Card>
                            </section>
                        </FormLayout>
                    )}
                </Form>
            </MainContainer>
        </>
    );
}

Edit.layout = (props: PageProps) => ({
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
        {
            title: 'تعديل الجدول الدراسي',
            href: edit.url({ classroom: props.schedule.classroom }),
        },
    ],
});

function gridStateKey(periodId: number, dayValue: string): string {
    return `${periodId}-${dayValue}`;
}

function initializeGridState(schedule: ClassScheduleGrid, days: Enum[]): Record<string, ScheduleCell> {
    const state: Record<string, ScheduleCell> = {};

    for (const period of schedule.periods) {
        for (const day of days) {
            const item = schedule.grid[period.id]?.[parseInt(day.id, 10)];
            const key = gridStateKey(period.id, day.id);

            state[key] = {
                subject_id: item && item !== 'break' ? (item.subject_id ?? null) : null,
            };
        }
    }

    return state;
}

function buildScheduleItems(
    schedule: ClassScheduleGrid,
    days: Enum[],
    gridState: Record<string, ScheduleCell>,
): ScheduleItem[] {
    const items: ScheduleItem[] = [];

    for (const period of schedule.periods) {
        if (period.is_break) {
            continue;
        }

        for (const day of days) {
            const cell = gridState[gridStateKey(period.id, day.id)];

            if (cell?.subject_id) {
                items.push({
                    class_period_id: period.id,
                    day_of_week: parseInt(day.id, 10),
                    subject_id: cell.subject_id,
                });
            }
        }
    }

    return items;
}
