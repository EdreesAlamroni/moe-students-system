import '@inertiajs/core';

import type { Auth, DashboardContext } from '@/types/auth';
import type { AcademicYear } from '@/types';
import type { Navigation } from '@/types/navigation';
import type { FlashMessage } from '@/types/ui';

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
        };
    }
}
