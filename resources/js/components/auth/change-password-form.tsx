import { useRef } from 'react';

import { Form } from '@inertiajs/react';

import { FormLayout } from '@/components/ui/structure/form-layout';

import { Spinner } from '@/components/ui/display/spinner';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import PasswordInput from '@/components/ui/controls/password-input';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';

import type { AuthRoutes } from '@/types/auth';

type Props = {
    routes: AuthRoutes;
    passwordRules: string;
};

export function ChangePasswordForm({ routes, passwordRules }: Props) {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <Form
            action={routes.changePasswordStore}
            method="post"
            resetOnError={['password', 'password_confirmation', 'current_password']}
            resetOnSuccess
            onError={(errors) => {
                if (errors.password) {
                    passwordInput.current?.focus();
                }

                if (errors.current_password) {
                    currentPasswordInput.current?.focus();
                }
            }}
        >
            {({ errors, processing }) => (
                <FormLayout>
                    <Field>
                        <Label htmlFor="current_password">كلمة المرور الحالية</Label>

                        <PasswordInput
                            id="current_password"
                            ref={currentPasswordInput}
                            name="current_password"
                            autoComplete="current-password"
                            placeholder="كلمة المرور الحالية"
                            hasErrors={!!errors.current_password}
                            autoFocus
                            required
                        />

                        <InputError message={errors.current_password} />
                    </Field>

                    <Field>
                        <Label htmlFor="password">كلمة المرور الجديدة</Label>

                        <PasswordInput
                            id="password"
                            ref={passwordInput}
                            name="password"
                            autoComplete="new-password"
                            placeholder="كلمة المرور الجديدة"
                            passwordrules={passwordRules}
                            hasErrors={!!errors.password}
                            required
                        />

                        <InputError message={errors.password} />
                    </Field>

                    <Field>
                        <Label htmlFor="password_confirmation">تأكيد كلمة المرور</Label>

                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            autoComplete="new-password"
                            placeholder="تأكيد كلمة المرور"
                            passwordrules={passwordRules}
                            hasErrors={!!errors.password_confirmation}
                            required
                        />

                        <InputError message={errors.password_confirmation} />
                    </Field>

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        disabled={processing}
                        data-test="change-password-button"
                    >
                        {processing && <Spinner />}
                        تحديث كلمة المرور
                    </Button>
                </FormLayout>
            )}
        </Form>
    );
}
