import type { AuthRoutes, DashboardContext } from '@/types/auth';

export type AuthStatusProps = {
    status?: string;
};

export function AuthStatus({ status }: AuthStatusProps) {
    if (!status) {
        return null;
    }

    return (
        <div
            aria-live="polite"
            className="mb-4 text-center text-sm font-medium text-green-600"
        >
            {status}
        </div>
    );
}

export type { AuthRoutes, DashboardContext };
