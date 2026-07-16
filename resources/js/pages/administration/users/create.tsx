import React, { useMemo, useState } from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import { useGroupedRolesSelection } from "@/hooks/use-grouped-roles-selection";

import type { EducationMonitor, EducationServicesOffice, Enum, School, Warehouse } from "@/types";
import type { RoleGroup } from "@/types/auth";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardDescription, CardFooter, CardFormContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { FormLayout } from "@/components/ui/structure/form-layout";
import { Separator } from "@/components/ui/structure/separator";

import RequiredFieldsNote from "@/components/ui/display/required-fields-note";

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

import { create, index, store } from "@/routes/administration/users";

type OrganizationOption = Pick<Warehouse | EducationMonitor | EducationServicesOffice | School, "id" | "name">;

type MonitorWithOffices = Pick<EducationMonitor, "id" | "name"> & {
    offices: OrganizationOption[];
};

type MonitorWithSchools = Pick<EducationMonitor, "id" | "name"> & {
    schools: OrganizationOption[];
};

type PageProps = {
    scope: Enum;
    creationLabel: string;
    warehouses: OrganizationOption[];
    monitors: OrganizationOption[] | MonitorWithOffices[] | MonitorWithSchools[];
    groupedRoles: RoleGroup[];
};

export default function Create({
    scope,
    creationLabel,
    warehouses,
    monitors,
    groupedRoles,
}: PageProps) {
    const isWarehouse = scope.id === "warehouse";
    const isEducationMonitor = scope.id === "education_monitor";
    const isEducationServicesOffice = scope.id === "education_services_office";
    const isSchool = scope.id === "school";
    const needsMonitor = isEducationMonitor || isEducationServicesOffice || isSchool;

    const [selectedWarehouseId, setSelectedWarehouseId] = useState<string>();
    const [selectedMonitorId, setSelectedMonitorId] = useState<string>();
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

    const availableOffices = useMemo(() => {
        if (!isEducationServicesOffice || !selectedMonitorId) {
            return [];
        }

        const monitor = (monitors as MonitorWithOffices[]).find(
            (item) => item.id.toString() === selectedMonitorId,
        );

        return monitor?.offices ?? [];
    }, [isEducationServicesOffice, monitors, selectedMonitorId]);

    const availableSchools = useMemo(() => {
        if (!isSchool || !selectedMonitorId) {
            return [];
        }

        const monitor = (monitors as MonitorWithSchools[]).find(
            (item) => item.id.toString() === selectedMonitorId,
        );

        return monitor?.schools ?? [];
    }, [isSchool, monitors, selectedMonitorId]);

    const handleMonitorChange = (value: string) => {
        setSelectedMonitorId(value);
        setSelectedOfficeId(undefined);
        setSelectedSchoolId(undefined);
    };

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
                                            <Field>
                                                <Label htmlFor="scope_display" required>
                                                    النطاق
                                                </Label>

                                                <Input
                                                    id="scope_display"
                                                    type="text"
                                                    value={scope.name}
                                                    disabled
                                                    autoComplete="off"
                                                />
                                            </Field>

                                            {isWarehouse && (
                                                <Field>
                                                    <Label
                                                        htmlFor="warehouse_id"
                                                        hasError={!!errors.warehouse_id}
                                                        required
                                                    >
                                                        المخزن
                                                    </Label>

                                                    {warehouses.length > 0 ? (
                                                        <Select
                                                            name="warehouse_id"
                                                            value={selectedWarehouseId}
                                                            onValueChange={setSelectedWarehouseId}
                                                        >
                                                            <SelectTrigger
                                                                id="warehouse_id"
                                                                hasError={!!errors.warehouse_id}
                                                            >
                                                                <SelectValue placeholder="اختر المخزن" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {warehouses.map((warehouse) => (
                                                                        <SelectItem
                                                                            key={warehouse.id}
                                                                            value={warehouse.id.toString()}
                                                                        >
                                                                            {warehouse.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="warehouse_id"
                                                            placeholder="لا توجد مخازن متاحة للاختيار"
                                                            aria-invalid={!!errors.warehouse_id}
                                                        />
                                                    )}

                                                    <InputError message={errors.warehouse_id} />
                                                </Field>
                                            )}

                                            {needsMonitor && (
                                                <Field>
                                                    <Label
                                                        htmlFor="education_monitor_id"
                                                        hasError={!!errors.education_monitor_id}
                                                        required
                                                    >
                                                        المُراقبة
                                                    </Label>

                                                    {monitors.length > 0 ? (
                                                        <Select
                                                            name="education_monitor_id"
                                                            value={selectedMonitorId}
                                                            onValueChange={handleMonitorChange}
                                                        >
                                                            <SelectTrigger
                                                                id="education_monitor_id"
                                                                hasError={!!errors.education_monitor_id}
                                                            >
                                                                <SelectValue placeholder="اختر المُراقبة" />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectGroup>
                                                                    {monitors.map((monitor) => (
                                                                        <SelectItem
                                                                            key={monitor.id}
                                                                            value={monitor.id.toString()}
                                                                        >
                                                                            {monitor.name}
                                                                        </SelectItem>
                                                                    ))}
                                                                </SelectGroup>
                                                            </SelectContent>
                                                        </Select>
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="education_monitor_id"
                                                            placeholder="لا توجد مُراقبات متاحة للاختيار"
                                                            aria-invalid={!!errors.education_monitor_id}
                                                        />
                                                    )}

                                                    <InputError message={errors.education_monitor_id} />
                                                </Field>
                                            )}

                                            {isEducationServicesOffice && (
                                                <Field>
                                                    <Label
                                                        htmlFor="education_services_office_id"
                                                        hasError={!!errors.education_services_office_id}
                                                        required
                                                    >
                                                        مكتب الخدمات التعليمية
                                                    </Label>

                                                    {selectedMonitorId ? (
                                                        availableOffices.length > 0 ? (
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
                                                                        {availableOffices.map((office) => (
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
                                                        )
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="education_services_office_id"
                                                            placeholder="يرجى اختيار المُراقبة أولاً"
                                                            aria-invalid={!!errors.education_services_office_id}
                                                        />
                                                    )}

                                                    <InputError message={errors.education_services_office_id} />
                                                </Field>
                                            )}

                                            {isSchool && (
                                                <Field>
                                                    <Label
                                                        htmlFor="school_id"
                                                        hasError={!!errors.school_id}
                                                        required
                                                    >
                                                        المدرسة
                                                    </Label>

                                                    {selectedMonitorId ? (
                                                        availableSchools.length > 0 ? (
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
                                                                        {availableSchools.map((school) => (
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
                                                        )
                                                    ) : (
                                                        <EmptyOptionsInput
                                                            id="school_id"
                                                            placeholder="يرجى اختيار المُراقبة أولاً"
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
                                                    hasErrors={!!errors.username}
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
