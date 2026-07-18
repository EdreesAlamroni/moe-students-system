import React, { useMemo, useState } from 'react';

import { Head, router } from '@inertiajs/react';

import type { EducationMonitor, GradeLevel, School } from '@/types';

import ActionsSection from '@/components/ui/structure/actions-section';
import { Card, CardContent, CardHeader, CardTableContent, CardTitle } from '@/components/ui/structure/card';
import MainContainer from '@/components/ui/structure/main-container';

import EmptyState from '@/components/ui/display/empty-state';
import { LoadingData } from '@/components/ui/display/loading-data';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow, TableCellNullableValue } from '@/components/ui/display/table';

import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

import { Button } from '@/components/ui/actions/button';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';

import { Building2Icon, ClockIcon, LoaderIcon, PrinterIcon } from 'lucide-react';

import { index, print } from '@/routes/warehouse/reports/book-distributions';

type OrganizationOption = Pick<EducationMonitor | School, 'id' | 'name'>;

type BookDistributionStatistic = GradeLevel;

type ReportPageProps = {
    monitors: OrganizationOption[];
    schools: OrganizationOption[];
    statistics: BookDistributionStatistic[];
    selected: {
        education_monitor_id: number | null;
        school_id: number | null;
    };
    canPrint: boolean;
};

type LoadingTarget = 'schools' | 'statistics';

const visitOptions = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
} as const;

