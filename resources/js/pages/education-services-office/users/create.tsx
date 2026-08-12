import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { useGroupedRolesSelection } from "@/hooks/use-grouped-roles-selection";
import type { EducationServicesOffice, Enum } from "@/types";
import type { RoleGroup } from "@/types/auth";
import type { SchoolWithPeriods } from "@/components/shared/users/school-user-period-fieldset";

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
import SchoolUserPeriodFieldset, { useSchoolPeriodAssignment } from "@/components/shared/users/school-user-period-fieldset";
import UsernameField from "@/components/shared/users/username-field";
import EmailField from "@/components/shared/users/email-field";
import PasswordField from "@/components/shared/users/password-field";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { index, create, store } from "@/routes/education-services-office/users";

type PageProps = {
    scope: Enum;
    creationLabel: string;
    office: EducationServicesOffice;
    schools: SchoolWithPeriods[];
    groupedRoles: RoleGroup[];
};

export default function Create({
    scope,
    creationLabel,
    office,
    schools,
    groupedRoles,
}: PageProps) {
    const isSchool = scope.id === "school";

    const {
        selectedSchoolId,
        selectedPeriodIds,
        handleSchoolChange,
        togglePeriod,
    } = useSchoolPeriodAssignment({ schools });

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


    const pageTitle = `إضافة ${creationLabel}`;

    return (
        <>
            <Head title={pageTitle} />

            <MainContainer>
                <Form
                    {...store.form()}
                    disableWhileProcessing
                    resetOnError={["password", "password_confirmation"]}
                >
                    {({ processing, errors }) => (
                        <FormLayout>
                            <ValidationErrors errors={errors} />

                            <input type="hidden" name="scope" value={scope.id} />
                            <input type="hidden" name="roles" value={JSON.stringify(selectedRoles)} />

                            <section>
                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>{pageTitle}</CardTitle>
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
                                                <DetailLabel>مكتب الخدمات التعليمية</DetailLabel>
                                                <DetailValue value={office.name} />
                                            </DetailField>

                                            {isSchool && (
                                                <SchoolUserPeriodFieldset
                                                    schools={schools}
                                                    selectedSchoolId={selectedSchoolId}
                                                    selectedPeriodIds={selectedPeriodIds}
                                                    onSchoolChange={handleSchoolChange}
                                                    onPeriodToggle={togglePeriod}
                                                    errors={errors}
                                                    className="col-span-full"
                                                />
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
                                                    hasError={!!errors.name}
                                                    autoComplete="name"
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <UsernameField
                                                error={errors.username}
                                            />

                                            <EmailField
                                                error={errors.email}
                                                className="col-span-full"
                                            />

                                            <PasswordField
                                                passwordError={errors.password}
                                                passwordConfirmationError={errors.password_confirmation}
                                            />

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

                                        <CreateButton processing={processing} />
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

Create.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المُستخدمين',
            href: index.url(),
        },
        {
            title: `إضافة ${props.creationLabel}`,
            href: create.url(props.scope.id),
        },
    ],
});
