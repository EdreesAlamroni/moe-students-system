import { cn } from '@/lib/utils';

import type { Student } from '@/types';

import { DetailField } from '@/components/ui/display/detail-field';
import { DetailLabel } from '@/components/ui/display/detail-label';
import { DetailValue } from '@/components/ui/display/detail-value';


type StudentEnrollmentFieldsProps = {
    student: Pick<Student, 'has_enrollment' | 'grade_level' | 'classroom'>;
};

function getGradeLevelDisplay(student: StudentEnrollmentFieldsProps['student']) {
    const name = student.grade_level?.name;
    const isEmpty = !student.has_enrollment || !name;

    return {
        value: isEmpty ? 'غير مسجل في صف دراسي' : name,
        isEmpty,
    };
}

function getClassroomDisplay(student: StudentEnrollmentFieldsProps['student']) {
    const classroomName = student.classroom?.name;
    const isEmpty = !student.has_enrollment || !classroomName;

    return {
        value: isEmpty
            ? 'غير مسجل في فصل دراسي'
            : `${student.grade_level?.name} / ${classroomName}`,
        isEmpty,
    };
}

export function StudentGradeLevelField({ student }: StudentEnrollmentFieldsProps) {
    const { value, isEmpty } = getGradeLevelDisplay(student);

    return (
        <DetailField>
            <DetailLabel>
                الصف الدراسي الحالي
            </DetailLabel>
            <DetailValue value={value} className={cn(isEmpty && 'text-muted-foreground')} />
        </DetailField>
    );
}

export function StudentClassroomField({ student }: StudentEnrollmentFieldsProps) {
    const { value, isEmpty } = getClassroomDisplay(student);

    return (
        <DetailField>
            <DetailLabel>
                الفصل الدراسي الحالي
            </DetailLabel>
            <DetailValue value={value} className={cn(isEmpty && 'text-muted-foreground')} />
        </DetailField>
    );
}
