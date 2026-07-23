export type * from './auth';
export type * from './navigation';
export type * from './ui';

export const PERMISSION_ACTIONS = [
    'viewAny', 'view', 'create', 'update', 'delete',
    'archive', 'restore', 'forceDelete', 'stateUpdate',
    'addGradeLevel', 'removeGradeLevel', 'resetClassroomDistribution',
    'addTransferredStudent', 'transferStudentOut',
    'enrollInGradeLevel', 'enrollInClassroom', 'transferClassroom',
    'viewPsychosocialCard', 'updatePsychosocialCard', 'printPsychosocialCard',
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
    already_distributed: boolean;
    students_count: number;
    distributed_count: number;
    pending_count: number;
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

export type ClassPeriod = {
    id: number;
    uuid: string;
    academic_period: Enum;
    name: string;
    start_time: string;
    end_time: string;
    order: number;
    is_break?: boolean;
    type?: string;
    schedules_count?: number;
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
}

export interface ClassSchedule {
    id: number;
    uuid: string;
    school_id: number;
    school?: School;
    academic_year_id: number;
    academic_year?: AcademicYear;
    classroom_id: number;
    classroom?: Classroom;
    class_period_id: number;
    class_period?: ClassPeriod;
    subject_id?: number;
    subject?: Subject;
    day_of_week: Enum;
    notes?: string;
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
}

export interface ClassScheduleGridItem {
    id: number;
    uuid: string;
    subject_id?: number;
    subject?: Subject;
    notes?: string;
}

export interface ClassScheduleGrid {
    classroom: Classroom;
    days: Enum[];
    periods: ClassPeriod[];
    grid: Record<number, Record<number, ClassScheduleGridItem | null | 'break'>>;
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
    education_monitors: EducationMonitor[];
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
    education_services_offices?: EducationServicesOffice[];
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
    education_monitor?: EducationMonitor;
    education_services_office_id?: number;
    office?: EducationServicesOffice;
    education_services_office?: EducationServicesOffice;
    educational_stages?: SchoolEducationalStage[];
    educational_stages_labels?: string;
    serial_number: string;
    type: Enum;
    educational_company_name?: string;
    branch_type?: Enum;
    building_type?: Enum;
    name: string;
    academic_period: Enum;
    students_gender: Enum;
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

export type Classroom = {
    id: number;
    uuid: string;
    academic_year_id: number;
    academic_year: AcademicYear;
    school_id: number;
    school: School;
    grade_level_id: number;
    grade_level: GradeLevel;
    name: string;
    capacity: number;
    students_count?: number;
    schedules_count?: number;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type StudentEnrollment = {
    id: number;
    uuid: string;
    academic_year_id: number;
    academic_year: AcademicYear;
    grade_level_id: number;
    grade_level: GradeLevel;
    classroom_id?: number;
    classroom?: Classroom;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type Student = {
    id: number;
    uuid: string;
    education_monitor_id?: number;
    monitor?: EducationMonitor;
    education_monitor?: EducationMonitor;
    school_id?: number;
    school?: School;
    nationality_id: number;
    nationality: Nationality;
    enrollment: StudentEnrollment;
    has_enrollment: boolean;
    grade_level: GradeLevel;
    classroom?: Classroom;
    number: string;
    registration_status: Enum;
    exam_enrollment_status: Enum
    first_name: string;
    father_name: string;
    grandfather_name: string;
    surname: string;
    mother_name: string;
    gender: Enum;
    date_of_birth: string;
    national_id?: string;
    family_registration_number?: string;
    passport_number?: string;
    full_name: string;
    father_full_name: string;
    is_libyan?: boolean;
    // transfer: StudentTransfer;
    // psychosocial_card: PsychosocialCard;
    already_distributed: boolean;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export type StudentTransfer = {
    id: number;
    uuid: string;
    left_academic_year_id: number;
    left_academic_year: AcademicYear;
    joined_academic_year_id?: number;
    joined_academic_year?: AcademicYear;
    student_id: number;
    student: Student;
    from_school_id: number;
    from_school: School;
    to_school_id?: number;
    to_school?: School;
    left_school_at: string;
    joined_school_at?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}


export type StudentPsychosocialCard = {
    id?: number;
    uuid?: string;
    guardian_name?: string;
    guardian_date_of_birth?: string;
    guardian_nationality_id?: number;
    guardian_nationality?: Nationality;
    guardian_relationship?: string;
    guardian_phone_number?: string;
    guardian_education_level?: string;
    guardian_job_title?: string;
    guardian_work_place?: string;
    mother_date_of_birth?: string;
    mother_nationality_id?: number;
    mother_nationality?: Nationality;
    mother_phone_number?: string;
    mother_education_level?: string;
    mother_profession?: string;
    mother_work_place?: string;
    number_of_family_members?: number;
    student_family_order?: number;
    number_of_siblings?: number;
    student_living_situation?: Enum;
    family_situation_reason?: Enum;
    residential_area?: string;
    residential_street?: string;
    nearest_landmark?: string;
    previous_activities?: string;
    talents?: string;
    previous_diseases?: string;
    physical_disability_type?: string;
    vision_level?: Enum;
    hearing_level?: Enum;
    family_income?: Enum;
    accommodation_type?: Enum;
    accommodation_form?: Enum;
    behavioral_problems?: Array<{ label?: string; behavior: string; has_problem: boolean; notes?: string }>;
    guardian_representative_name?: string;
    guardian_representative_relationship?: string;
    guardian_representative_id_card_number?: string;
    guardian_representative_phone_number?: string;
    guardian_representative_work_place?: string;
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

export interface AcademicRecordAttempt {
    id: number;
    uuid: string;
    academic_year?: AcademicYear;
    status: Enum;
    rating?: Enum | null;
    created_at?: string;
}

export interface GroupedAcademicRecord {
    grade_level: GradeLevel;
    attempts: AcademicRecordAttempt[];
    is_passed: boolean;
    is_current?: boolean;
}

export interface AcademicRecordProgress {
    completed: number;
    total: number;
}
