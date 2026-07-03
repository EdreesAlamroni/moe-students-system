import { Head } from '@inertiajs/react';

import { ChangePasswordForm } from '@/components/auth/change-password-form';

import type { AuthPageProps } from '@/types/auth';

type Props = AuthPageProps & {
    passwordRules: string;
};

export default function ChangePassword({ heading, routes, passwordRules }: Props) {
    return (
        <>
            <Head title={heading.title} />

            <ChangePasswordForm routes={routes} passwordRules={passwordRules} />
        </>
    );
}

ChangePassword.layout = ({ heading }: Props) => heading;
