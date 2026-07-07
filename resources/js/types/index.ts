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
