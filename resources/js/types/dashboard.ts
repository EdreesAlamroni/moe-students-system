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
