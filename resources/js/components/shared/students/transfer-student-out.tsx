import React from 'react';

import { Link } from '@inertiajs/react';

import type { Student } from '@/types';

import { Icon } from '@/components/ui/display/icon';

import { Button } from '@/components/ui/actions/button';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alerts/alert-dialog';

import { CheckCircleIcon, LogOutIcon } from 'lucide-react';

import { destroy as destroyEducationMonitorTransfer } from '@/routes/education-monitor/students/transfers';

type TransferStudentOutContext = 'school' | 'education-monitor';

const config: Record<TransferStudentOutContext, {
    buttonLabel: string;
    descriptionContext: string;
}> = {
    school: {
        buttonLabel: 'نقل الطالب من المدرسة',
        descriptionContext: 'من المدرسة',
    },
    'education-monitor': {
        buttonLabel: 'نقل الطالب من المُراقبة',
        descriptionContext: 'من المُراقبة',
    },
};

function resolveDestroyUrl(context: TransferStudentOutContext, student: Student): string {
    switch (context) {
        case 'education-monitor':
            return destroyEducationMonitorTransfer.url({ student });
        case 'school':
            throw new Error('School student transfer out route is not yet implemented.');
    }
}

type TransferStudentOutProps = {
    student: Student;
    context: TransferStudentOutContext;
};

export default function TransferStudentOut({ student, context }: TransferStudentOutProps) {
    const { buttonLabel, descriptionContext } = config[context];

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <Button type="button" variant="destructive">
                    <Icon iconNode={LogOutIcon} />
                    <span>{buttonLabel}</span>
                </Button>
            </AlertDialogTrigger>

            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        هل أنت متأكد من الإجراء ؟
                    </AlertDialogTitle>

                    <AlertDialogDescription >
                        <span>سيتم نقل الطالب </span>
                        <strong className="mx-1 inline-block font-medium text-foreground">{student.full_name}</strong>
                        <span>{descriptionContext}، هل أنت متأكد من هذا الإجراء ؟</span>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>إلغاء الأمر</AlertDialogCancel>
                    <AlertDialogAction asChild>
                        <Link
                            href={resolveDestroyUrl(context, student)}
                            method="delete"
                            as="button"
                        >
                            <CheckCircleIcon />
                            تـأكـيـد
                        </Link>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}
