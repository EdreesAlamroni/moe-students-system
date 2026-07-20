import React, { useState } from 'react';

import { Head, router, useForm } from '@inertiajs/react';

import type { EducationMonitor, GradeLevel, School } from '@/types';

import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/structure/card';
import MainContainer from '@/components/ui/structure/main-container';

import { Badge } from '@/components/ui/display/badge';
import EmptyState from '@/components/ui/display/empty-state';
import { LoadingData } from '@/components/ui/display/loading-data';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, TableCellNullableValue, } from '@/components/ui/display/table';

import { Checkbox } from '@/components/ui/controls/checkbox';
import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

import { Button } from '@/components/ui/actions/button';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';
import ValidationErrors from '@/components/ui/alerts/validation-errors';

import { BookTextIcon, Building2Icon, CheckCircle2Icon, CheckCircleIcon, ListIcon, LoaderIcon } from 'lucide-react';

import { ConfirmBookDistributionDialog } from '@/components/shared/book-distributions/confirm-distribution-dialog';

import { index, store } from '@/routes/warehouse/book-distributions';

type OrganizationOption = Pick<EducationMonitor | School, 'id' | 'name'>;

type IndexPageProps = {
    monitors: OrganizationOption[];
    schools: OrganizationOption[];
    gradeLevels: GradeLevel[];
    selected: {
        education_monitor_id: number | null;
        school_id: number | null;
    };
    can: {
        distribute: boolean;
    };
};

type LoadingTarget = 'schools' | 'grades';

const visitOptions = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
} as const;

