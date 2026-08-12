import { Head } from '@inertiajs/react';

import { resolveOrganizationDisplay } from '@/lib/user-organization';

import type { User } from '@/types';
import type { RoleGroup } from '@/types/auth';
import type { SchoolWithPeriods } from '@/components/shared/users/school-user-period-fieldset';

import UserForm from '@/components/shared/users/user-form';
import UserContextDetails from '@/components/shared/users/user-context-details';
import UserOrganizationDetails from '@/components/shared/users/user-organization-details';
import SchoolUserPeriodFieldset, { useSchoolPeriodAssignment } from '@/components/shared/users/school-user-period-fieldset';

import { edit, index, show, update } from '@/routes/education-monitor/users';

type PageProps = {
    user: User;
    schools: SchoolWithPeriods[];
    groupedRoles: RoleGroup[];
};

export default function Edit({ user, schools, groupedRoles }: PageProps) {
    const isSchoolUser = user.scope.id === 'school';
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

    return (
        <>
            <Head title="تعديل بيانات المُستخدم" />

            <UserForm
                mode="edit"
                title="تعديل بيانات المُستخدم"
                cancelHref={show.url({ user })}
                form={update.form({ user })}
                groupedRoles={groupedRoles}
                initialRoleIds={user.role_ids ?? []}
                accountDefaults={{
                    name: user.name,
                    username: user.username,
                    email: user.email,
                }}
                context={(errors) => (
                    <>
                        <UserContextDetails
                            items={[
                                { label: 'النطاق', value: user.scope.name },
                            ]}
                        />

                        <UserOrganizationDetails
                            organization={organization}
                            isSchoolUser={isSchoolUser}
                        />

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
                    </>
                )}
            />
        </>
    );
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
