import React from 'react';

import { Form } from '@inertiajs/react';

import type { User } from '@/types';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import PasswordInput from '@/components/ui/controls/password-input';
import InputError from '@/components/ui/controls/input-error';

import { Button } from '@/components/ui/actions/button';
import { UpdateButton } from '@/components/ui/actions/submit-button';

import {
    Dialog,
    DialogBody,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogFormLayout,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/overlay/dialog';

import { KeyRoundIcon } from 'lucide-react';

import { update as passwordUpdate } from '@/routes/administration/users/password';

type UpdatePasswordProps = {
    user: User;
};

export function UpdatePassword({ user }: UpdatePasswordProps) {
    const [isOpen, setIsOpen] = React.useState(false);
    const [formKey, setFormKey] = React.useState(0);

    function handleOpenChange(open: boolean): void {
        setIsOpen(open);

        if (!open) {
            setFormKey((key) => key + 1);
        }
    }

    return (
        <Dialog
            open={isOpen}
            onOpenChange={handleOpenChange}
        >
            <DialogTrigger asChild>
                <Button
                    type="button"
                    variant="outline"
                >
                    <KeyRoundIcon />
                    <span>تحديث كلمة المرور</span>
                </Button>
            </DialogTrigger>

            <DialogContent>
                <Form
                    key={formKey}
                    {...passwordUpdate.form({ user: user })}
                    disableWhileProcessing
                    resetOnError={['password', 'password_confirmation']}
                    onSuccess={() => handleOpenChange(false)}
                    options={{
                        preserveScroll: true,
                        preserveState: 'errors',
                    }}
                >
                    {({ processing, errors }) => (
                        <DialogFormLayout>
                            <DialogHeader>
                                <DialogTitle>تحديث كلمة المرور</DialogTitle>
                                <DialogDescription>
                                    قم بتعيين كلمة مرور جديدة لحساب المُستخدم{' '}
                                    <span className="font-semibold">{user.name}</span>.
                                </DialogDescription>
                            </DialogHeader>

                            <DialogBody>
                                <Field>
                                    <Label
                                        htmlFor="password"
                                        hasError={!!errors.password}
                                        required
                                    >
                                        كلمة المرور
                                    </Label>

                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        autoComplete="new-password"
                                        hasError={!!errors.password}
                                        required
                                        autoFocus
                                    />

                                    <InputError message={errors.password} />
                                </Field>

                                <Field>
                                    <Label
                                        htmlFor="password_confirmation"
                                        hasError={!!errors.password_confirmation}
                                        required
                                    >
                                        تأكيد كلمة المرور
                                    </Label>

                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                        hasError={!!errors.password_confirmation}
                                        required
                                    />

                                    <InputError message={errors.password_confirmation} />
                                </Field>
                            </DialogBody>

                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline">
                                        <span>إغلاق</span>
                                    </Button>
                                </DialogClose>

                                <UpdateButton processing={processing} />
                            </DialogFooter>
                        </DialogFormLayout>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
