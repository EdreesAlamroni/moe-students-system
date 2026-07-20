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

import {
    formatClassroomName,
    isScheduleGridItem,
    ScheduleGridTable,
} from '@/components/features/school/class-schedules/schedule-grid-table';

import { AlertTriangleIcon, CalendarDaysIcon, LoaderIcon, ReplyIcon } from 'lucide-react';

import { index as classroomIndex, show as classroomShow } from '@/routes/school/classrooms';
import { show, edit, update } from '@/routes/school/classrooms/class-schedules';

type PageProps = {
    schedule: ClassScheduleGrid;
    subjects?: Subject[];
    days: Enum[];
};

export default function Edit({ schedule, subjects, days }: PageProps) {
    const classroomName = formatClassroomName(schedule.classroom);
    const subjectsLoading = subjects === undefined;
    const canEdit = schedule.periods.length > 0 && !subjectsLoading && (subjects?.length ?? 0) > 0;

    const [grid, setGrid] = React.useState(() => initializeGrid(schedule, days));

    const items = React.useMemo(() => buildScheduleItems(schedule, days, grid), [schedule, days, grid]);

    return (
        <>
            <Head title="تعديل الجدول الدراسي" />

            <MainContainer showAcademicYearNotice>
                {!subjectsLoading && subjects.length === 0 && (
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
                                                if (period.is_break) {
                                                    return (
                                                        <span className="text-sm text-muted-foreground">استراحة</span>
                                                    );
                                                }

                                                const key = `${period.id}-${day.id}`;
                                                const gridItem = schedule.grid[period.id]?.[parseInt(day.id, 10)];
                                                const subjectId = grid[key] ?? null;
                                                const label = subjectId !== null
                                                    ? subjects?.find((subject) => subject.id === subjectId)?.name ?? (isScheduleGridItem(gridItem) ? gridItem.subject?.name : undefined)
                                                    : undefined;

                                                return (
                                                    <SubjectScheduleSelect
                                                        value={subjectId}
                                                        label={label}
                                                        subjects={subjects}
                                                        loading={subjectsLoading}
                                                        disabled={!canEdit}
                                                        onValueChange={(value) => {
                                                            setGrid((current) => ({
                                                                ...current,
                                                                [key]: value ? parseInt(value, 10) : null,
                                                            }));
                                                        }}
                                                    />
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

                                        <UpdateButton processing={processing} disabled={!canEdit || processing} />
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

function SubjectScheduleSelect({
    value,
    label,
    subjects,
    loading,
    disabled,
    onValueChange,
}: {
    value: number | null;
    label?: string;
    subjects?: Subject[];
    loading: boolean;
    disabled: boolean;
    onValueChange: (value: string) => void;
}) {
    if (loading && value === null) {
        return (
            <Select disabled open={false}>
                <SelectTrigger className="h-8 text-xs" aria-busy="true">
                    <span className="flex items-center gap-2 text-muted-foreground">
                        <LoaderIcon className="size-3.5 shrink-0 animate-spin" />
                        <span>جارٍ تحميل المواد…</span>
                    </span>
                </SelectTrigger>
            </Select>
        );
    }

    return (
        <Select
            value={value?.toString()}
            disabled={disabled}
            onValueChange={onValueChange}
        >
            <SelectTrigger className="h-8 text-xs">
                {label ? (
                    <span className="truncate">{label}</span>
                ) : (
                    <SelectValue placeholder="المادة الدراسية" />
                )}
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    {(subjects ?? []).map((subject) => (
                        <SelectItem key={subject.id} value={subject.id.toString()}>
                            {subject.name}
                        </SelectItem>
                    ))}
                </SelectGroup>
            </SelectContent>
        </Select>
    );
}

function initializeGrid(schedule: ClassScheduleGrid, days: Enum[]) {
    const grid: Record<string, number | null> = {};

    for (const period of schedule.periods) {
        for (const day of days) {
            const item = schedule.grid[period.id]?.[parseInt(day.id, 10)];
            grid[`${period.id}-${day.id}`] = isScheduleGridItem(item) ? (item.subject_id ?? null) : null;
        }
    }

    return grid;
}

function buildScheduleItems(
    schedule: ClassScheduleGrid,
    days: Enum[],
    grid: Record<string, number | null>,
) {
    const items = [];

    for (const period of schedule.periods) {
        if (period.is_break) {
            continue;
        }

        for (const day of days) {
            const subjectId = grid[`${period.id}-${day.id}`];

            if (subjectId) {
                items.push({
                    class_period_id: period.id,
                    day_of_week: parseInt(day.id, 10),
                    subject_id: subjectId,
                });
            }
        }
    }

    return items;
}
