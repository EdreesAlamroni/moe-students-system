import { Head } from '@inertiajs/react';

import type { EducationServicesOffice, Enum } from '@/types';
import type { RoleGroup } from '@/types/auth';
import type { SchoolWithPeriods } from '@/components/shared/users/school-user-period-fieldset';

import UserForm from '@/components/shared/users/user-form';
import UserContextDetails from '@/components/shared/users/user-context-details';
import SchoolUserPeriodFieldset, { useSchoolPeriodAssignment } from '@/components/shared/users/school-user-period-fieldset';

import { index, create, store } from '@/routes/education-services-office/users';

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
    const isSchool = scope.id === 'school';

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
                                { label: 'مكتب الخدمات التعليمية', value: office.name },
                            ]}
                        />

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
