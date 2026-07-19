import { useRef } from 'react';

import { Form, usePage } from '@inertiajs/react';

import { FormLayout } from '@/components/ui/structure/form-layout';

import Heading from '@/components/ui/display/heading';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import PasswordInput from '@/components/ui/controls/password-input';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';

type PageProps = {
    passwordRules: string;
    routes: {
        update: string;
    };
};

export function SecurityForm() {
    const { passwordRules, routes } = usePage<PageProps>().props;
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <Form
            action={routes.update}
            method="put"
            options={{
                preserveScroll: true,
            }}
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
                            hasError={!!errors.current_password}
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
                            hasError={!!errors.password}
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
                            hasError={!!errors.password_confirmation}
                        />

                        <InputError message={errors.password_confirmation} />
                    </Field>

                    <div className="flex items-center gap-4">
                        <Button disabled={processing} data-test="update-password-button">
                            حفظ
                        </Button>
                    </div>
                </FormLayout>
            )}
        </Form>
    );
}

export function SecurityPageContent() {
    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="تحديث كلمة المرور"
                description="تأكد من أن حسابك يستخدم كلمة مرور طويلة وعشوائية للبقاء آمنًا"
            />

            <SecurityForm />
        </div>
    );
}
