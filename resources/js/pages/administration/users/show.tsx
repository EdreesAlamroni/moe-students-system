import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, ModelState, User } from "@/types";
import type { RoleGroup } from "@/types/auth";

import { resolveOrganizationDisplay } from "@/lib/user-organization";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";
import { StatePill } from "@/components/ui/display/state";

import GroupedRolesShowCard from "@/components/shared/users/grouped-roles-show-card";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { NotepadTextIcon, SquarePenIcon } from "lucide-react";

import { destroy, edit, index, show } from "@/routes/administration/users";

type PageProps = {
    user: User;
    roles: RoleGroup[];
    availableStates: ModelState[];
    availableRequestStates: ModelState[];
    isRequestPending: boolean;
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({ user, roles, canAny, can }: PageProps) {
    const organization = resolveOrganizationDisplay(user.organization);

    return (
        <MainContainer>
            <Head title="عرض بيانات المُستخدم" />

            {canAny && (
                <ActionsSection>
                    {can.update && (
                        <Button variant="outline" asChild>
                            <Link href={edit.url({ user })}>
                                <SquarePenIcon />
                                <span>تعديل بيانات المُستخدم</span>
                            </Link>
                        </Button>
                    )}

                    {can.delete && (
                        <ConfirmDeleteAction
                            title="حذف المُستخدم"
                            href={destroy.url({ user })}
                        />
                    )}
                </ActionsSection>
            )}

            <section>
                <Card>
                    <CardHeader className="border-b">
                        <CardTitle>
                            <NotepadTextIcon />
                            <span>عرض بيانات المُستخدم</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-6">
                        <DetailFields columns={2}>
                            <DetailField>
                                <DetailLabel>الاسم</DetailLabel>
                                <DetailValue value={user.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>اسم المُستخدم</DetailLabel>
                                <DetailValue value={user.username} className="font-mono" />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>البريد الإلكتروني</DetailLabel>
                                <DetailValue value={user.email} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>النطاق</DetailLabel>
                                <DetailValue value={user.scope.name} />
                            </DetailField>

                            {organization && (
                                <>
                                    {organization.parent && (
                                        <DetailField>
                                            <DetailLabel>{organization.parent.label}</DetailLabel>
                                            <DetailValue value={organization.parent.name} />
                                        </DetailField>
                                    )}

                                    <DetailField className={organization.parent ? undefined : "col-span-full"}>
                                        <DetailLabel>{organization.label}</DetailLabel>
                                        <DetailValue value={organization.name} />
                                    </DetailField>
                                </>
                            )}

                            <DetailField>
                                <DetailLabel>حالة الحساب</DetailLabel>
                                <DetailValue variant="plain">
                                    <StatePill state={user.state} />
                                </DetailValue>
                            </DetailField>

                            <DetailField>
                                <DetailLabel>حالة الطلب</DetailLabel>
                                <DetailValue variant="plain">
                                    <StatePill state={user.request_state} />
                                </DetailValue>
                            </DetailField>
                        </DetailFields>
                    </CardContent>
                </Card>
            </section>

            <GroupedRolesShowCard roles={roles} />
        </MainContainer>
    )
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المُستخدمين',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المُستخدم',
            href: show.url({ user: props.user }),
        },
    ],
});
