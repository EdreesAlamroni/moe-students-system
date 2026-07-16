import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { useGroupedRolesSelection } from "@/hooks/use-grouped-roles-selection";
import { resolveOrganizationDisplay } from "@/lib/user-organization";

import type { Enum } from "@/types";
import type { RoleGroup, UserOrganizationContext } from "@/types/auth";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";
import { Separator } from "@/components/ui/structure/separator";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";
import { DetailField } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import GroupedRolesFieldset from "@/components/shared/users/grouped-roles-fieldset";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, show, edit, update } from "@/routes/administration/users";

type EditableUser = {
    id: number;
    uuid: string;
    name: string;
    username: string;
    email: string | null;
    scope: Enum;
    organization: UserOrganizationContext | null;
    role_ids: number[];
};

type PageProps = {
    user: EditableUser;
    groupedRoles: RoleGroup[];
};

export default function Edit({ user, groupedRoles }: PageProps) {
    const organization = resolveOrganizationDisplay(user.organization);

    const {
        selectedRoles,
        allRolesChecked,
        someRolesChecked,
        toggleRole,
        toggleAllRoles,
        isGroupAllChecked,
        isGroupSomeChecked,
        toggleGroupRoles,
    } = useGroupedRolesSelection(groupedRoles, user.role_ids ?? []);

    return (
        <>
            <Head title="تعديل بيانات المُستخدم" />

            <MainContainer>
                <Form
                    {...update.form({ user: user })}
                    disableWhileProcessing
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="roles" value={JSON.stringify(selectedRoles)} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>تعديل بيانات المُستخدم</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>النطاق</DetailLabel>
                                                <DetailValue value={user.scope.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>اسم المُستخدم</DetailLabel>
                                                <DetailValue value={user.username} className="font-mono" />
                                            </DetailField>

                                            {organization && (
                                                <>
                                                    {organization.parent && (
                                                        <DetailField>
                                                            <DetailLabel>{organization.parent.label}</DetailLabel>
                                                            <DetailValue value={organization.parent.name} />
                                                        </DetailField>
                                                    )}

                                                    <DetailField className={organization.parent ? undefined : "col-span-full"}>
                                                        <DetailLabel>{organization.label}</DetailLabel>
                                                        <DetailValue value={organization.name} />
                                                    </DetailField>
                                                </>
                                            )}

                                            <Separator className="col-span-full" />

                                            <Field>
                                                <Label
                                                    htmlFor="name"
                                                    hasError={!!errors.name}
                                                    required
                                                >
                                                    الاسم
                                                </Label>

                                                <Input
                                                    id="name"
                                                    type="text"
                                                    name="name"
                                                    defaultValue={user.name}
                                                    hasErrors={!!errors.name}
                                                    autoComplete="name"
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="email"
                                                    hasError={!!errors.email}
                                                >
                                                    البريد الإلكتروني
                                                </Label>

                                                <Input
                                                    id="email"
                                                    type="email"
                                                    name="email"
                                                    defaultValue={user.email ?? ""}
                                                    hasErrors={!!errors.email}
                                                    autoComplete="email"
                                                />

                                                <InputError message={errors.email} />
                                            </Field>

                                            <Separator className="col-span-full" />

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
                                        </div>
                                    </CardFormContent>

                                    <CardFooter className="justify-end gap-x-4 border-t">
                                        <Button
                                            variant="outline"
                                            className="flex items-center gap-x-2"
                                            asChild
                                        >
                                            <Link href={show.url({ user: user })}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <UpdateButton processing={processing} />
                                    </CardFooter>
                                </Card>
                            </section>
                        </FormLayout>
                    )}
                </Form>
            </MainContainer>
        </>
    )
}

Edit.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المُستخدمين',
            href: index.url(),
        },
        {
            title: 'تعديل بيانات المُستخدم',
            href: edit.url({ user: props.user }),
        },
    ],
});
