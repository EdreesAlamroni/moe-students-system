import { Head } from '@inertiajs/react';

import { AuthStatus } from '@/components/auth/auth-status';
import { LoginForm } from '@/components/auth/login-form';

import type { AuthPageProps } from '@/types/auth';

type Props = AuthPageProps & {
    status?: string;
};

export default function Login({ heading, routes, status }: Props) {
    return (
        <>
            <Head title={heading.title} />

            <AuthStatus status={status} />

            <LoginForm routes={routes} />
        </>
    );
}

Login.layout = ({ heading }: Props) => heading;
