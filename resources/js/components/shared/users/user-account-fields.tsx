import UserNameField from '@/components/shared/users/user-name-field';
import UsernameField from '@/components/shared/users/username-field';
import EmailField from '@/components/shared/users/email-field';
import PasswordField from '@/components/shared/users/password-field';

import { DetailField } from '@/components/ui/display/detail-field';
import { DetailLabel } from '@/components/ui/display/detail-label';
import { DetailValue } from '@/components/ui/display/detail-value';

import type { UserFormErrors } from '@/components/shared/users/user-form-types';

type UserAccountFieldsProps = {
    mode: 'create' | 'edit';
    errors: UserFormErrors;
    defaults?: {
        name?: string;
        username?: string;
        email?: string | null;
    };
};

export default function UserAccountFields({
    mode,
    errors,
    defaults,
}: UserAccountFieldsProps) {
    return (
        <>
            <UserNameField
                error={errors.name}
                defaultValue={defaults?.name}
            />

            {mode === 'create' ? (
                <UsernameField error={errors.username} />
            ) : (
                <DetailField>
                    <DetailLabel>اسم المُستخدم</DetailLabel>
                    <DetailValue value={defaults?.username} className="font-mono" />
                </DetailField>
            )}

            <EmailField
                error={errors.email}
                defaultValue={defaults?.email ?? undefined}
                className="col-span-full"
            />

            {mode === 'create' && (
                <PasswordField
                    passwordError={errors.password}
                    passwordConfirmationError={errors.password_confirmation}
                />
            )}
        </>
    );
}
