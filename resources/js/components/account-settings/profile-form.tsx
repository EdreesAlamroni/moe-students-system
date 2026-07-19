import { Form, usePage } from '@inertiajs/react';

import { FormLayout } from '@/components/ui/structure/form-layout';

import Heading from '@/components/ui/display/heading';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { Input } from '@/components/ui/controls/input';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';

import type { Auth } from '@/types/auth';

type PageProps = {
    auth: Auth;
    routes: {
        update: string;
    };
};

export function ProfileForm() {
    const { auth, routes } = usePage<PageProps>().props;

    return (
        <Form
            action={routes.update}
            method="patch"
            options={{
                preserveScroll: true,
            }}
        >
            {({ processing, errors }) => (
                <FormLayout>
                    <Field>
                        <Label htmlFor="name" required>
                            الاسم
                        </Label>

                        <Input
                            id="name"
                            name="name"
                            defaultValue={auth.user?.name ?? ''}
                            autoComplete="name"
                            hasError={!!errors.name}
                            required
                        />

                        <InputError message={errors.name} />
                    </Field>

                    <Field>
                        <Label htmlFor="email" required>
                            البريد الإلكتروني
                        </Label>

                        <Input
                            id="email"
                            type="email"
                            name="email"
                            defaultValue={auth.user?.email ?? ''}
                            autoComplete="username"
                            hasError={!!errors.email}
                            required
                        />

                        <InputError message={errors.email} />
                    </Field>

                    <div className="flex items-center gap-4">
                        <Button disabled={processing} data-test="update-profile-button">
                            حفظ
                        </Button>
                    </div>
                </FormLayout>
            )}
        </Form>
    );
}

export function ProfilePageContent() {
    return (
        <div className="space-y-6">
            <Heading
                variant="small"
                title="الملف الشخصي"
                description="تحديث الاسم والبريد الإلكتروني"
            />

            <ProfileForm />
        </div>
    );
}
