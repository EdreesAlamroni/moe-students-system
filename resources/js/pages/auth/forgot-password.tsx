import { Head } from '@inertiajs/react';

import { AuthStatus } from '@/components/auth/auth-status';
import { ForgotPasswordForm } from '@/components/auth/forgot-password-form';

import type { AuthPageProps } from '@/types/auth';

type Props = AuthPageProps & {
    status?: string;
};

export default function ForgotPassword({ heading, routes, status }: Props) {
    return (
        <>
            <Head title={heading.title} />

            <AuthStatus status={status} />

            <ForgotPasswordForm routes={routes} />
        </>
    );
}

ForgotPassword.layout = ({ heading }: Props) => heading;
