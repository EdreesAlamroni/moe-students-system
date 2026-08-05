import React from 'react'

import { Head } from "@inertiajs/react";

import type { EducationMonitor } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { StatCardsSection } from "@/components/ui/display/stat-card";
import { PhoneNumberLink, WhatsappLink } from "@/components/ui/display/smart-links";

import { LocationShowMap } from "@/components/ui/maps/location-show-map";

import { BuildingIcon, NotepadTextIcon, SchoolIcon, UsersIcon } from "lucide-react";

import { index, show } from "@/routes/warehouse/education-monitors";

type PageProps = {
    monitor: EducationMonitor;
}

export default function Show({ monitor }: PageProps) {
    return (
        <>
            <Head title="عرض بيانات المُراقبة" />

            <MainContainer showAcademicYearNotice>
                <StatCardsSection
                    items={[
                        { label: "مكاتب الخدمات التعليمية", value: monitor.offices_count || 0, icon: BuildingIcon },
                        { label: "المدارس", value: monitor.schools_count || 0, icon: SchoolIcon },
                        { label: "الطلاب", value: monitor.students_count || 0, icon: UsersIcon },
                    ]}
                    columns={3}
                />

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات المُراقبة</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <DetailFields columns={2}>
                                <DetailField>
                                    <DetailLabel>اسم المُراقبة</DetailLabel>
                                    <DetailValue value={monitor.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>البلدية</DetailLabel>
                                    <DetailValue value={monitor.municipal?.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>رقم الهاتف</DetailLabel>
                                    <DetailValue>
                                        <PhoneNumberLink value={monitor.phone_number} />
                                    </DetailValue>
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>رقم هاتف الواتساب</DetailLabel>
                                    <DetailValue>
                                        <WhatsappLink value={monitor.formatted_whatsapp_phone_number} />
                                    </DetailValue>
                                </DetailField>

                                <DetailField className="col-span-full">
                                    <DetailLabel>العنوان</DetailLabel>
                                    <DetailValue value={monitor.address} />
                                </DetailField>
                            </DetailFields>

                            {monitor.has_coordinates && (
                                <>
                                    <DetailFields columns={2}>
                                        <DetailField>
                                            <DetailLabel>خط العرض</DetailLabel>
                                            <DetailValue value={monitor.latitude} className="font-mono" />
                                        </DetailField>

                                        <DetailField>
                                            <DetailLabel>خط الطول</DetailLabel>
                                            <DetailValue value={monitor.longitude} className="font-mono" />
                                        </DetailField>
                                    </DetailFields>

                                    <DetailFields columns={1}>
                                        <DetailField className="col-span-full">
                                            <DetailLabel>الموقع على الخريطة</DetailLabel>
                                            <LocationShowMap
                                                latitude={monitor.latitude}
                                                longitude={monitor.longitude}
                                            />
                                        </DetailField>
                                    </DetailFields>
                                </>
                            )}
                        </CardContent>
                    </Card>
                </section>
            </MainContainer>
        </>
    )
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المُراقبات',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المُراقبة',
            href: show.url({ monitor: props.monitor }),
        },
    ],
});
