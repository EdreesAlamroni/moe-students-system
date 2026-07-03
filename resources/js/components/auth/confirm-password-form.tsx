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
};

export function ConfirmPasswordForm({ routes }: Props) {
    return (
        <Form
            action={routes.confirmPasswordStore}
            method="post"
            resetOnSuccess={['password']}
        >
            {({ processing, errors }) => (
                <FormLayout>
                    <Field>
                        <Label htmlFor="password" required>
                            كلمة المرور
                        </Label>

                        <PasswordInput
                            id="password"
                            name="password"
                            placeholder="كلمة المرور"
                            autoComplete="current-password"
                            autoFocus
                            required
                        />

                        <InputError message={errors.password} />
                    </Field>

                    <div className="flex items-center">
                        <Button
                            className="w-full"
                            disabled={processing}
                            data-test="confirm-password-button"
                        >
                            {processing && <Spinner />}
                            تأكيد كلمة المرور
                        </Button>
                    </div>
                </FormLayout>
            )}
        </Form>
    );
}
