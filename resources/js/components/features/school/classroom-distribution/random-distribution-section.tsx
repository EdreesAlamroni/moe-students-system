import React from 'react';

import { router, usePage } from '@inertiajs/react';

import type { GradeLevel } from '@/types';
import type {
    ClassroomDistributionMethod,
    ClassroomRow,
} from '@/types/classroom-distribution';

import {
    Card,
    CardContent,
    CardDescription,
    CardFormFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/structure/card';

import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alerts/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alerts/alert-dialog';
import ValidationErrors from '@/components/ui/alerts/validation-errors';

import EmptyState from '@/components/ui/display/empty-state';
import { Badge } from '@/components/ui/display/badge';

import { Checkbox } from '@/components/ui/controls/checkbox';
import { Label } from '@/components/ui/controls/label';

import { Button } from '@/components/ui/actions/button';
import { ConfirmButton } from '@/components/ui/actions/submit-button';

import ClassroomCard from '@/components/features/school/classroom-distribution/classroom-card';

import { AlertTriangleIcon, CheckCircle2Icon, ShuffleIcon } from 'lucide-react';

interface RandomDistributionSectionProps {
    method: ClassroomDistributionMethod;
    classrooms: ClassroomRow[];
    gradeLevel: GradeLevel;
    selectedGradeLevelId: number;
    pendingInGradeCount: number;
    formsDisabled: boolean;
}

export default function RandomDistributionSection({
    method,
    classrooms,
    gradeLevel,
    selectedGradeLevelId,
    pendingInGradeCount,
    formsDisabled,
}: RandomDistributionSectionProps) {
    const { errors: inertiaErrors } = usePage().props;

    const [randomSelection, setRandomSelection] = React.useState<
        Record<number, boolean>
    >({});
    const [dialogOpen, setDialogOpen] = React.useState<boolean>(false);

    const noPendingStudents: boolean = pendingInGradeCount === 0;
    const isSelectionDisabled: boolean = formsDisabled || noPendingStudents;

    const selectableClassroomIds: number[] = React.useMemo(() => {
        return classrooms
            .filter((classroom) => classroom.remaining_capacity > 0)
            .map((classroom) => classroom.id);
    }, [classrooms]);

    const toggleClassroom = (id: number, checked: boolean): void => {
        if (isSelectionDisabled) {
            return;
        }

        setRandomSelection((previous) => ({ ...previous, [id]: checked }));
    };

    const selectedClassroomIds = selectableClassroomIds.filter(
        (id) => randomSelection[id],
    );

    const totalRemainingCapacity: number = React.useMemo(() => {
        return classrooms
            .filter((classroom) => randomSelection[classroom.id])
            .reduce((sum, classroom) => sum + classroom.remaining_capacity, 0);
    }, [classrooms, randomSelection]);

    const overflowCount: number = Math.max(
        0,
        pendingInGradeCount - totalRemainingCapacity,
    );
    const hasCapacityShortage: boolean =
        selectedClassroomIds.length > 0 &&
        pendingInGradeCount > totalRemainingCapacity;

    const canOpenDialog: boolean =
        !isSelectionDisabled &&
        classrooms.length > 0 &&
        selectedClassroomIds.length > 0;

    const allSelected: boolean =
        selectableClassroomIds.length > 0 &&
        selectableClassroomIds.every((id: number) => randomSelection[id]);
    const someSelected: boolean =
        selectableClassroomIds.some((id: number) => randomSelection[id]) &&
        !allSelected;

    const toggleAll = (checked: boolean | 'indeterminate'): void => {
        if (isSelectionDisabled) {
            return;
        }

        const next: Record<number, boolean> = {};

        if (checked === true) {
            selectableClassroomIds.forEach((classroomId: number) => {
                next[classroomId] = true;
            });
        }

        setRandomSelection(next);
    };

    const handleSubmit = (): void => {
        router.post(
            method.route,
            {
                grade_level_id: selectedGradeLevelId,
                classroom_ids: selectedClassroomIds,
            },
            {
                preserveScroll: true,
                onFinish: () => setDialogOpen(false),
            },
        );
    };

    return (
        <section>
            <Card>
                <CardHeader className="border-b">
                    <CardTitle>
                        <ShuffleIcon />
                        <span>توزيع عشوائي حسب السعة</span>
                    </CardTitle>
                    <CardDescription>
                        يتم اختيار الطلاب غير المعيَّنين في فصل دراسي لهذا الصف
                        عشوائياً، مع ملء المقاعد المتاحة أولاً، ثم توزيع أي فائض
                        عشوائياً على الفصول الدراسية المحددة إذا تجاوز العدد
                        السعة الإجمالية.
                    </CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-6">
                    <ValidationErrors errors={inertiaErrors ?? {}} />

                    {hasCapacityShortage && (
                        <Alert variant="warning">
                            <AlertTriangleIcon />
                            <AlertTitle>
                                تجاوز السعة الإجمالية للفصول الدراسية المحددة
                            </AlertTitle>
                            <AlertDescription className="mt-1 [&_p:not(:last-child)]:mb-1.5">
                                <p>
                                    عدد الطلاب في هذا الصف الدراسي غير
                                    المعيَّنين في فصل دراسي هو{' '}
                                    <strong className="font-mono">
                                        {pendingInGradeCount}
                                    </strong>
                                    ، بينما إجمالي المقاعد المتاحة في الفصول
                                    الدراسية المحددة هو{' '}
                                    <strong className="font-mono">
                                        {totalRemainingCapacity}
                                    </strong>
                                    .
                                </p>
                                <p>
                                    سيتم استيعاب الطلاب ضمن السعة قدر الإمكان،
                                    ثم يُوزَّع الباقي (
                                    <strong className="font-mono">
                                        {overflowCount}
                                    </strong>{' '}
                                    طالب/طالبة) عشوائياً على الفصول الدراسية
                                    المحددة، ما قد يعني تجاوز سعة بعض الفصول
                                    الدراسية.
                                </p>
                                <p className="text-xs">
                                    راجع بطاقات الفصول الدراسية أدناه، ثم أكّد
                                    العملية من نافذة التأكيد لعرض ملخص كامل قبل
                                    التنفيذ.
                                </p>
                            </AlertDescription>
                        </Alert>
                    )}

                    {classrooms.length === 0 ? (
                        <EmptyState text="لا توجد فصول دراسية لهذا الصف في السنة الحالية. أضف الفصول من قائمة الفصول الدراسية." />
                    ) : (
                        <div className="flex flex-col gap-4">
                            <div className="flex flex-wrap items-center justify-between gap-3">
                                <h3 className="flex items-center gap-x-2 text-sm font-medium text-foreground">
                                    <span>الفصول الدراسية المشاركة في التوزيع</span>
                                    <Badge
                                        variant="secondary"
                                        className="font-mono tabular-nums"
                                    >
                                        {classrooms.length}
                                    </Badge>
                                </h3>
                                {pendingInGradeCount > 0 && (
                                    <span className="text-xs text-muted-foreground">
                                        طلاب بلا فصل دراسي في هذا الصف الدراسي:{' '}
                                        <span className="font-mono font-medium text-foreground tabular-nums">
                                            {pendingInGradeCount}
                                        </span>
                                    </span>
                                )}
                                {noPendingStudents && (
                                    <span className="text-xs text-muted-foreground">
                                        لا يوجد طلاب مسجّلون في هذا الصف الدراسي
                                        بانتظار تعيين فصل دراسي حالياً؛ تم تعطيل
                                        الاختيار.
                                    </span>
                                )}
                            </div>

                            <div className="flex items-center gap-x-2">
                                <Checkbox
                                    id="select-all-classrooms"
                                    checked={
                                        allSelected
                                            ? true
                                            : someSelected
                                                ? 'indeterminate'
                                                : false
                                    }
                                    onCheckedChange={toggleAll}
                                    disabled={
                                        isSelectionDisabled ||
                                        selectableClassroomIds.length === 0
                                    }
                                />
                                <Label
                                    htmlFor="select-all-classrooms"
                                    className="font-normal"
                                >
                                    تحديد جميع الفصول الدراسية
                                </Label>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                {classrooms.map((classroom) => (
                                    <ClassroomCard
                                        key={classroom.id}
                                        classroom={classroom}
                                        selected={!!randomSelection[classroom.id]}
                                        disabled={isSelectionDisabled}
                                        showShortageHint={
                                            !!randomSelection[classroom.id] &&
                                            hasCapacityShortage &&
                                            selectedClassroomIds.length > 0
                                        }
                                        onToggle={toggleClassroom}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </CardContent>
                <CardFormFooter>
                    <Button
                        type="button"
                        variant="default"
                        disabled={!canOpenDialog}
                        onClick={() => setDialogOpen(true)}
                    >
                        <CheckCircle2Icon />
                        <span>مراجعة وتأكيد التوزيع</span>
                    </Button>

                    <AlertDialog open={dialogOpen} onOpenChange={setDialogOpen}>
                        <AlertDialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    تأكيد التوزيع العشوائي
                                </AlertDialogTitle>
                                <AlertDialogDescription asChild>
                                    <div className="space-y-4 text-right text-sm text-foreground">
                                        <p>
                                            الصف الدراسي:{' '}
                                            <span className="font-medium">
                                                {gradeLevel.name}
                                            </span>
                                        </p>
                                        <div className="space-y-2 rounded-none border border-border bg-muted/50 p-4">
                                            <p className="text-sm font-medium text-foreground">
                                                مُلخص
                                            </p>
                                            <ul className="space-y-2 text-xs text-muted-foreground">
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        طلاب بلا فصل دراسي (هذا
                                                        الصف الدراسي)
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {pendingInGradeCount}
                                                    </span>
                                                </li>
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        عدد الفصول الدراسية
                                                        المشاركة
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {
                                                            selectedClassroomIds.length
                                                        }
                                                    </span>
                                                </li>
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        إجمالي المقاعد المتاحة
                                                        في الفصول الدراسية
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {totalRemainingCapacity}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>

                                        {hasCapacityShortage ? (
                                            <Alert
                                                variant="warning"
                                                className="text-start"
                                            >
                                                <AlertTriangleIcon />
                                                <AlertTitle>
                                                    تنبيه السعة الإجمالية
                                                </AlertTitle>
                                                <AlertDescription>
                                                    <div>
                                                        عدد الطلاب يتجاوز
                                                        المقاعد المتاحة بمقدار{' '}
                                                        <strong className="font-mono tabular-nums">
                                                            {overflowCount}
                                                        </strong>
                                                        . سيتم تعبئة المقاعد
                                                        المتاحة أولاً، ثم توزيع
                                                        الباقي عشوائياً على
                                                        الفصول الدراسية المحددة
                                                        (قد يتجاوز ذلك سعة بعض
                                                        الفصول الدراسية).
                                                    </div>
                                                </AlertDescription>
                                            </Alert>
                                        ) : (
                                            <p className="text-sm leading-relaxed text-muted-foreground">
                                                ضمن السعة الإجمالية للفصول
                                                الدراسية المحددة؛ لا يُتوقع فائض
                                                يتطلب تجاوز المقاعد المتاحة قبل
                                                التوزيع العشوائي للفائض.
                                            </p>
                                        )}

                                        <p className="text-sm leading-relaxed text-muted-foreground">
                                            بالتأكيد، سيتم تنفيذ التوزيع
                                            العشوائي فوراً ولا يمكن التراجع عنه
                                            من هذه الخطوة.
                                        </p>
                                    </div>
                                </AlertDialogDescription>
                            </AlertDialogHeader>

                            <AlertDialogFooter>
                                <AlertDialogCancel>
                                    إلغاء الأمر
                                </AlertDialogCancel>
                                <AlertDialogAction asChild>
                                    <ConfirmButton
                                        type="button"
                                        onClick={handleSubmit}
                                    />
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardFormFooter>
            </Card>
        </section>
    );
}
