import { Form } from '@inertiajs/react';

import { FormLayout } from '@/components/ui/structure/form-layout';

import { Spinner } from '@/components/ui/display/spinner';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Input } from '@/components/ui/controls/input';
import PasswordInput from '@/components/ui/controls/password-input';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';

import type { AuthRoutes } from '@/types/auth';

type Props = {
    routes: AuthRoutes;
    token: string;
    email: string;
    passwordRules: string;
};

export function ResetPasswordForm({ routes, token, email, passwordRules }: Props) {
    if (!routes.resetPasswordStore) {
        return null;
    }

    return (
        <Form
            action={routes.resetPasswordStore}
            method="post"
            transform={(data) => ({ ...data, token, email })}
            resetOnSuccess={['password', 'password_confirmation']}
        >
            {({ processing, errors }) => (
                <FormLayout>
                    <Field>
                        <Label htmlFor="email" required>
                            البريد الإلكتروني
                        </Label>

                        <Input
                            id="email"
                            type="email"
                            name="email"
                            value={email}
                            autoComplete="email"
                            readOnly
                            required
                        />

                        <InputError message={errors.email} />
                    </Field>

                    <Field>
                        <Label htmlFor="password" required>
                            كلمة المرور
                        </Label>

                        <PasswordInput
                            id="password"
                            name="password"
                            autoComplete="new-password"
                            placeholder="كلمة المرور الجديدة"
                            autoFocus
                            passwordrules={passwordRules}
                            hasError={!!errors.password}
                            required
                        />

                        <InputError message={errors.password} />
                    </Field>

                    <Field>
                        <Label htmlFor="password_confirmation" required>
                            تأكيد كلمة المرور
                        </Label>

                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            autoComplete="new-password"
                            placeholder="تأكيد كلمة المرور الجديدة"
                            passwordrules={passwordRules}
                            hasError={!!errors.password_confirmation}
                            required
                        />

                        <InputError message={errors.password_confirmation} />
                    </Field>

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        disabled={processing}
                        data-test="reset-password-button"
                    >
                        {processing && <Spinner />}
                        إعادة تعيين كلمة المرور
                    </Button>
                </FormLayout>
            )}
        </Form>
    );
}
