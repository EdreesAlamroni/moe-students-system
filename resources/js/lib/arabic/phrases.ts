import formatNumber from "@/components/shared/dashboard/format-number";

import { formatArabicAgreementForCase, verbPastThirdPerson } from "@/lib/arabic/agreement";
import { formatArabicCount } from "@/lib/arabic/count";
import type { ArabicCountForms } from "@/lib/arabic/count";
import {
    arabicNouns,
    arabicParticiples,
    arabicVerbForms,
} from "@/lib/arabic/nouns";
import type { DistributionTarget } from "@/lib/arabic/nouns";

export function studentsInclusive(count: number): string {
    return formatArabicCount(count, arabicNouns.studentsInclusive);
}

export function genderComparison(males: number, females: number): string {
    return `${formatArabicCount(males, arabicNouns.males)} مقابل ${formatArabicCount(females, arabicNouns.females)}`;
}

export function countComparison(
    left: number,
    right: number,
    leftForms: ArabicCountForms,
    rightForms: ArabicCountForms,
    separator = "مقابل",
): string {
    return `${formatArabicCount(left, leftForms)} ${separator} ${formatArabicCount(right, rightForms)}`;
}

export function schoolTypeComparison(publicCount: number, privateCount: number): string {
    return countComparison(
        publicCount,
        privateCount,
        arabicNouns.publicSchoolType,
        arabicNouns.privateSchoolType,
    );
}

export function schoolTypeStudentComparison(publicCount: number, privateCount: number): string {
    return countComparison(
        publicCount,
        privateCount,
        arabicNouns.publicSchoolTypeScoped,
        arabicNouns.privateSchoolTypeScoped,
    );
}

export function studentsDistributedAcross(
    students: number,
    targetCount: number,
    target: DistributionTarget,
): string {
    const studentLabel = formatArabicCount(students, arabicNouns.students);
    const targetLabel = formatArabicCount(targetCount, arabicNouns[target]);
    const participle = formatArabicAgreementForCase(
        students,
        arabicParticiples.distributed,
        "nominative",
    );

    return `${studentLabel} ${participle} على ${targetLabel}`;
}

export function studentsGroupedAcross(
    students: number,
    schools: number,
    prefix: "يضم" | "تضم",
): string {
    const studentLabel = formatArabicCount(students, arabicNouns.students);
    const schoolLabel = formatArabicCount(schools, arabicNouns.schools);
    const participle = formatArabicAgreementForCase(
        students,
        arabicParticiples.distributed,
        "accusative",
    );

    return `${prefix} ${studentLabel} ${participle} على ${schoolLabel}`;
}

export function assignmentStatus(assigned: number, unassigned: number): string {
    const assignedLabel = formatArabicCount(assigned, arabicNouns.students);
    const assignedParticiple = formatArabicAgreementForCase(
        assigned,
        arabicParticiples.assigned,
        "nominative",
    );
    const unassignedLabel = formatArabicCount(unassigned, arabicNouns.students);
    const unassignedParticiple = formatArabicAgreementForCase(
        unassigned,
        arabicParticiples.assigned,
        "accusative",
    );

    return `${assignedLabel} ${assignedParticiple} و ${unassignedLabel} غير ${unassignedParticiple} لمدرسة`;
}

export function enrollmentStatus(enrolled: number, unenrolled: number): string {
    const enrolledLabel = formatArabicCount(enrolled, arabicNouns.students);
    const enrolledParticiple = formatArabicAgreementForCase(
        enrolled,
        arabicParticiples.enrolled,
        "nominative",
    );
    const unenrolledLabel = formatArabicCount(unenrolled, arabicNouns.students);
    const unenrolledParticiple = formatArabicAgreementForCase(
        unenrolled,
        arabicParticiples.enrolled,
        "accusative",
    );

    return `${enrolledLabel} ${enrolledParticiple} و ${unenrolledLabel} غير ${unenrolledParticiple} بالصفوف الدراسية`;
}

