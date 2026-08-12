import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { useGroupedRolesSelection } from "@/hooks/use-grouped-roles-selection";
import { resolveOrganizationDisplay } from "@/lib/user-organization";

import type { User } from "@/types";
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
import EmailField from "@/components/shared/users/email-field";

import { Button } from "@/components/ui/actions/button";
import { UpdateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { edit, index, show, update } from "@/routes/education-monitor/users";

type PageProps = {
    user: User;
    schools: SchoolWithPeriods[];
    groupedRoles: RoleGroup[];
};

export default function Edit({ user, schools, groupedRoles }: PageProps) {
    const isSchoolUser = user.scope.id === "school";
    const organization = resolveOrganizationDisplay(user.organization);

    const {
        selectedSchoolId,
        selectedPeriodIds,
        handleSchoolChange,
        togglePeriod,
    } = useSchoolPeriodAssignment({
        schools,
        initialSchoolId: user.school_id,
        initialPeriodIds: user.school_period_ids ?? [],
    });

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

                                            {organization && !isSchoolUser && (
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

                                            {isSchoolUser && organization?.parent && (
                                                <DetailField className="col-span-full">
                                                    <DetailLabel>{organization.parent.label}</DetailLabel>
                                                    <DetailValue value={organization.parent.name} />
                                                </DetailField>
                                            )}

                                            {isSchoolUser && (
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
                                                    className="not-placeholder-shown:font-mono"
                                                    defaultValue={user.name}
                                                    hasError={!!errors.name}
                                                    autoComplete="name"
                                                    required
                                                />

                                                <InputError message={errors.name} />
                                            </Field>

                                            <EmailField
                                                error={errors.email}
                                                defaultValue={user.email ?? ""}
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
