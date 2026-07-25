import type { EducationMonitor } from "@/types";

export type DashboardSummary = {
    students: number;
    males: number;
    females: number;
    grade_levels: number;
    classrooms: number;
    nationalities: number;
};

export type GradeLevelDistributionItem = {
    name: string;
    males: number;
    females: number;
    students: number;
};

export type ClassroomOccupancyItem = {
    name: string;
    grade_level: string;
    students: number;
    capacity: number;
};

export type NationalityDistributionItem = {
    name: string;
    students: number;
};

export type AdministrationDashboardSummary = {
    students: number;
    males: number;
    females: number;
    schools: number;
    education_monitors: number;
    education_services_offices: number;
    warehouses: number;
    grade_levels: number;
    classrooms: number;
    academic_years: number;
    nationalities: number;
};

export type EducationMonitorDistributionItem = {
    name: string;
    males: number;
    females: number;
    students: number;
    schools: number;
};

export type SchoolDistributionItem = {
    name: string;
    students: number;
    classrooms: number;
    monitor: Pick<EducationMonitor, 'name'>;
};

export type WarehouseDashboardSummary = {
    education_monitors: number;
    schools: number;
    students: number;
    book_distributions: number;
    students_received: number;
    students_pending: number;
    completion_rate: number;
};

export type WarehouseEducationMonitorDistributionItem = {
    name: string;
    students: number;
    schools: number;
    book_distributions: number;
    students_received: number;
    students_pending: number;
    completion_rate: number;
};

export type WarehouseSchoolDistributionItem = {
    name: string;
    students: number;
    book_distributions: number;
    students_received: number;
    students_pending: number;
    completion_rate: number;
    monitor: Pick<EducationMonitor, 'name'>;
};

export type WarehouseAcademicYearTrendItem = {
    name: string;
    book_distributions: number;
    students_received: number;
    is_current: boolean;
};

export type WarehouseRecentActivityItem = {
    id: number;
    distributed_at: string | null;
    school: string;
    grade_level: string;
    monitor: string;
};
