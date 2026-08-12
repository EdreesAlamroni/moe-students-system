import { Head } from '@inertiajs/react';

import type { Enum } from '@/types';
import type { RoleGroup } from '@/types/auth';

import UserForm from '@/components/shared/users/user-form';
import UserContextDetails from '@/components/shared/users/user-context-details';

import { create, index, store } from '@/routes/warehouse/users';

type PageProps = {
    scope: Enum;
    warehouse: {
        id: number;
        name: string;
    } | null;
    groupedRoles: RoleGroup[];
};

export default function Create({
    scope,
    warehouse,
    groupedRoles,
}: PageProps) {
    return (
        <>
            <Head title="إضافة مُستخدم جديد" />

            <UserForm
                mode="create"
                title="إضافة مُستخدم جديد"
                cancelHref={index.url()}
                form={store.form()}
                groupedRoles={groupedRoles}
                context={() => (
                    <UserContextDetails
                        items={[
                            { label: 'النطاق', value: scope.name },
                            { label: 'المخزن', value: warehouse?.name },
                        ]}
                    />
                )}
            />
        </>
    );
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'المُستخدمين',
            href: index.url(),
        },
        {
            title: 'إضافة مُستخدم جديد',
            href: create.url(),
        },
    ],
});
