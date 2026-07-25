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
