import React from 'react';

import { Head } from '@inertiajs/react';

import type { EducationMonitor, EducationServicesOffice, Enum } from '@/types';
import type { RoleGroup } from '@/types/auth';
import type { SchoolWithPeriods } from '@/components/shared/users/school-user-period-fieldset';

import UserForm from '@/components/shared/users/user-form';
import UserContextDetails from '@/components/shared/users/user-context-details';
import SchoolUserPeriodFieldset, { useSchoolPeriodAssignment } from '@/components/shared/users/school-user-period-fieldset';

import Field from '@/components/ui/controls/field';
import { Label } from '@/components/ui/controls/label';
import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';
import InputError from '@/components/ui/controls/input-error';

import { create, index, store } from '@/routes/education-monitor/users';

type PageProps = {
    scope: Enum;
    creationLabel: string;
    monitor: EducationMonitor;
    offices: EducationServicesOffice[];
    schools: SchoolWithPeriods[];
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
    const isEducationServicesOffice = scope.id === 'education_services_office';
    const isSchool = scope.id === 'school';

    const [selectedOfficeId, setSelectedOfficeId] = React.useState<string>();

    const {
        selectedSchoolId,
        selectedPeriodIds,
        handleSchoolChange,
        togglePeriod,
    } = useSchoolPeriodAssignment({ schools });

    const pageTitle = `إضافة ${creationLabel}`;

    return (
        <>
            <Head title={pageTitle} />

            <UserForm
                mode="create"
                title={pageTitle}
                cancelHref={index.url()}
                form={store.form()}
                groupedRoles={groupedRoles}
                hiddenFields={<input type="hidden" name="scope" value={scope.id} />}
                context={(errors) => (
                    <>
                        <UserContextDetails
                            items={[
                                { label: 'النطاق', value: scope.name },
                                { label: 'المُراقبة', value: monitor.name },
                            ]}
                        />

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
                    </>
                )}
            />
        </>
    );
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
