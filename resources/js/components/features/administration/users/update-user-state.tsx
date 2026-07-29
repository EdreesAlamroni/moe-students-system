import React from 'react';

import { Form } from '@inertiajs/react';

import type { ModelState, User } from '@/types';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/controls/select';
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

import { IdCardIcon, ShieldAlertIcon } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

import { update as stateUpdate } from '@/routes/administration/users/state';
import { update as requestStateUpdate } from '@/routes/administration/users/request-state';

type UpdateUserStateDialogConfig = {
    fieldName: 'state' | 'request_state';
    buttonLabel: string;
    title: string;
    description: React.ReactNode;
    icon: LucideIcon;
    resolveForm: (user: User) => ReturnType<typeof stateUpdate.form>;
};

type UpdateUserStateDialogProps = {
    user: User;
    availableStates: ModelState[];
    config: UpdateUserStateDialogConfig;
};

function UpdateUserStateDialog({
    user,
    availableStates,
    config,
}: UpdateUserStateDialogProps) {
    const [isOpen, setIsOpen] = React.useState(false);
    const [selectedStateId, setSelectedStateId] = React.useState<string | undefined>();

    const Icon = config.icon;

    function handleOpenChange(open: boolean): void {
        setIsOpen(open);

        if (!open) {
            setSelectedStateId(undefined);
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
                    <Icon />
                    <span>{config.buttonLabel}</span>
                </Button>
            </DialogTrigger>

            <DialogContent>
                <Form
                    {...config.resolveForm(user)}
                    disableWhileProcessing
                    resetOnError={[config.fieldName]}
                    onSuccess={() => handleOpenChange(false)}
                    options={{
                        preserveScroll: true,
                        preserveState: 'errors',
                    }}
                >
                    {({ processing, errors }) => {
                        const fieldError = errors[config.fieldName];

                        return (
                            <DialogFormLayout>
                                <DialogHeader>
                                    <DialogTitle>{config.title}</DialogTitle>
                                    <DialogDescription>
                                        {config.description}
                                    </DialogDescription>
                                </DialogHeader>

                                <DialogBody>
                                    <Field>
                                        <Label
                                            htmlFor={config.fieldName}
                                            hasError={!!fieldError}
                                            required
                                        >
                                            الحالة
                                        </Label>

                                        <Select
                                            name={config.fieldName}
                                            value={selectedStateId}
                                            onValueChange={setSelectedStateId}
                                            required
                                        >
                                            <SelectTrigger
                                                id={config.fieldName}
                                                hasError={!!fieldError}
                                            >
                                                <SelectValue placeholder="اختر الحالة" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {availableStates.map((state) => (
                                                        <SelectItem
                                                            key={state.id}
                                                            value={state.id}
                                                        >
                                                            {state.action ?? state.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>

                                        <InputError message={fieldError} />
                                    </Field>
                                </DialogBody>

                                <DialogFooter>
                                    <DialogClose asChild>
                                        <Button type="button" variant="outline">
                                            <span>إغلاق</span>
                                        </Button>
                                    </DialogClose>

                                    <UpdateButton
                                        processing={processing}
                                        disabled={processing || !selectedStateId}
                                    />
                                </DialogFooter>
                            </DialogFormLayout>
                        );
                    }}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

type UpdateAccountStateProps = {
    user: User;
    availableStates: ModelState[];
};

export function UpdateAccountState({ user, availableStates }: UpdateAccountStateProps) {
    return (
        <UpdateUserStateDialog
            user={user}
            availableStates={availableStates}
            config={{
                fieldName: 'state',
                buttonLabel: 'حالة الحساب',
                title: 'تحديث حالة الحساب',
                description: (
                    <>
                        الحالة الحالية لحساب المُستخدم:{' '}
                        <span className="font-semibold">{user.state.name}</span>.
                    </>
                ),
                icon: IdCardIcon,
                resolveForm: (target) => stateUpdate.form({ user: target }),
            }}
        />
    );
}

type UpdateRequestStateProps = {
    user: User;
    availableRequestStates: ModelState[];
};

export function UpdateRequestState({ user, availableRequestStates }: UpdateRequestStateProps) {
    return (
        <UpdateUserStateDialog
            user={user}
            availableStates={availableRequestStates}
            config={{
                fieldName: 'request_state',
                buttonLabel: 'حالة الطلب',
                title: 'تحديث حالة الطلب',
                description: (
                    <>
                        حالة الطلب الحالية لحساب المُستخدم:{' '}
                        <span className="font-semibold">{user.request_state.name}</span>.
                    </>
                ),
                icon: ShieldAlertIcon,
                resolveForm: (target) => requestStateUpdate.form({ user: target }),
            }}
        />
    );
}
