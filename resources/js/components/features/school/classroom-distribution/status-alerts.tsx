import { usePage } from "@inertiajs/react";

import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alerts/alert';

import type { EnrollmentSummary } from '@/types/classroom-distribution';

import { emptyStates } from '@/lib/arabic-labels';

import { AlertTriangleIcon, CheckCircle2Icon, InfoIcon } from 'lucide-react';

interface StatusAlertsProps {
    isDistributionCompleted: boolean;
    canDistribute: boolean;
    isAcademicYearActive: boolean;
    enrollmentSummary?: EnrollmentSummary;
}

export default function StatusAlerts({
    isDistributionCompleted,
    canDistribute,
    isAcademicYearActive,
    enrollmentSummary,
}: StatusAlertsProps) {
    const { currentAcademicYear } = usePage().props;

    const hasEligibleEnrollments = (enrollmentSummary?.eligibleCount ?? 0) > 0;
    const hasAnyEnrollments = (enrollmentSummary?.totalCount ?? 0) > 0;
    const withoutGradeLevelCount = enrollmentSummary?.withoutGradeLevelCount ?? 0;

    return (
        <>
            {isAcademicYearActive && (
                <Alert variant="info">
                    <InfoIcon />
                    <AlertTitle>
                        تنبيه بخصوص إعادة تعيين توزيع الفصول الدراسية
                    </AlertTitle>
                    <AlertDescription>
                        إذا كنت بحاجة إلى إعادة تعيين توزيع الفصول الدراسية
                        لجميع الصفوف أو لصف معين، يرجى التواصل مع المراقبة
                        التابع لها لإتمام هذه العملية.
                    </AlertDescription>
                </Alert>
            )}

            {(currentAcademicYear === null) && (
                <Alert variant="info">
                    <InfoIcon />
                    <AlertTitle>لم يتم تفعيل سنة دراسية حالياً</AlertTitle>
                    <AlertDescription>
                        لا توجد سنة دراسية نشطة حالياً.
                        يرجى انتظار تفعيل السنة الدراسية من الإدارة لبدء توزيع الطلبة.
                    </AlertDescription>
                </Alert>
            )}

            {(!isAcademicYearActive && currentAcademicYear !== null) && (
                <Alert variant="info">
                    <InfoIcon />
                    <AlertTitle>عرض سنة دراسية سابقة</AlertTitle>
                    <AlertDescription>
                        أنت تستعرض بيانات سنة دراسية سابقة. عمليات التوزيع
                        والتعديل متاحة فقط للسنة الدراسية الحالية.
                    </AlertDescription>
                </Alert>
            )}

            {isAcademicYearActive &&
                enrollmentSummary &&
                !hasAnyEnrollments &&
                !isDistributionCompleted && (
                    <Alert variant="info">
                        <InfoIcon />
                        <AlertTitle>لا توجد تسجيلات طلبة</AlertTitle>
                        <AlertDescription>
                            لا توجد تسجيلات طلبة للسنة الدراسية الحالية. يُرجى
                            تسجيل الطلبة في السنة الدراسية أولاً قبل تنفيذ
                            التوزيع أو إتمامه.
                        </AlertDescription>
                    </Alert>
                )}

            {isAcademicYearActive &&
                enrollmentSummary &&
                hasAnyEnrollments &&
                !hasEligibleEnrollments &&
                !isDistributionCompleted && (
                    <Alert variant="warning">
                        <AlertTriangleIcon />
                        <AlertTitle>الطلبة غير مسجّلين في صف دراسي</AlertTitle>
                        <AlertDescription>
                            {emptyStates.studentsEnrolledWithoutGradeLevel()}
                        </AlertDescription>
                    </Alert>
                )}

            {isAcademicYearActive &&
                enrollmentSummary &&
                hasEligibleEnrollments &&
                withoutGradeLevelCount > 0 &&
                !isDistributionCompleted && (
                    <Alert variant="warning">
                        <AlertTriangleIcon />
                        <AlertTitle>
                            بعض الطلبة غير مسجّلين في صف دراسي
                        </AlertTitle>
                        <AlertDescription>
                            يتبقى{' '}
                            <strong className="font-mono tabular-nums">
                                {withoutGradeLevelCount}
                            </strong>{' '}
                            طالب/طالبة غير مسجّلين في أي صف دراسي للسنة الدراسية
                            الحالية. يُرجى إكمال تسجيلهم في صف دراسي قبل إتمام
                            التوزيع.
                        </AlertDescription>
                    </Alert>
                )}

            {isAcademicYearActive && isDistributionCompleted && (
                <Alert>
                    <CheckCircle2Icon />
                    <AlertTitle>تم إتمام التوزيع</AlertTitle>
                    <AlertDescription>
                        تم إنهاء عملية توزيع الطلبة على الفصول للسنة الدراسية
                        الحالية. لا يمكن تنفيذ توزيع جديد أو تعديل التوزيع من
                        هذه الشاشة.
                    </AlertDescription>
                </Alert>
            )}

            {isAcademicYearActive &&
                !canDistribute &&
                hasEligibleEnrollments &&
                !isDistributionCompleted && (
                    <Alert variant="destructive">
                        <AlertTriangleIcon />
                        <AlertTitle>لا تملك صلاحية التوزيع</AlertTitle>
                        <AlertDescription>
                            عرض فقط — يلزم صلاحية تنفيذ التوزيع لتنفيذ توزيع
                            الطلبة على الفصول الدراسية.
                        </AlertDescription>
                    </Alert>
                )}
        </>
    );
}
