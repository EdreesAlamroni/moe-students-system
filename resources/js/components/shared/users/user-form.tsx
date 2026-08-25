import type { ReactNode } from 'react';

import { Form, Link } from '@inertiajs/react';

import { useGroupedRolesSelection } from '@/hooks/use-grouped-roles-selection';

import type { RoleGroup } from '@/types/auth';

import MainContainer from '@/components/ui/structure/main-container';
import { Card, CardDescription, CardFormContent, CardFormFooter, CardHeader, CardTitle } from '@/components/ui/structure/card';
import { FormLayout } from '@/components/ui/structure/form-layout';

import Heading from '@/components/ui/display/heading';
import RequiredFieldsNote from '@/components/ui/display/required-fields-note';

import InputError from '@/components/ui/controls/input-error';

import ValidationErrors from '@/components/ui/alerts/validation-errors';

import GroupedRolesFieldset from '@/components/shared/users/grouped-roles-fieldset';
import UserAccountFields from '@/components/shared/users/user-account-fields';
import type { UserFormErrors, UserFormValues } from '@/components/shared/users/user-form-types';

import { Button } from '@/components/ui/actions/button';
import { CreateButton, UpdateButton } from '@/components/ui/actions/submit-button';

import { ReplyIcon } from 'lucide-react';

type FormOptions = Omit<React.ComponentProps<typeof Form<UserFormValues>>, 'children'>;

type UserFormProps = {
    mode: 'create' | 'edit';
    title: string;
    cancelHref: string;
    form: FormOptions;
    groupedRoles: RoleGroup[];
    initialRoleIds?: number[];
    accountDefaults?: {
        name?: string;
        username?: string;
        email?: string | null;
    };
    context: (errors: UserFormErrors) => ReactNode;
    hiddenFields?: ReactNode;
};

function UserFormSection({
    title,
    children,
}: {
    title: string;
    children: ReactNode;
}) {
    return (
        <section aria-label={title} className="col-span-full space-y-4">
            <Heading variant="small" title={title} />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {children}
            </div>
        </section>
    );
}

export default function UserForm({
    mode,
    title,
    cancelHref,
    form,
    groupedRoles,
    initialRoleIds = [],
    accountDefaults,
    context,
    hiddenFields,
}: UserFormProps) {
    const {
        selectedRoles,
        allRolesChecked,
        someRolesChecked,
        toggleRole,
        toggleAllRoles,
        isGroupAllChecked,
        isGroupSomeChecked,
        toggleGroupRoles,
    } = useGroupedRolesSelection(groupedRoles, initialRoleIds);

    return (
        <MainContainer>
            <Form<UserFormValues>
                {...form}
                disableWhileProcessing
                resetOnError={mode === 'create' ? ['password', 'password_confirmation'] : false}
            >
                {({ processing, errors }) => (
                    <FormLayout>
                        <ValidationErrors errors={errors} />

                        {hiddenFields}

                        <input type="hidden" name="roles" value={JSON.stringify(selectedRoles)} />

                        <section>
                            <Card>
                                <CardHeader>
                                    <CardTitle>{title}</CardTitle>
                                    <CardDescription>
                                        <RequiredFieldsNote />
                                    </CardDescription>
                                </CardHeader>

                                <CardFormContent>
                                    <div className="grid grid-cols-1 gap-6">
                                        <UserFormSection title="المؤسسة التعليمية">
                                            {context(errors)}
                                        </UserFormSection>

                                        <UserFormSection title="البيانات الشخصية">
                                            <UserAccountFields
                                                mode={mode}
                                                errors={errors}
                                                defaults={accountDefaults}
                                            />
                                        </UserFormSection>

                                        <UserFormSection title="الصلاحيات">
                                            <div className="col-span-full space-y-2">
                                                <GroupedRolesFieldset
                                                    groupedRoles={groupedRoles}
                                                    selectedRoles={selectedRoles}
                                                    allRolesChecked={allRolesChecked}
                                                    someRolesChecked={someRolesChecked}
                                                    onToggleAllRoles={toggleAllRoles}
                                                    onToggleRole={toggleRole}
                                                    isGroupAllChecked={isGroupAllChecked}
                                                    isGroupSomeChecked={isGroupSomeChecked}
                                                    onToggleGroupRoles={toggleGroupRoles}
                                                    hasError={!!errors.roles}
                                                />

                                                <InputError message={errors.roles} />
                                            </div>
                                        </UserFormSection>
                                    </div>
                                </CardFormContent>

                                <CardFormFooter>
                                    <Button
                                        variant="outline"
                                        className="flex items-center gap-x-2"
                                        asChild
                                    >
                                        <Link href={cancelHref}>
                                            <ReplyIcon />
                                            <span>إلغاء الأمر</span>
                                        </Link>
                                    </Button>

                                    {mode === 'create' ? (
                                        <CreateButton processing={processing} />
                                    ) : (
                                        <UpdateButton processing={processing} />
                                    )}
                                </CardFormFooter>
                            </Card>
                        </section>
                    </FormLayout>
                )}
            </Form>
        </MainContainer>
    );
}
