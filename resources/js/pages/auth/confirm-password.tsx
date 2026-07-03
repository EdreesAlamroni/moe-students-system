import { Head } from '@inertiajs/react';

import { ConfirmPasswordForm } from '@/components/auth/confirm-password-form';

import type { AuthPageProps } from '@/types/auth';

type Props = AuthPageProps;

export default function ConfirmPassword({ heading, routes }: Props) {
    return (
        <>
            <Head title={heading.title} />

            <ConfirmPasswordForm routes={routes} />
        </>
    );
}

ConfirmPassword.layout = ({ heading }: Props) => heading;
