import React from 'react';

import { Head } from '@inertiajs/react';

import type { EducationMonitor, Enum, GradeLevel } from '@/types';

import { AdministrationOrganizationFields } from '@/components/shared/schools/administration-organization-fields';
import { CreateSchoolForm } from '@/components/shared/schools/create-school-form';

import { index, create, store } from '@/routes/administration/schools';

type PageProps = {
    monitors: EducationMonitor[];
    types: Enum[];
    academicPeriods: Enum[];
    studentsGender: Enum[];
    branchTypes: Enum[];
    buildingTypes: Enum[];
    educationalStages: Enum[];
    gradeLevels: GradeLevel[];
    schoolPrivateType: string;
    schoolDualAcademicPeriod: string;
};

export default function Create({
    monitors,
    ...formProps
}: PageProps) {
    return (
        <>
            <Head title="إضافة مدرسة جديدة" />

            <CreateSchoolForm
                form={store.form()}
                indexUrl={index.url()}
                organizationFields={(errors) => (
                    <AdministrationOrganizationFields
                        monitors={monitors}
                        errors={errors}
                    />
                )}
                {...formProps}
            />
        </>
    );
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'المدارس',
            href: index.url(),
        },
        {
            title: 'إضافة مدرسة جديدة',
            href: create.url(),
        },
    ],
});