export function monitorsAndSchools(monitors: number, schools: number): string {
    return `${formatArabicCount(monitors, arabicNouns.monitors)} و ${formatArabicCount(schools, arabicNouns.schools)}`;
}

export function studentsFromSeats(students: number, seats: number): string {
    return `${formatArabicCount(students, arabicNouns.students)} من أصل ${formatArabicCount(seats, arabicNouns.seats)}`;
}

export function studentsReceivedBooks(received: number, eligible: number): string {
    const receivedLabel = formatArabicCount(received, arabicNouns.students);

    const verb = verbPastThirdPerson(received, arabicVerbForms.receivedBooks);

    return `${receivedLabel} ${verb} الكُتب من أصل ${formatNumber(eligible)} ضمن الصفوف المؤكَّدة`;
}

export function schoolDistributionDetail(schools: number, monitors: number): string {
    return `متوسط عدد الطلبة لكل مدرسة عبر ${formatArabicCount(schools, arabicNouns.schools)} و ${formatArabicCount(monitors, arabicNouns.monitors)}`;
}

export function warehouseCoverageDetail(
    monitors: number,
    schools: number,
    averageStudentsPerSchool: number,
): string {
    return `${monitorsAndSchools(monitors, schools)} بمتوسط ${formatArabicCount(averageStudentsPerSchool, arabicNouns.students)} لكل مدرسة`;
}

export function completionRateDetail(
    completionRate: number,
    received: number,
    pending: number,
): string {
    const receivedLabel = formatArabicCount(received, arabicNouns.recipients);
    const pendingLabel = formatArabicCount(pending, arabicNouns.pendingDeliveries);

    return `أنجزت ${completionRate}٪ (${receivedLabel} / ${pendingLabel})`;
}

export function completionRateWithPendingDetail(
    completionRate: number,
    pending: number,
): string {
    const pendingLabel = formatArabicCount(pending, arabicNouns.pendingDeliveries);

    return `أنجزت ${completionRate}٪ مع ${pendingLabel}`;
}

export const emptyStates = {
    noEnrolledStudents: () => "لا يوجد طلبة مسجّلون حالياً",
    noGradeLevelEnrolledStudents: () => "لا يوجد طلبة مقيد بالصفوف الدراسية",
    noGradeLevelEnrolledStudentsCurrently: () => "لا يوجد طلبة مقيد بالصفوف الدراسية حالياً",
    noAssignedStudents: (target: string) => `لا يوجد طلبة مسند إلى ${target}`,
    noRegisteredStudentsInSchools: () => "لا يوجد طلبة مسجّلون في المدارس",
    noRegisteredStudentsInPublicSchools: () => "لا يوجد طلبة مسجّلون في المدارس العامة",
    noRegisteredStudentsInPrivateSchools: () => "لا يوجد طلبة مسجّلون في المدارس الخاصة",
    noRegisteredStudentsInSchoolType: () => "لا يوجد طلبة مسند إلى المدارس",
    noClassroomsForCurrentYear: () => "لا توجد فصول دراسية للسنة الدراسية الحالية",
    noClassroomsCurrently: () => "لا توجد فصول دراسية حالياً",
    noClassroomsWithCapacity: () => "لا توجد فصول دراسية بسعة محددة",
    noSchoolsRegistered: () => "لا توجد مدارس مسجلة حالياً",
    noMonitorsAssigned: () => "لا توجد مُراقبات تعليمية مسندة إلى هذا المخزن",
    noStudentsAwaitingClassroom: () => "لا يوجد طلبة مسجّلون في هذا الصف الدراسي بانتظار تعيين فصل دراسي للسنة الدراسية الحالية",
    noStudentsInGradeLevel: () => "لا يوجد طلبة مسجّلون في هذا الصف الدراسي",
    studentsEnrolledWithoutGradeLevel: () => "يوجد طلبة مسجّل في السنة الدراسية الحالية، لكن لم يُسجَّل أيٌّ منهم في صف دراسي. يُرجى تسجيلهم في الصف الدراسي المناسب قبل تنفيذ التوزيع",
} as const;
