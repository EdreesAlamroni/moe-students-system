import { Head } from '@inertiajs/react';

import type { EducationMonitor, Enum, Warehouse } from '@/types';
import type { RoleGroup } from '@/types/auth';

import UserForm from '@/components/shared/users/user-form';
import AdministrationUserOrganizationFields from '@/components/shared/users/administration-user-organization-fields';

import { index, create, store } from '@/routes/administration/users';

type PageProps = {
    scope: Enum;
    creationLabel: string;
    warehouses: Warehouse[];
    monitors: EducationMonitor[];
    groupedRoles: RoleGroup[];
};

export default function Create({
    scope,
    creationLabel,
    warehouses,
    monitors,
    groupedRoles,
}: PageProps) {
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
                    <AdministrationUserOrganizationFields
                        scope={scope}
                        warehouses={warehouses}
                        monitors={monitors}
                        errors={errors}
                    />
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
