import React from 'react';

import { router } from '@inertiajs/react';

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

import { ConfirmButton } from '@/components/ui/actions/submit-button';

import { AlertTriangleIcon, CheckCircle2Icon } from 'lucide-react';

import { toast } from 'sonner';

import { finalize } from '@/routes/school/classroom-distribution';

type FinalizeSectionProps = {
    schoolWideUnassignedCount: number;
    enrollmentsWithoutGradeLevelCount: number;
    canFinalize: boolean;
    isDistributionCompleted: boolean;
    academicYearName: string | undefined;
    isAcademicYearActive: boolean;
};

export default function FinalizeSection({
    schoolWideUnassignedCount,
    enrollmentsWithoutGradeLevelCount,
    canFinalize,
    isDistributionCompleted,
    academicYearName,
    isAcademicYearActive,
}: FinalizeSectionProps) {
    const [dialogOpen, setDialogOpen] = React.useState(false);

    const hasMissingGradeLevel = enrollmentsWithoutGradeLevelCount > 0;
    const finalizeReady =
        !hasMissingGradeLevel &&
        schoolWideUnassignedCount === 0 &&
        canFinalize &&
        !isDistributionCompleted;

    const handleSubmit = (): void => {
        if (!canFinalize) {
            toast.error('لا تملك صلاحية الإتمام');

            return;
        }

        router.post(
            finalize.url(),
            {},
            {
                preserveScroll: true,
                onFinish: () => setDialogOpen(false),
            },
        );
    };

    return (
        <section>
            <Card className="border-dashed">
                <CardHeader className="border-b">
                    <CardTitle>
                        <CheckCircle2Icon />
                        <span>إتمام التوزيع للسنة الدراسية الحالية</span>
                    </CardTitle>
                    <CardDescription>
                        يُسمح بإتمام العملية فقط بعد تسجيل جميع الطلاب في صف
                        دراسي وتعيينهم في فصول لهذه السنة الدراسية. بعد الإتمام
                        لن يمكن تعديل التوزيع من هذه الشاشة.
                    </CardDescription>
                </CardHeader>

                <CardContent className="space-y-4">
                    {hasMissingGradeLevel && (
                        <Alert variant="warning">
                            <AlertTriangleIcon />
                            <AlertTitle>
                                بعض الطلاب غير مسجّلين في صف دراسي
                            </AlertTitle>
                            <AlertDescription>
                                <div>
                                    يتبقى{' '}
                                    <strong className="font-mono tabular-nums">
                                        {enrollmentsWithoutGradeLevelCount}
                                    </strong>{' '}
                                    طالب/طالبة غير مسجّلين في أي صف دراسي للسنة
                                    الدراسية الحالية. يُرجى تسجيلهم في صف دراسي
                                    من صفحة الطالب قبل إتمام التوزيع.
                                </div>
                            </AlertDescription>
                        </Alert>
                    )}

                    {!finalizeReady &&
                        !hasMissingGradeLevel &&
                        schoolWideUnassignedCount > 0 && (
                            <Alert variant="warning">
                                <AlertTriangleIcon />
                                <AlertTitle>
                                    لا يزال هناك طلاب بلا فصل دراسي
                                </AlertTitle>
                                <AlertDescription>
                                    <div>
                                        يتبقى{' '}
                                        <strong className="font-mono tabular-nums">
                                            {schoolWideUnassignedCount}
                                        </strong>{' '}
                                        طالب/طالبة مسجّلين في صف دراسي لكن بلا
                                        فصل دراسي للسنة الدراسية الحالية. عيّنهم
                                        عبر التوزيع العشوائي أو اليدوي لكل صف
                                        دراسي قبل الإتمام.
                                    </div>
                                </AlertDescription>
                            </Alert>
                        )}

                    {finalizeReady && (
                        <Alert>
                            <CheckCircle2Icon />
                            <AlertTitle>جاهز للإتمام</AlertTitle>
                            <AlertDescription>
                                جميع الطلاب المسجّلين في صف دراسي لديهم فصل
                                دراسي للسنة الدراسية الحالية. يمكنك إتمام
                                التوزيع رسمياً لإغلاق العملية.
                            </AlertDescription>
                        </Alert>
                    )}
                </CardContent>

                <CardFormFooter>
                    <ConfirmButton
                        type="button"
                        disabled={!finalizeReady || !isAcademicYearActive}
                        onClick={() => setDialogOpen(true)}
                        title="إتمام التوزيع وإغلاق العملية"
                    />

                    <AlertDialog open={dialogOpen} onOpenChange={setDialogOpen}>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    تأكيد إتمام التوزيع
                                </AlertDialogTitle>
                                <AlertDialogDescription asChild>
                                    <div className="space-y-4 text-right text-sm text-foreground">
                                        <p>
                                            أنت على وشك إتمام توزيع الطلاب على
                                            الفصول للسنة الدراسية{' '}
                                            <strong className="font-mono">
                                                {academicYearName}
                                            </strong>
                                            .
                                        </p>
                                        <div className="rounded-none border border-border bg-muted/50 p-4">
                                            <p className="mb-3 text-sm font-medium">
                                                ملخص
                                            </p>
                                            <ul className="space-y-2 text-xs leading-relaxed text-muted-foreground">
                                                <li>
                                                    جميع الطلاب المسجّلين لديهم
                                                    فصل دراسي للسنة الدراسية
                                                    الحالية.
                                                </li>
                                                <li>
                                                    لن يمكن تنفيذ توزيع جديد أو
                                                    تعديل التوزيع من هذه الصفحة
                                                    بعد الإتمام.
                                                </li>
                                                <li>
                                                    يُنصح بالتحقق من كل الصفوف
                                                    قبل المتابعة.
                                                </li>
                                            </ul>
                                        </div>

                                        <Alert
                                            variant="destructive"
                                            className="text-start"
                                        >
                                            <AlertTriangleIcon />
                                            <AlertTitle>تنبيه</AlertTitle>
                                            <AlertDescription>
                                                هذا الإجراء يُغلق عملية التوزيع
                                                لهذه السنة في لوحة تحكم المدرسة.
                                                تأكد أن التوزيع النهائي مطابقاً
                                                لسياسة المدرسة.
                                            </AlertDescription>
                                        </Alert>
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
