import React from 'react';

import { Head } from '@inertiajs/react';

import type { Enum, GradeLevel } from '@/types';

import { CreateSchoolForm } from '@/components/shared/schools/create-school-form';

import { index, create, store } from '@/routes/education-services-office/schools';

type PageProps = {
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

export default function Create(formProps: PageProps) {
    return (
        <>
            <Head title="إضافة مدرسة جديدة" />

            <CreateSchoolForm
                form={store.form()}
                indexUrl={index.url()}
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
