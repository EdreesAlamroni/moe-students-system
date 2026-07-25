import type { EducationMonitor, EducationServicesOffice } from "@/types";

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

export type EducationMonitorDashboardSummary = {
    students: number;
    males: number;
    females: number;
    nationalities: number;
    education_services_offices: number;
    schools: number;
    grade_levels: number;
    classrooms: number;
    students_unassigned_to_school: number;
};

export type EducationServicesOfficeDistributionItem = {
    name: string;
    males: number;
    females: number;
    students: number;
    schools: number;
};

export type EducationMonitorSchoolDistributionItem = {
    name: string;
    students: number;
    classrooms: number;
    office: Pick<EducationServicesOffice, 'name'> | null;
};

export type SchoolTypeDistribution = {
    public_schools: number;
    private_schools: number;
    public_students: number;
    private_students: number;
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
