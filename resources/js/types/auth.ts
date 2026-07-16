import type { Enum, ModelState } from "@/types/index";

/** Display-friendly reference to an organizational entity. */
export type EntityReference = {
    id: number;
    name: string;
};

/** Known Laravel morph class names for the user's `organization` relationship. */
export type UserOrganizationType =
    | "App\\Models\\EducationMonitor"
    | "App\\Models\\Warehouse"
    | "App\\Models\\EducationServicesOffice"
    | "App\\Models\\School"
    | (string & {});

/** Shared parent for organizations that report to an education monitor. */
export type EducationMonitorParent = {
    education_monitor: EntityReference;
};

export type EducationMonitorOrganization = {
    education_monitor: EntityReference;
};

export type WarehouseOrganization = {
    warehouse: EntityReference;
};

export type EducationServicesOfficeOrganization = EducationMonitorParent & {
    education_services_office: EntityReference;
};

export type SchoolOrganization = EducationMonitorParent & {
    school: EntityReference;
};

/**
 * Resolved organizational context for a scoped user.
 * Root organizations have no parent; child organizations include their education monitor.
 */
export type UserOrganizationContext =
    | { type: "education_monitor"; organization: EducationMonitorOrganization }
    | { type: "education_services_office"; organization: EducationServicesOfficeOrganization }
    | { type: "school"; organization: SchoolOrganization }
    | { type: "warehouse"; organization: WarehouseOrganization };

export type UserScope = {
    id: string | number;
    name: string;
    icon: string;
};

export type User = {
    id: number;
    uuid: string;
    name: string;
    email: string;
    username: string;
    scope: Enum;
    role?: Enum;
    state: ModelState;
    request_state: ModelState;
    /** Raw morph key — mirrors `users.organization_id`. */
    organization_id?: number;
    /** Raw morph type — mirrors `users.organization_type`. */
    organization_type?: UserOrganizationType;
    /** Resolved organization for the user's attached entity, when eager-loaded. */
    organization?: UserOrganizationContext;
    role_ids: number[];
    avatar?: string;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type RoleItem = {
    id: number;
    name: string;
    label: string;
};

export type RoleGroup = {
    label: string;
    roles: RoleItem[];
};

export type Auth = {
    user: User | null;
};

export type DashboardContext = {
    key: string;
    label: string;
};

export type AuthPageHeading = {
    title: string;
    description: string;
};

export type AuthRoutes = {
    login: string;
    logout: string;
    confirmPassword: string;
    confirmPasswordStore: string;
    changePassword: string;
    changePasswordStore: string;
    forgotPassword?: string;
    forgotPasswordStore?: string;
    resetPasswordStore?: string;
};

export type AuthPageProps = {
    dashboard: DashboardContext;
    routes: AuthRoutes;
    heading: AuthPageHeading;
};
