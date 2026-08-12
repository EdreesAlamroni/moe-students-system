import { Head } from '@inertiajs/react';

import { resolveOrganizationDisplay } from '@/lib/user-organization';

import type { User } from '@/types';
import type { RoleGroup } from '@/types/auth';

import UserForm from '@/components/shared/users/user-form';
import UserContextDetails from '@/components/shared/users/user-context-details';
import UserOrganizationDetails from '@/components/shared/users/user-organization-details';

import { edit, index, show, update } from '@/routes/warehouse/users';

type PageProps = {
    user: User;
    groupedRoles: RoleGroup[];
};

export default function Edit({ user, groupedRoles }: PageProps) {
    const organization = resolveOrganizationDisplay(user.organization);

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
                context={() => (
                    <>
                        <UserContextDetails
                            items={[
                                { label: 'النطاق', value: user.scope.name },
                            ]}
                        />

                        <UserOrganizationDetails
                            organization={organization}
                            mode="simple"
                        />
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
