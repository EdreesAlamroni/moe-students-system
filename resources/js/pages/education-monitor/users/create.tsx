import React, { useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { usernameInputConstraints } from "@/lib/input-constraints";

import { useGroupedRolesSelection } from "@/hooks/use-grouped-roles-selection";

import type { EducationMonitor, EducationServicesOffice, Enum, School } from "@/types";
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
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";
import InputError from "@/components/ui/controls/input-error";

import ValidationErrors from "@/components/ui/alerts/validation-errors";

import GroupedRolesFieldset from "@/components/shared/users/grouped-roles-fieldset";

import { Button } from "@/components/ui/actions/button";
import { CreateButton } from "@/components/ui/actions/submit-button";

import { ReplyIcon } from "lucide-react";

import { create, index, store } from "@/routes/education-monitor/users";

type OrganizationOption = Pick<EducationServicesOffice | School, "id" | "name">;

type PageProps = {
    scope: Enum;
    creationLabel: string;
    monitor: Pick<EducationMonitor, "id" | "name">;
    offices: OrganizationOption[];
    schools: OrganizationOption[];
    groupedRoles: RoleGroup[];
};

export default function Create({
    scope,
    creationLabel,
    monitor,
    offices,
    schools,
    groupedRoles,
}: PageProps) {
    const isEducationServicesOffice = scope.id === "education_services_office";
    const isSchool = scope.id === "school";

    const [selectedOfficeId, setSelectedOfficeId] = useState<string>();
    const [selectedSchoolId, setSelectedSchoolId] = useState<string>();

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
                                                <DetailLabel>المُراقبة</DetailLabel>
                                                <DetailValue value={monitor.name} />
                                            </DetailField>

                                            {isEducationServicesOffice && (
                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="education_services_office_id"
                                                        hasError={!!errors.education_services_office_id}
                                                        required
                                                    >
                                                        مكتب الخدمات التعليمية
                                                    </Label>

                                                    {offices.length > 0 ? (
                                                        <Select
                                                            name="education_services_office_id"
                                                            value={selectedOfficeId}
                                                            onValueChange={setSelectedOfficeId}
                                                        >
                                                            <SelectTrigger
                                                                id="education_services_office_id"
                                                                hasError={!!errors.education_services_office_id}
                                                            >
                                                                <SelectValue placeholder="اختر مكتب الخدمات التعليمية" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {offices.map((office) => (
                                                                        <SelectItem
                                                                            key={office.id}
                                                                            value={office.id.toString()}
                                                                        >
                                                                            {office.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="education_services_office_id"
                                                            placeholder="لا توجد مكاتب خدمات تعليمية متاحة للاختيار"
                                                            aria-invalid={!!errors.education_services_office_id}
                                                        />
                                                    )}

                                                    <InputError message={errors.education_services_office_id} />
                                                </Field>
                                            )}

                                            {isSchool && (
                                                <Field className="col-span-full">
                                                    <Label
                                                        htmlFor="school_id"
                                                        hasError={!!errors.school_id}
                                                        required
                                                    >
                                                        المدرسة
                                                    </Label>

                                                    {schools.length > 0 ? (
                                                        <Select
                                                            name="school_id"
                                                            value={selectedSchoolId}
                                                            onValueChange={setSelectedSchoolId}
                                                        >
                                                            <SelectTrigger
                                                                id="school_id"
                                                                hasError={!!errors.school_id}
                                                            >
                                                                <SelectValue placeholder="اختر المدرسة" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {schools.map((school) => (
                                                                        <SelectItem
                                                                            key={school.id}
                                                                            value={school.id.toString()}
                                                                        >
                                                                            {school.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="school_id"
                                                            placeholder="لا توجد مدارس متاحة للاختيار"
                                                            aria-invalid={!!errors.school_id}
                                                        />
                                                    )}

                                                    <InputError message={errors.school_id} />
                                                </Field>
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
                                                    hasErrors={!!errors.name}
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
                                                    className="not-placeholder-shown:font-mono"
                                                    hasErrors={!!errors.username}
                                                    autoComplete="username"
                                                    required
                                                    {...usernameInputConstraints()}
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
                                                    hasErrors={!!errors.email}
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
                                                    hasErrors={!!errors.password}
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
                                                    hasErrors={!!errors.password_confirmation}
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