export default function Index({ monitors, schools, gradeLevels, selected, can }: IndexPageProps) {
    const [loading, setLoading] = useState<LoadingTarget | null>(null);
    const [pendingMonitorId, setPendingMonitorId] = useState<string>();
    const [pendingSchoolId, setPendingSchoolId] = useState<string>();
    const [confirmOpen, setConfirmOpen] = useState(false);

    const form = useForm<{ grade_level_ids: number[] }>({ grade_level_ids: [] });

    const monitorId = pendingMonitorId ?? selected.education_monitor_id?.toString() ?? '';
    const schoolId = pendingSchoolId ?? selected.school_id?.toString() ?? '';
    const isLoadingSchools = loading === 'schools';
    const isLoadingGrades = loading === 'grades';
    const showGradeLevels = Boolean(schoolId);

    const eligibleIds = gradeLevels.filter((gradeLevel) => !gradeLevel.already_distributed).map((gradeLevel) => gradeLevel.id);
    const selectedIds = form.data.grade_level_ids;
    const selectedCount = selectedIds.length;
    const allEligibleSelected = eligibleIds.length > 0 && selectedCount === eligibleIds.length;
    const someEligibleSelected = selectedCount > 0 && !allEligibleSelected;
    const schoolName = schools.find((school) => String(school.id) === schoolId)?.name ?? '';

    function clearSelection(): void {
        form.setData('grade_level_ids', []);
    }

    function selectMonitor(value: string): void {
        setPendingMonitorId(value);
        setPendingSchoolId(undefined);
        setLoading('schools');
        clearSelection();

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
        setLoading('grades');
        clearSelection();

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

    function toggleAll(checked: boolean | 'indeterminate'): void {
        form.setData('grade_level_ids', checked === true ? eligibleIds : []);
    }

    function toggleGradeLevel(gradeLevelId: number, checked: boolean): void {
        form.setData(
            'grade_level_ids',
            checked ? [...selectedIds, gradeLevelId] : selectedIds.filter((id) => id !== gradeLevelId),
        );
    }

    function confirmDistribution(): void {
        form.transform((data) => ({
            ...data,
            education_monitor_id: selected.education_monitor_id,
            school_id: selected.school_id,
        }));

        form.post(store.url(), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => clearSelection(),
            onFinish: () => setConfirmOpen(false),
        });
    }

    return (
        <>
            <Head title="توزيع الكُتب المدرسية" />

            <MainContainer changeAcademicYearNotice>
                <Alert>
                    <BookTextIcon />
                    <AlertTitle>توزيع الكُتب المدرسية</AlertTitle>
                    <AlertDescription className="flex flex-col gap-1">
                        <span>اختر المُراقبة ثم المدرسة لعرض الصفوف الدراسية، ثم حدد الصفوف التي استلمت الكُتب من المخزن.</span>
                        <span>يُسمح بتأكيد استلام الكُتب لكل صف دراسي مرة واحدة فقط خلال السنة الدراسية الحالية.</span>
                    </AlertDescription>
                </Alert>

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

                {!showGradeLevels ? (
                    <section>
                        <Card>
                            <CardContent>
                                <EmptyState
                                    icon={Building2Icon}
                                    text={monitorId ? 'اختر المدرسة للمتابعة' : 'ابدأ باختيار الجهة التعليمية'}
                                    description={
                                        monitorId
                                            ? 'بعد اختيار المدرسة، ستظهر الصفوف الدراسية المتاحة لتأكيد استلام الكُتب.'
                                            : 'اختر المُراقبة ثم المدرسة لعرض الصفوف الدراسية وتطبيق التوزيع.'
                                    }
                                />
                            </CardContent>
                        </Card>
                    </section>
                ) : (
                    <section className="flex flex-col gap-6">
                        <ValidationErrors errors={form.errors} />

                        <Card className="gap-0">
                            <CardHeader className="border-b gap-0">
                                <CardTitle className="min-w-0 flex-1">
                                    <ListIcon />
                                    <div className="flex min-w-0 flex-1 items-center justify-between gap-x-4">
                                        <div className="flex items-center gap-x-1.5">
                                            <span>الصفوف الدراسية</span>
                                            {!isLoadingGrades && gradeLevels.length > 0 && (
                                                <span className="font-mono">({gradeLevels.length})</span>
                                            )}
                                        </div>

                                        {selectedCount > 0 && (
                                            <span
                                                className="inline-flex shrink-0 items-center gap-x-2 text-xs font-normal normal-case tracking-normal"
                                                aria-live="polite"
                                            >
                                                <span className="text-muted-foreground">مُحددة للتأكيد</span>
                                                <span className="font-mono tabular-nums text-foreground">
                                                    <span className="font-semibold">{selectedCount}</span>
                                                    <span className="mx-1 text-muted-foreground/50">/</span>
                                                    <span className="text-muted-foreground">{eligibleIds.length}</span>
                                                </span>
                                            </span>
                                        )}
                                    </div>
                                </CardTitle>
                            </CardHeader>

                            {isLoadingGrades ? (
                                <CardContent>
                                    <LoadingData className="pt-6 pb-3" />
                                </CardContent>
                            ) : gradeLevels.length === 0 ? (
                                <CardContent>
                                    <EmptyState
                                        text="لا توجد صفوف دراسية مُضافة لهذه المدرسة في السنة الدراسية الحالية."
                                    />
                                </CardContent>
                            ) : (
                                <CardContent className="p-0">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead scope="col" className="w-24 [&:has([role=checkbox])]:pr-6">
                                                    <Checkbox
                                                        aria-label="تحديد جميع الصفوف المؤهلة"
                                                        disabled={eligibleIds.length === 0 || form.processing}
                                                        checked={
                                                            allEligibleSelected
                                                                ? true
                                                                : someEligibleSelected
                                                                    ? 'indeterminate'
                                                                    : false
                                                        }
                                                        onCheckedChange={toggleAll}
                                                    />
                                                </TableHead>
                                                <TableHead scope="col" className="w-24">
                                                    #
                                                </TableHead>
                                                <TableHead scope="col">الصف الدراسي</TableHead>
                                                <TableHead scope="col">المرحلة التعليمية</TableHead>
                                                <TableHead scope="col" className="text-center">
                                                    عدد الطلاب
                                                </TableHead>
                                                <TableHead scope="col" className="text-center">
                                                    الحالة
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {gradeLevels.map((gradeLevel, rowIndex) => {
                                                const isSelected = selectedIds.includes(gradeLevel.id);

                                                return (
                                                    <TableRow
                                                        key={gradeLevel.id}
                                                        data-state={isSelected ? 'selected' : undefined}
                                                    >
                                                        <TableCell className="[&:has([role=checkbox])]:pr-6 *:[[role=checkbox]]:translate-y-0">
                                                            <Checkbox
                                                                id={`grade-level-${gradeLevel.id}`}
                                                                aria-label={`تحديد الصف ${gradeLevel.name}`}
                                                                disabled={gradeLevel.already_distributed || form.processing}
                                                                checked={isSelected}
                                                                onCheckedChange={(value) =>
                                                                    toggleGradeLevel(gradeLevel.id, value === true)
                                                                }
                                                            />
                                                        </TableCell>
                                                        <TableCell className="font-mono">{rowIndex + 1}</TableCell>
                                                        <TableCell>
                                                            <Label
                                                                htmlFor={`grade-level-${gradeLevel.id}`}
                                                                className={
                                                                    gradeLevel.already_distributed
                                                                        ? 'font-normal text-muted-foreground'
                                                                        : 'cursor-pointer font-normal'
                                                                }
                                                            >
                                                                {gradeLevel.name}
                                                            </Label>
                                                        </TableCell>
                                                        <TableCell>{gradeLevel.educational_stage.name}</TableCell>
                                                        <TableCell className="text-center">
                                                            <TableCellNullableValue
                                                                className="font-mono"
                                                                value={gradeLevel.students_count}
                                                                fallback={0}
                                                            />
                                                        </TableCell>
                                                        <TableCell>
                                                            <div className="flex justify-center">
                                                                {gradeLevel.already_distributed ? (
                                                                    <Badge variant="success">
                                                                        <CheckCircle2Icon data-icon="inline-start" />
                                                                        تم الاستلام
                                                                    </Badge>
                                                                ) : (
                                                                    <Badge variant="outline">لم يستلم</Badge>
                                                                )}
                                                            </div>
                                                        </TableCell>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            )}

                            {can.distribute && !isLoadingGrades && gradeLevels.length > 0 && (
                                <>
                                    <CardFooter className="justify-end border-t">
                                        <Button
                                            type="button"
                                            disabled={selectedCount === 0 || form.processing}
                                            onClick={() => setConfirmOpen(true)}
                                        >
                                            <CheckCircleIcon />
                                            <span>توزيع الكُتب للصفوف المحددة</span>
                                        </Button>
                                    </CardFooter>

                                    <ConfirmBookDistributionDialog
                                        open={confirmOpen}
                                        onOpenChange={setConfirmOpen}
                                        context="warehouse"
                                        schoolName={schoolName}
                                        selectedCount={selectedCount}
                                        processing={form.processing}
                                        onConfirm={confirmDistribution}
                                    />
                                </>
                            )}
                        </Card>
                    </section>
                )}
            </MainContainer>
        </>
    );
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'توزيع الكُتب المدرسية',
            href: index.url(),
        },
    ],
});
