import '@inertiajs/core';

import type { AcademicYear, Enum, GradeLevel, SchoolPeriod } from '@/types';
import type { Auth, DashboardContext } from '@/types/auth';
import type { Navigation } from '@/types/navigation';
import type { FlashMessage } from '@/types/ui';

export type EducationMonitorOrganizationContext = {
    type: 'education_monitor';
    id: number;
    name: string;
};

export type EducationServicesOfficeOrganizationContext = {
    type: 'education_services_office';
    id: number;
    name: string;
};

export type SchoolOrganizationContext = SchoolPeriod & {
    type: 'school';
    students_gender_options?: Enum[];
    available_grade_levels: GradeLevel[];
    available_periods?: Array<{
        id: number;
        name: string;
        academic_period: Enum & { display_name: string };
    }>;
};

export type OrganizationContext =
    | SchoolOrganizationContext
    | EducationMonitorOrganizationContext
    | EducationServicesOfficeOrganizationContext;

declare module 'react' {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    interface InputHTMLAttributes<T> {
        passwordrules?: string;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            dashboard: DashboardContext | null;
            sidebarOpen: boolean;
            routeName: string | null;
            navigation: Navigation;
            currentAcademicYear: AcademicYear | null;
            availableAcademicYears: AcademicYear[];
            flash: FlashMessage;
            organization: OrganizationContext | null;
        };
    }
}
