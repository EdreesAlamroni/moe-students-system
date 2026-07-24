import React from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';
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

import { AlertTriangleIcon } from 'lucide-react';

type BaseConfirmBookDistributionDialogProps = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    processing: boolean;
    selectedCount: number;
    onConfirm: () => void;
};

type SchoolConfirmBookDistributionDialogProps = BaseConfirmBookDistributionDialogProps & {
    context: 'school';
    gradeLevelName: string;
    classroomName?: string | null;
};

type WarehouseConfirmBookDistributionDialogProps = BaseConfirmBookDistributionDialogProps & {
    context: 'warehouse';
    schoolName: string;
};

export type ConfirmBookDistributionDialogProps = | SchoolConfirmBookDistributionDialogProps | WarehouseConfirmBookDistributionDialogProps;

const CONTEXT_CONFIG = {
    school: {
        title: 'تأكيد تسليم الكُتب',
        intro: 'أنت على وشك تسجيل استلام الكُتب للطلاب المحددين. يُسمح بتسليم الكُتب لكل طالب مرة واحدة فقط خلال السنة الدراسية الحالية.',
        countLabel: 'عدد الطلاب المحددين',
        countUnit: 'طالب/طالبة',
        warningDescription: 'سيتم تسجيل استلام الكُتب مباشرة، ولا يمكن التراجع عن هذا الإجراء أو تعديله أو حذفه بعد التأكيد.',

    },
    warehouse: {
        title: 'تأكيد استلام الكُتب',
        intro: 'أنت على وشك تأكيد استلام الكُتب من المخزن للصفوف الدراسية المحددة. يُسمح بتأكيد الاستلام لكل صف دراسي مرة واحدة فقط خلال السنة الدراسية الحالية.',
        countLabel: 'عدد الصفوف المحددة',
        countUnit: 'صف/صفوف دراسية',
        warningDescription: 'سيتم تأكيد الاستلام مباشرة، ولا يمكن التراجع عن هذا الإجراء أو تعديله أو حذفه بعد التأكيد.',
    },
} as const;

export function ConfirmBookDistributionDialog(props: ConfirmBookDistributionDialogProps) {
    const { open, onOpenChange, processing, selectedCount, onConfirm, context } = props;
    const config = CONTEXT_CONFIG[context];

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent className="sm:max-w-lg">
                <AlertDialogHeader>
                    <AlertDialogTitle>{config.title}</AlertDialogTitle>
                    <AlertDialogDescription asChild>
                        <div className="space-y-4 text-right text-sm text-foreground">
                            <p className="leading-relaxed text-muted-foreground">{config.intro}</p>

                            <div className="rounded-lg border border-border bg-muted/50 p-4 space-y-2">
                                <p className="text-sm font-medium text-foreground">مُلخص</p>
                                <ul className="space-y-2 text-xs text-muted-foreground">
                                    {context === 'school' ? (
                                        <>
                                            <li className="flex flex-wrap justify-between gap-2">
                                                <span>الصف الدراسي</span>
                                                <span className="font-medium text-foreground">{props.gradeLevelName}</span>
                                            </li>
                                            <li className="flex flex-wrap justify-between gap-2">
                                                <span>الفصل الدراسي</span>
                                                <span className="font-medium text-foreground">
                                                    {props.classroomName ?? 'جميع الفصول'}
                                                </span>
                                            </li>
                                        </>
                                    ) : (
                                        <li className="flex flex-wrap justify-between gap-2">
                                            <span>المدرسة</span>
                                            <span className="font-medium text-foreground">{props.schoolName}</span>
                                        </li>
                                    )}
                                    <li className="flex flex-wrap justify-between gap-2">
                                        <span>{config.countLabel}</span>
                                        <span className="font-medium text-foreground">
                                            <span className="font-mono tabular-nums">{selectedCount}</span>
                                            {" "}
                                            <span>{config.countUnit}</span>
                                        </span>
                                    </li>
                                </ul>
                            </div>

                            <Alert variant="warning">
                                <AlertTriangleIcon />
                                <AlertTitle>تنبيه</AlertTitle>
                                <AlertDescription>
                                    {config.warningDescription}
                                </AlertDescription>
                            </Alert>
                        </div>
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel type="button" disabled={processing}>
                        إلغاء الأمر
                    </AlertDialogCancel>
                    <AlertDialogAction asChild>
                        <ConfirmButton
                            type="button"
                            variant="destructive"
                            processing={processing}
                            disabled={processing}
                            onClick={onConfirm}
                        />
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
