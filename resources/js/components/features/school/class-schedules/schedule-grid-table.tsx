import React from 'react';

import { cn } from '@/lib/utils';

import type { ClassPeriod, ClassScheduleGrid, Enum } from '@/types';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/display/table';

type ScheduleGridTableProps = {
    periods: ClassPeriod[];
    days: Enum[];
    renderCell: (period: ClassPeriod, day: Enum) => React.ReactNode;
};

export function ScheduleGridTable({ periods, days, renderCell }: ScheduleGridTableProps) {
    return (
        <Table>
            <TableHeader>
                <TableRow className="hover:bg-inherit">
                    <TableHead scope="col" className="min-w-[140px] border-b border-l border-border">
                        الحصة
                    </TableHead>
                    {days.map((day, index) => (
                        <TableHead
                            key={day.id}
                            scope="col"
                            className={cn(
                                'min-w-[160px] border-b border-border text-center',
                                index < days.length - 1 && 'border-l',
                            )}
                        >
                            {day.name}
                        </TableHead>
                    ))}
                </TableRow>
            </TableHeader>
            <TableBody>
                {periods.map((period, periodIndex) => {
                    const isLastPeriod = periodIndex === periods.length - 1;

                    return (
                        <TableRow
                            key={period.id}
                            className={period.is_break ? 'bg-green-50 hover:bg-green-50' : 'hover:bg-inherit'}
                        >
                            <TableCell className={cn('border-l border-border', !isLastPeriod && 'border-b')}>
                                <div className="flex flex-col gap-1">
                                    <span className="text-sm font-medium">{period.name}</span>
                                    <span className="font-mono text-xs tabular-nums text-muted-foreground">
                                        {period.start_time} - {period.end_time}
                                    </span>
                                </div>
                            </TableCell>
                            {days.map((day, dayIndex) => (
                                <TableCell
                                    key={day.id}
                                    className={cn(
                                        'border-border text-center',
                                        !isLastPeriod && 'border-b',
                                        dayIndex < days.length - 1 && 'border-l',
                                    )}
                                >
                                    {renderCell(period, day)}
                                </TableCell>
                            ))}
                        </TableRow>
                    );
                })}
            </TableBody>
        </Table>
    );
}

export function formatClassroomName(classroom: ClassScheduleGrid['classroom']): string {
    return classroom.grade_level
        ? `${classroom.grade_level.name} / ${classroom.name}`
        : classroom.name;
}

export function isScheduleGridItem(
    value: ClassScheduleGrid['grid'][number][number],
): value is NonNullable<Exclude<ClassScheduleGrid['grid'][number][number], 'break'>> {
    return value !== null && value !== 'break';
}

export function hasScheduleItems(schedule: ClassScheduleGrid): boolean {
    return Object.values(schedule.grid).some((row) =>
        Object.values(row).some(isScheduleGridItem),
    );
}
