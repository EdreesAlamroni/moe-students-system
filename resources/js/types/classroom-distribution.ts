import type { Classroom, GradeLevel, Student } from '@/types';

export type ClassroomRow = Classroom & {
    remaining_capacity: number;
}

export type ListStudent = Student;

export type ClassroomDistributionMethod = {
    name: string;
    value: string;
    description: string;
    icon: string;
    route: string;
}

export type EnrollmentSummary = {
    totalCount: number;
    eligibleCount: number;
    withoutGradeLevelCount: number;
    withoutClassroomCount: number;
}

export type IndexPageProps = {
    methods: ClassroomDistributionMethod[];
    isDistributionCompleted: boolean;
    enrollmentSummary: EnrollmentSummary;
    schoolWideUnassignedCount: number;
    can: {
        distribute: boolean;
        finalize: boolean;
    };
}

export type RandomPageProps = {
    gradeLevels: GradeLevel[];
    selectedGradeLevelId: number | null;
    gradeLevel: GradeLevel | null;
    classrooms: ClassroomRow[];
    pendingStudentCount: number;
    isDistributionCompleted: boolean;
    method: ClassroomDistributionMethod;
    can: {
        distribute: boolean;
    };
}

export type ManualPageProps = {
    gradeLevels: GradeLevel[];
    selectedGradeLevelId: number | null;
    gradeLevel: GradeLevel | null;
    classrooms: ClassroomRow[];
    unassignedStudents: ListStudent[];
    isDistributionCompleted: boolean;
    method: ClassroomDistributionMethod;
    can: {
        distribute: boolean;
    };
}
