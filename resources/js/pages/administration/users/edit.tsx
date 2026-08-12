import React from 'react';

import { Head } from '@inertiajs/react';

import { resolveOrganizationDisplay } from '@/lib/user-organization';

import type { EducationMonitor, User } from '@/types';
import type { RoleGroup } from '@/types/auth';
import type { SchoolWithPeriods } from '@/components/shared/users/school-user-period-fieldset';

import UserForm from '@/components/shared/users/user-form';
import UserContextDetails from '@/components/shared/users/user-context-details';
import UserOrganizationDetails from '@/components/shared/users/user-organization-details';
import SchoolUserPeriodFieldset, { useSchoolPeriodAssignment } from '@/components/shared/users/school-user-period-fieldset';

import { index, show, edit, update } from '@/routes/administration/users';

type PageProps = {
    user: User;
    monitors: EducationMonitor[];
    groupedRoles: RoleGroup[];
};

export default function Edit({ user, monitors, groupedRoles }: PageProps) {
    const isSchoolUser = user.scope.id === 'school';
    const isWarehouse = user.scope.id === 'warehouse';
    const isEducationMonitor = user.scope.id === 'education_monitor';
    const isSchool = user.scope.id === 'school';
    const organization = resolveOrganizationDisplay(user.organization);

    const availableSchools = React.useMemo((): SchoolWithPeriods[] => {
        if (!isSchoolUser) {
            return [];
        }

        for (const monitor of monitors) {
            const school = (monitor.schools ?? []).find(
                (item) => item.id === user.school_id,
            );

            if (school) {
                return monitor.schools as SchoolWithPeriods[];
            }
        }

        return monitors.flatMap((monitor) => (monitor.schools ?? []) as SchoolWithPeriods[]);
    }, [isSchoolUser, monitors, user.school_id]);

    const {
        selectedSchoolId,
        selectedPeriodIds,
        handleSchoolChange,
        togglePeriod,
    } = useSchoolPeriodAssignment({
        schools: availableSchools,
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
                                {
                                    label: 'النطاق',
                                    value: user.scope.name,
                                    className: isWarehouse || isEducationMonitor || isSchool ? undefined : 'col-span-full',
                                },
                            ]}
                        />

                        <UserOrganizationDetails
                            organization={organization}
                            isSchoolUser={isSchoolUser}
                        />

                        {isSchoolUser && (
                            <SchoolUserPeriodFieldset
                                schools={availableSchools}
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
