import { Head } from '@inertiajs/react';

import type { EducationServicesOffice, Enum, GradeLevel } from '@/types';

import { CreateSchoolForm } from '@/components/shared/schools/create-school-form';
import { EducationMonitorOrganizationFields } from '@/components/shared/schools/education-monitor-organization-fields';

import { index, create, store } from '@/routes/education-monitor/schools';

type PageProps = {
    offices: Pick<EducationServicesOffice, 'id' | 'name'>[];
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
    offices,
    ...formProps
}: PageProps) {
    return (
        <>
            <Head title="إضافة مدرسة جديدة" />

            <CreateSchoolForm
                form={store.form()}
                indexUrl={index.url()}
                organizationFields={(errors) => (
                    <EducationMonitorOrganizationFields
                        offices={offices}
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
