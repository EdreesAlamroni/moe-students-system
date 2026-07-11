export type * from './auth';
export type * from './navigation';
export type * from './ui';

export const PERMISSION_ACTIONS = [
    'viewAny', 'view', 'create', 'update', 'delete',
    'archive', 'restore', 'forceDelete', 'stateUpdate',
    'addGradeLevel', 'removeGradeLevel', 'resetClassroomDistribution',
    'addTransferredStudent', 'transferStudentOut',
    'enrollInGradeLevel', 'enrollInClassroom',
    'viewPsychosocialCard', 'updatePsychosocialCard',
    'viewAcademicRecord', 'createAcademicRecord',
    'print', 'export',
    'close',
] as const;

export type PermissionAction = typeof PERMISSION_ACTIONS[number];

export type CanPermissions = Record<PermissionAction, boolean>;

export type Enum = {
    key: string;
    id: string;
    name: string;
}

export type ModelState = {
    id: string,
    name: string,
    uiClasses: string,
    action?: string,
}

export type BooleanSelectOption = {
    id: boolean;
    name: string;
}

export type PaginationMeta = {
    current_page: number;
    from: number;
    to: number;
    per_page: number;
    last_page: number;
    total: number;
    first_page_url: string;
    last_page_url: string;
    next_page_url: string | null;
    prev_page_url: string | null;
    path: string;
}

export type PaginationLink = {
    url: string | null;
    label: string;
    page: number | null;
    active: boolean;
}

export type Paginated<T> = PaginationMeta & {
    data: T[];
    links: PaginationLink[];
}

export type Nationality = {
    id: number;
    uuid: string;
    name: string;
    code: string;
    full_name: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type Municipal = {
    id: number;
    uuid: string;
    name: string;
    schools_count?: number;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export type AcademicYear = {
    id: number;
    uuid: string;
    name: string;
    start_date?: string;
    end_date?: string;
    is_active: boolean;
    status?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type GradeLevel = {
    id: number;
    uuid: string;
    name: string;
    code: string;
    educational_stage: Enum;
    order: number;
    created_at: string,
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type Subject = {
    id: number;
    uuid: string;
    grade_level_id: number;
    grade_level: GradeLevel;
    name: string;
    code: string;
    included_in_total_score: boolean;
    included_in_total_score_label: string;
    needs_lab: boolean;
    needs_lab_label: string;
    description?: string;
    created_at: string,
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type Warehouse = {
    id: number;
    uuid: string;
    name: string;
    address?: string;
    add_location_to_map?: boolean;
    latitude?: string;
    longitude?: string;
    has_coordinates: boolean;
    monitors: EducationMonitor[];
    monitors_count: number;
    schools_count: number;
    education_monitor_ids: number[];
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type EducationMonitor = {
    id: number;
    uuid: string;
    municipal_id?: number;
    municipal?: Municipal;
    name: string;
    phone_number?: string;
    whatsapp_phone_number?: string;
    formatted_whatsapp_phone_number?: string;
    address?: string;
    add_location_to_map?: boolean;
    latitude?: string;
    longitude?: string;
    has_coordinates?: boolean;
    offices?: EducationServicesOffice[];
    schools?: School[];
    offices_count?: number;
    schools_count?: number;
    students_count?: number;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type EducationServicesOffice = {
    id: number;
    uuid: string;
    education_monitor_id: number;
    monitor?: EducationMonitor;
    education_monitor?: EducationMonitor;
    name: string;
    phone_number?: string;
    whatsapp_phone_number?: string;
    formatted_whatsapp_phone_number?: string;
    address?: string;
    add_location_to_map?: boolean;
    latitude?: string;
    longitude?: string;
    has_coordinates?: boolean;
    schools?: {
        id: number;
        uuid: string;
        name: string;
    }[];
    schools_count?: number;
    students_count?: number;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface School {
    id: number;
    uuid: string;
    education_monitor_id: number;
    monitor?: EducationMonitor;
    education_services_office_id?: number;
    office?: EducationServicesOffice;
    educational_stages?: SchoolEducationalStage[];
    serial_number: string;
    type: Enum;
    educational_company_name?: string;
    branch_type?: Enum;
    name: string;
    academic_period?: Enum;
    students_gender?: Enum;
    phone_number?: string;
    whatsapp_phone_number?: string;
    address?: string;
    is_public?: boolean;
    is_private?: boolean;
    is_morning_period?: boolean;
    is_evening_period?: boolean;
    grade_levels_count?: number;
    classrooms_count?: number;
    students_count?: number;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface SchoolEducationalStage {
    id: number;
    school_id: number;
    school?: School;
    academic_year_id?: number;
    academic_year?: AcademicYear;
    stage: Enum;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
};
