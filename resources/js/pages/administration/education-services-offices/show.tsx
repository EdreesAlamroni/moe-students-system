import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, EducationServicesOffice } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { StatCardsSection } from "@/components/ui/display/stat-card";
import { PhoneNumberLink, WhatsappLink } from "@/components/ui/display/smart-links";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { LocationShowMap } from "@/components/ui/maps/location-show-map";

import { NotepadTextIcon, SchoolIcon, SquarePenIcon, UsersIcon } from "lucide-react";

import { destroy, edit, index, show } from "@/routes/administration/education-services-offices";

type PageProps = {
    office: EducationServicesOffice;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ office, canAny, can }: PageProps) {
    return (
        <MainContainer>
            <Head title="عرض بيانات مكتب الخدمات التعليمية" />

            {canAny && (
                <ActionsSection>
                    {can.update && (
                        <Button
                            variant="outline"
                            asChild
                        >
                            <Link href={edit.url({ office: office })}>
                                <SquarePenIcon />
                                <span>تعديل بيانات مكتب الخدمات التعليمية</span>
                            </Link>
                        </Button>
                    )}

                    {can.delete && (
                        <ConfirmDeleteAction
                            title="حذف مكتب الخدمات التعليمية"
                            href={destroy.url({ office: office })}
                        />
                    )}
                </ActionsSection>
            )}

            <StatCardsSection
                items={[
                    { label: "المدارس", value: office.schools_count || 0, icon: SchoolIcon },
                    { label: "الطلاب", value: office.students_count || 0, icon: UsersIcon },
                ]}
                columns={2}
            />

            <section>
                <Card>
                    <CardHeader className="border-b">
                        <CardTitle>
                            <NotepadTextIcon />
                            <span>عرض بيانات مكتب الخدمات التعليمية</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-6">
                        <DetailFields columns={2}>
                            <DetailField>
                                <DetailLabel>اسم مكتب الخدمات التعليمية</DetailLabel>
                                <DetailValue value={office.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>المُراقبة</DetailLabel>
                                <DetailValue value={office.monitor?.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>رقم الهاتف</DetailLabel>
                                <DetailValue>
                                    <PhoneNumberLink value={office.phone_number} />
                                </DetailValue>
                            </DetailField>

                            <DetailField>
                                <DetailLabel>رقم هاتف الواتساب</DetailLabel>
                                <DetailValue>
                                    <WhatsappLink value={office.formatted_whatsapp_phone_number} />
                                </DetailValue>
                            </DetailField>

                            <DetailField className="col-span-full">
                                <DetailLabel>العنوان</DetailLabel>
                                <DetailValue value={office.address} />
                            </DetailField>
                        </DetailFields>

                        {office.has_coordinates && (
                            <>
                                <DetailFields columns={2}>
                                    <DetailField>
                                        <DetailLabel>خط العرض</DetailLabel>
                                        <DetailValue value={office.latitude} className="font-mono" />
                                    </DetailField>

                                    <DetailField>
                                        <DetailLabel>خط الطول</DetailLabel>
                                        <DetailValue value={office.longitude} className="font-mono" />
                                    </DetailField>
                                </DetailFields>

                                <DetailFields columns={1}>
                                    <DetailField className="col-span-full">
                                        <DetailLabel>الموقع على الخريطة</DetailLabel>
                                        <LocationShowMap
                                            latitude={office.latitude}
                                            longitude={office.longitude}
                                        />
                                    </DetailField>
                                </DetailFields>
                            </>
                        )}
                    </CardContent>
                </Card>
            </section>
        </MainContainer>
    )
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'مكاتب الخدمات التعليمية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات مكتب الخدمات التعليمية',
            href: show.url({ office: props.office }),
        },
    ],
});
