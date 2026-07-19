import { Form } from '@inertiajs/react';

import { FormLayout } from '@/components/ui/structure/form-layout';

import { Spinner } from '@/components/ui/display/spinner';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Input } from '@/components/ui/controls/input';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';
import TextLink from '@/components/ui/actions/text-link';

import type { AuthRoutes } from '@/types/auth';

type Props = {
    routes: AuthRoutes;
};

export function ForgotPasswordForm({ routes }: Props) {
    if (!routes.forgotPasswordStore) {
        return null;
    }

    return (
        <div className="space-y-6">
            <Form action={routes.forgotPasswordStore} method="post">
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
                                autoComplete="email"
                                placeholder="البريد الإلكتروني"
                                autoFocus
                                hasError={!!errors.email}
                                required
                            />

                            <InputError message={errors.email} />
                        </Field>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                            data-test="email-password-reset-link-button"
                        >
                            {processing && <Spinner />}
                            إرسال رابط إعادة تعيين كلمة المرور
                        </Button>
                    </FormLayout>
                )}
            </Form>

            <p className="text-center text-sm text-muted-foreground">
                <span>أو، العودة إلى </span>
                <TextLink href={routes.login}>تسجيل الدخول</TextLink>
            </p>
        </div>
    );
}
