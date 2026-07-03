import { Head } from '@inertiajs/react';

import { ResetPasswordForm } from '@/components/auth/reset-password-form';

import type { AuthPageProps } from '@/types/auth';

type Props = AuthPageProps & {
    token: string;
    email: string;
    passwordRules: string;
};

export default function ResetPassword({ heading, routes, token, email, passwordRules }: Props) {
    return (
        <>
            <Head title={heading.title} />

            <ResetPasswordForm
                routes={routes}
                token={token}
                email={email}
                passwordRules={passwordRules}
            />
        </>
    );
}

ResetPassword.layout = ({ heading }: Props) => heading;
