import React from "react";

import { DetailField } from '@/components/ui/display/detail-field';
import { DetailLabel } from '@/components/ui/display/detail-label';
import { DetailValue } from '@/components/ui/display/detail-value';

import { Button } from '@/components/ui/actions/button';
import {
    Dialog,
    DialogBody,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/overlay/dialog';

import type { Student } from '@/types';

import { EyeIcon } from 'lucide-react';

type StudentDetailsDialogContext = 'school' | 'education-monitor';

const config: Record<StudentDetailsDialogContext, {
    description: string;
}> = {
    school: {
        description: 'مراجعة بيانات الطالب قبل إضافته إلى المدرسة.',
    },
    'education-monitor': {
        description: 'مراجعة بيانات الطالب قبل إضافته إلى المُراقبة.',
    },
};

type StudentDetailsDialogProps = {
    student: Student;
    context: StudentDetailsDialogContext;
};

export default function StudentDetailsDialog({ student, context }: StudentDetailsDialogProps) {
    const { description } = config[context];

    return (
        <Dialog>
            <DialogTrigger
                asChild
            >
                <Button
                    type="button"
                    variant="link"
                    className="group h-auto cursor-pointer p-0 text-xs no-underline"
                >
                    <EyeIcon className="h-4 w-4 group-hover:animate-pulse" />
                    <span>عرض التفاصيل</span>
                </Button>
            </DialogTrigger>

            <DialogContent className="sm:max-w-3xl">
                <DialogHeader>
                    <DialogTitle>تفاصيل الطالب</DialogTitle>
                    <DialogDescription>{description}</DialogDescription>
                </DialogHeader>

                <DialogBody className="mb-4 max-h-[70vh] overflow-y-auto">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <DetailField className="col-span-full">
                            <DetailLabel>
                                الصف الدراسي الحالي
                            </DetailLabel>
                            <DetailValue value={student.grade_level?.name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                اسم الأول للطالب
                            </DetailLabel>
                            <DetailValue value={student.first_name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                اسم الأب للطالب
                            </DetailLabel>
                            <DetailValue value={student.father_name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                اسم الجد للطالب
                            </DetailLabel>
                            <DetailValue value={student.grandfather_name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                اللقب للطالب
                            </DetailLabel>
                            <DetailValue value={student.surname} />
                        </DetailField>

                        <DetailField className="col-span-full">
                            <DetailLabel>
                                اسم الأم
                            </DetailLabel>
                            <DetailValue value={student.mother_name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                الجنسية
                            </DetailLabel>
                            <DetailValue value={student.nationality?.name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                الجنس
                            </DetailLabel>
                            <DetailValue value={student.gender.name} />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                تاريخ الميلاد
                            </DetailLabel>
                            <DetailValue value={student.date_of_birth} className="font-mono" />
                        </DetailField>

                        <DetailField>
                            <DetailLabel>
                                رقم جواز السفر
                            </DetailLabel>
                            <DetailValue value={student.passport_number} className="font-mono" />
                        </DetailField>

                        {student.is_libyan && (
                            <>
                                <DetailField>
                                    <DetailLabel>
                                        الرقم الوطني
                                    </DetailLabel>
                                    <DetailValue value={student.national_id} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>
                                        رقم القيد
                                    </DetailLabel>
                                    <DetailValue value={student.family_registration_number} className="font-mono" />
                                </DetailField>
                            </>
                        )}
                    </div>
                </DialogBody>

                <DialogFooter>
                    <DialogClose asChild>
                        <Button
                            type="button"
                            variant="outline"
                        >
                            <span>إغلاق</span>
                        </Button>
                    </DialogClose>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
