import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { useGroupedRolesSelection } from "@/hooks/use-grouped-roles-selection";

import type { Enum } from "@/types";
import type { RoleGroup } from "@/types/auth";

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
import PasswordInput from "@/components/ui/controls/password-input";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import GroupedRolesFieldset from "@/components/shared/users/grouped-roles-fieldset";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { create, index, store } from "@/routes/warehouse/users";

type PageProps = {
    scope: Enum;
    warehouse: {
        id: number;
        name: string;
    } | null;
    groupedRoles: RoleGroup[];
};

export default function Create({
    scope,
    warehouse,
    groupedRoles,
}: PageProps) {
    const {
        selectedRoles,
        allRolesChecked,
        someRolesChecked,
        toggleRole,
        toggleAllRoles,
        isGroupAllChecked,
        isGroupSomeChecked,
        toggleGroupRoles,
    } = useGroupedRolesSelection(groupedRoles);

    return (
        <>
            <Head title="إضافة مُستخدم جديد" />

            <MainContainer>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                    resetOnError={["password", "password_confirmation"]}
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="roles" value={JSON.stringify(selectedRoles)} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>إضافة مُستخدم جديد</CardTitle>
                                        <CardDescription>
                                            <RequiredFieldsNote />
                                        </CardDescription>
                                    </CardHeader>

                                    <CardFormContent>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <DetailField>
                                                <DetailLabel>النطاق</DetailLabel>
                                                <DetailValue value={scope.name} />
                                            </DetailField>

                                            <DetailField>
                                                <DetailLabel>المخزن</DetailLabel>
                                                <DetailValue value={warehouse?.name} />
                                            </DetailField>

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
                                                    hasError={!!errors.name}
                                                    autoComplete="name"
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <Field>
                                                <Label
                                                    htmlFor="username"
                                                    hasError={!!errors.username}
                                                    required
                                                >
                                                    اسم المُستخدم
                                                </Label>

                                                <Input
                                                    id="username"
                                                    type="text"
                                                    name="username"
                                                    hasError={!!errors.username}
                                                    autoComplete="username"
                                                    required
                                                />

                                                <InputError message={errors.username} />
                                            </Field>

                                            <Field className="md:col-span-2">
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
                                                    hasError={!!errors.email}
                                                    autoComplete="email"
                                                />

                                                <InputError message={errors.email} />
                                            </Field>

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
                                            <Link href={index.url()}>
                                                <ReplyIcon />
                                                <span>إلغاء الأمر</span>
                                            </Link>
                                        </Button>

                                        <CreateButton
                                            processing={processing}
                                        />
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

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'المُستخدمين',
            href: index.url(),
        },
        {
            title: 'إضافة مُستخدم جديد',
            href: create.url(),
        },
    ],
});
