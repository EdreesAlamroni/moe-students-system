import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, EducationMonitor, EducationServicesOffice, Paginated } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";
import { Table, TableBody, TableCell, TableCellActions, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { StatCardsSection } from "@/components/ui/display/stat-card";
import { PhoneNumberLink, WhatsappLink } from "@/components/ui/display/smart-links";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import { LocationShowMap } from "@/components/ui/maps/location-show-map";

import { BuildingIcon, ListIcon, NotepadTextIcon, SchoolIcon, SquarePenIcon, UsersIcon } from "lucide-react";

import { index, show, edit, destroy } from "@/routes/administration/education-monitors";
import { show as showOffice } from "@/routes/administration/education-services-offices";

type OfficeProps = EducationServicesOffice & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    monitor: EducationMonitor;
    offices: Paginated<OfficeProps>;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ monitor, offices, canAny, can }: PageProps) {
    const { data: officesData, links: officesLinks, ...officesMeta } = offices;

    const hasPagination = officesData.length > 0 && officesMeta.last_page > 1;

    return (
        <MainContainer>
            <Head title="عرض بيانات المُراقبة" />

            {canAny && (
                <ActionsSection>
                    {can.update && (
                        <Button
                            variant="outline"
                            asChild
                        >
                            <Link href={edit.url({ monitor: monitor })}>
                                <SquarePenIcon />
                                <span>تعديل بيانات المُراقبة</span>
                            </Link>
                        </Button>
                    )}

                    {can.delete && (
                        <ConfirmDeleteAction
                            title="حذف المُراقبة"
                            href={destroy.url({ monitor: monitor })}
                        />
                    )}
                </ActionsSection>
            )}

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

            <section>
                <Card>
                    <CardHeader className="border-b">
                        <CardTitle>
                            <ListIcon />
                            <div className="flex items-center gap-x-1.5">
                                <span>مكاتب الخدمات التعليمية</span>
                                <span className="font-mono">({officesMeta.total})</span>
                            </div>
                        </CardTitle>
                    </CardHeader>
                    {officesData.length > 0 ? (
                        <CardTableContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                        <TableHead scope="col">اسم مكتب الخدمات التعليمية</TableHead>
                                        <TableHead scope="col" className="text-center">عدد المدارس</TableHead>
                                        <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        <TableHead scope="col" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {officesData.map((office: OfficeProps, index: number) => (
                                        <TableRow key={office.uuid}>
                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                            <TableCell>{office.name}</TableCell>
                                            <TableCell className="text-center">
                                                <TableCellNullableValue className="font-mono" value={office.schools_count} fallback={0} />
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <TableCellNullableValue className="font-mono" value={office.students_count} fallback={0} />
                                            </TableCell>
                                            <TableCellActions>
                                                {office.canAny && (
                                                    <>
                                                        {office.can.view && (
                                                            <ViewDetailsLink
                                                                href={showOffice.url({ office: office })}
                                                            />
                                                        )}
                                                    </>
                                                )}
                                            </TableCellActions>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardTableContent>
                    ) : (
                        <CardContent>
                            <EmptyState />
                        </CardContent>
                    )}
                    {hasPagination && (
                        <CardFooter className="border-t">
                            <Paginator
                                links={officesLinks}
                                meta={officesMeta}
                            />
                        </CardFooter>
                    )}
                </Card>
            </section>
        </MainContainer>
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
