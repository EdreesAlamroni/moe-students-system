import React from 'react';

import { Form } from '@inertiajs/react';

import type { User } from '@/types';

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

import PasswordField from "@/components/shared/users/password-field";

import { LockKeyholeIcon } from 'lucide-react';

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
                    <LockKeyholeIcon />
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
                                <PasswordField
                                    passwordError={errors.password}
                                    passwordConfirmationError={errors.password_confirmation}
                                />
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