export default function ReportPage({ monitors, schools, statistics: gradeLevelStatistics, selected, canPrint }: ReportPageProps) {
    const [loading, setLoading] = useState<LoadingTarget | null>(null);
    const [pendingMonitorId, setPendingMonitorId] = useState<string>();
    const [pendingSchoolId, setPendingSchoolId] = useState<string>();

    const monitorId = pendingMonitorId ?? selected.education_monitor_id?.toString() ?? '';
    const schoolId = pendingSchoolId ?? selected.school_id?.toString() ?? '';
    const isLoadingSchools = loading === 'schools';
    const isLoadingStatistics = loading === 'statistics';
    const showReport = Boolean(schoolId);

    const totals = useMemo(() => {
        const confirmedStatistics = gradeLevelStatistics.filter((statistic) => statistic.already_distributed);

        return {
            students_count: gradeLevelStatistics.reduce((sum, statistic) => sum + statistic.students_count, 0),
            distributed_count: confirmedStatistics.reduce((sum, statistic) => sum + statistic.distributed_count, 0),
            pending_count: confirmedStatistics.reduce((sum, statistic) => sum + statistic.pending_count, 0),
        };
    }, [gradeLevelStatistics]);

    function selectMonitor(value: string): void {
        setPendingMonitorId(value);
        setPendingSchoolId(undefined);
        setLoading('schools');

        router.get(index.url(), { education_monitor_id: value }, {
            ...visitOptions,
            onFinish: () => {
                setLoading(null);
                setPendingMonitorId(undefined);
            },
        });
    }

    function selectSchool(value: string): void {
        setPendingSchoolId(value);
        setLoading('statistics');

        router.get(index.url(), {
            education_monitor_id: monitorId,
            school_id: value,
        }, {
            ...visitOptions,
            onFinish: () => {
                setLoading(null);
                setPendingSchoolId(undefined);
            },
        });
    }

    return (
        <>
            <Head title="تقرير إحصائيات توزيع الكُتب المدرسية" />

            <MainContainer showAcademicYearNotice>
                <Alert>
                    <PrinterIcon />
                    <AlertTitle>تقرير إحصائيات توزيع الكُتب المدرسية</AlertTitle>
                    <AlertDescription className="flex flex-col gap-1">
                        <span>
                            اختر المُراقبة والمدرسة لإنشاء تقرير إحصائيات توزيع الكُتب المدرسية لكل صف دراسي.
                        </span>
                        <span>
                            تُعرض إحصائيات استلام الطلاب من المدرسة بعد تأكيد استلام الكُتب من المخزن؛ وإلا يظهر
                            تنبيه بذلك في التقرير.
                        </span>
                    </AlertDescription>
                </Alert>

                {canPrint && (
                    <ActionsSection>
                        <Button variant="default" asChild>
                            <a
                                href={print.url({
                                    query: {
                                        education_monitor_id: monitorId,
                                        school_id: schoolId,
                                    },
                                })}
                                target="_blank"
                            >
                                <PrinterIcon />
                                <span>طباعة التقرير</span>
                            </a>
                        </Button>
                    </ActionsSection>
                )}

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <Building2Icon />
                                <span>اختيار الجهة التعليمية</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <Field>
                                    <Label htmlFor="education_monitor_id" required>
                                        المُراقبة
                                    </Label>

                                    {monitors.length > 0 ? (
                                        <Select
                                            value={monitorId || undefined}
                                            disabled={loading !== null}
                                            onValueChange={selectMonitor}
                                        >
                                            <SelectTrigger id="education_monitor_id">
                                                <SelectValue placeholder="اختر المُراقبة" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {monitors.map((monitor) => (
                                                        <SelectItem key={monitor.id} value={String(monitor.id)}>
                                                            {monitor.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="education_monitor_id"
                                            placeholder="لا توجد مُراقبات متاحة للاختيار"
                                        />
                                    )}
                                </Field>

                                <Field>
                                    <Label htmlFor="school_id" required>
                                        المدرسة
                                    </Label>

                                    {!monitorId ? (
                                        <EmptyOptionsInput id="school_id" placeholder="اختر المُراقبة أولاً" />
                                    ) : isLoadingSchools ? (
                                        <Select disabled open={false}>
                                            <SelectTrigger id="school_id" aria-busy="true">
                                                <span className="flex items-center gap-2 text-muted-foreground">
                                                    <LoaderIcon className="size-3.5 shrink-0 animate-spin" />
                                                    <span>جارٍ تحميل المدارس…</span>
                                                </span>
                                            </SelectTrigger>
                                        </Select>
                                    ) : schools.length > 0 ? (
                                        <Select
                                            value={schoolId || undefined}
                                            disabled={loading !== null}
                                            onValueChange={selectSchool}
                                        >
                                            <SelectTrigger id="school_id">
                                                <SelectValue placeholder="اختر المدرسة" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {schools.map((school) => (
                                                        <SelectItem key={school.id} value={String(school.id)}>
                                                            {school.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="school_id"
                                            placeholder="لا توجد مدارس متاحة لهذه المُراقبة"
                                        />
                                    )}
                                </Field>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {!showReport ? (
                    <section>
                        <Card>
                            <CardContent>
                                <EmptyState
                                    icon={Building2Icon}
                                    text={monitorId ? 'اختر المدرسة للمتابعة' : 'ابدأ باختيار الجهة التعليمية'}
                                    description={
                                        monitorId
                                            ? 'بعد اختيار المدرسة، ستظهر إحصائيات توزيع الكُتب لكل صف دراسي.'
                                            : 'اختر المُراقبة ثم المدرسة لإنشاء تقرير إحصائيات توزيع الكُتب المدرسية.'
                                    }
                                />
                            </CardContent>
                        </Card>
                    </section>
                ) : (
                    <section>
                        <Card className="gap-0">
                            <CardHeader className="border-b gap-0">
                                <CardTitle className="min-w-0 flex-1">
                                    <PrinterIcon />
                                    <div className="flex min-w-0 flex-1 items-center gap-x-1.5">
                                        <span>إحصائيات الصفوف الدراسية</span>
                                        {!isLoadingStatistics && gradeLevelStatistics.length > 0 && (
                                            <span className="font-mono">({gradeLevelStatistics.length})</span>
                                        )}
                                    </div>
                                </CardTitle>
                            </CardHeader>

                            {isLoadingStatistics ? (
                                <CardContent>
                                    <LoadingData className="py-6" />
                                </CardContent>
                            ) : gradeLevelStatistics.length === 0 ? (
                                <CardContent>
                                    <EmptyState
                                        text="لا توجد صفوف دراسية مُضافة لهذه المدرسة في السنة الدراسية الحالية."
                                    />
                                </CardContent>
                            ) : (
                                <CardTableContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead scope="col">الصف الدراسي</TableHead>
                                                <TableHead scope="col" className="text-center">
                                                    عدد الطلاب
                                                </TableHead>
                                                <TableHead scope="col" className="text-center">
                                                    المُوزَّع
                                                </TableHead>
                                                <TableHead scope="col" className="text-center">
                                                    المُعلَّق
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {gradeLevelStatistics.map((statistic) => (
                                                <TableRow key={statistic.id}>
                                                    <TableCell>{statistic.name}</TableCell>
                                                    {statistic.already_distributed ? (
                                                        <>
                                                            <TableCell className="text-center">
                                                                <TableCellNullableValue
                                                                    className="font-mono"
                                                                    value={statistic.students_count}
                                                                    fallback={0}
                                                                />
                                                            </TableCell>
                                                            <TableCell className="text-center">
                                                                <TableCellNullableValue
                                                                    className="font-mono"
                                                                    value={statistic.distributed_count}
                                                                    fallback={0}
                                                                />
                                                            </TableCell>
                                                            <TableCell className="text-center">
                                                                <TableCellNullableValue
                                                                    className="font-mono"
                                                                    value={statistic.pending_count}
                                                                    fallback={0}
                                                                />
                                                            </TableCell>
                                                        </>
                                                    ) : (
                                                        <TableCell colSpan={3} className="text-center">
                                                            <span className="inline-flex items-center justify-center gap-x-2 text-sm">
                                                                <ClockIcon className="size-3.5 shrink-0 text-muted-foreground" />
                                                                <span className="font-medium text-muted-foreground">
                                                                    بانتظار تأكيد استلام الكُتب من المخزن
                                                                </span>
                                                            </span>
                                                        </TableCell>
                                                    )}
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                        <TableFooter>
                                            <TableRow>
                                                <TableCell className="font-medium">
                                                    المجموع
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue
                                                        className="font-mono font-medium"
                                                        value={totals.students_count}
                                                        fallback={0}
                                                    />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue
                                                        className="font-mono font-medium"
                                                        value={totals.distributed_count}
                                                        fallback={0}
                                                    />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue
                                                        className="font-mono font-medium"
                                                        value={totals.pending_count}
                                                        fallback={0}
                                                    />
                                                </TableCell>
                                            </TableRow>
                                        </TableFooter>
                                    </Table>
                                </CardTableContent>
                            )}
                        </Card>
                    </section>
                )}
            </MainContainer>
        </>
    );
}

ReportPage.layout = () => ({
    breadcrumbs: [
        {
            title: 'تقرير إحصائيات توزيع الكُتب المدرسية',
            href: index.url(),
        },
    ],
});
