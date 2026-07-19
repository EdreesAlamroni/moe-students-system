import { Form } from '@inertiajs/react';

import { FormLayout } from '@/components/ui/structure/form-layout';

import { Spinner } from '@/components/ui/display/spinner';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Input } from '@/components/ui/controls/input';
import PasswordInput from '@/components/ui/controls/password-input';
import InputError from '@/components/ui/controls/input-error';
import { Checkbox } from '@/components/ui/controls/checkbox';

import { Button } from '@/components/ui/actions/button';
import TextLink from '@/components/ui/actions/text-link';

import type { AuthRoutes } from '@/types/auth';

type Props = {
    routes: AuthRoutes;
};

export function LoginForm({ routes }: Props) {
    return (
        <Form
            action={routes.login}
            method="post"
            resetOnSuccess={['password']}
            className="flex flex-col gap-6"
        >
            {({ processing, errors }) => (
                <FormLayout>
                    <Field>
                        <Label htmlFor="username">اسم المُستخدم</Label>

                        <Input
                            id="username"
                            type="text"
                            name="username"
                            autoComplete="username"
                            placeholder="اسم المُستخدم"
                            hasError={!!errors.username}
                            autoFocus
                            tabIndex={1}
                            required
                        />

                        <InputError message={errors.username} />
                    </Field>

                    <Field>
                        <div className="flex items-center">
                            <Label htmlFor="password">كلمة المرور</Label>
                            {routes.forgotPassword && (
                                <TextLink
                                    href={routes.forgotPassword}
                                    className="ms-auto text-sm"
                                    tabIndex={5}
                                >
                                    هل نسيت كلمة المرور؟
                                </TextLink>
                            )}
                        </div>

                        <PasswordInput
                            id="password"
                            name="password"
                            autoComplete="current-password"
                            placeholder="كلمة المرور"
                            tabIndex={2}
                            required
                        />

                        <InputError message={errors.password} />
                    </Field>

                    <div className="flex items-center gap-x-3">
                        <Checkbox id="remember" name="remember" tabIndex={3} />

                        <Label htmlFor="remember">تذكرني</Label>
                    </div>

                    <Button
                        type="submit"
                        className="mt-4 w-full"
                        tabIndex={4}
                        disabled={processing}
                        data-test="login-button"
                    >
                        {processing && <Spinner />}
                        دخـول
                    </Button>
                </FormLayout>
            )}
        </Form>
    );
}
