import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, EducationMonitor, Paginated, Warehouse } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { StatCardsSection } from "@/components/ui/display/stat-card";
import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";
import { Table, TableBody, TableCell, TableCellActions, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import { LocationShowMap } from "@/components/ui/maps/location-show-map";

import { LandmarkIcon, ListIcon, NotepadTextIcon, SchoolIcon, SquarePenIcon } from "lucide-react";

import { index, show, edit, destroy } from "@/routes/administration/warehouses";
import { show as showMonitor } from "@/routes/administration/education-monitors";


type MonitorProps = EducationMonitor & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    warehouse: Warehouse;
    monitors: Paginated<MonitorProps>;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ warehouse, monitors, canAny, can }: PageProps) {
    const { data: monitorsData, links: monitorsLinks, ...monitorsMeta } = monitors;

    const hasPagination = monitorsData.length > 0 && monitorsMeta.last_page > 1;

    return (
        <>
            <Head title="عرض بيانات المخزن" />

            <MainContainer showAcademicYearNotice>
                {canAny && (
                    <ActionsSection>
                        {can.update && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href={edit.url({ warehouse: warehouse })}>
                                    <SquarePenIcon />
                                    <span>تعديل بيانات المخزن</span>
                                </Link>
                            </Button>
                        )}

                        {can.delete && (
                            <ConfirmDeleteAction
                                title="حذف المخزن"
                                href={destroy.url({ warehouse: warehouse })}
                            />
                        )}
                    </ActionsSection>
                )}


                <StatCardsSection
                    items={[
                        { label: "المُراقبات", value: warehouse.monitors_count || 0, icon: LandmarkIcon },
                        { label: "المدارس", value: warehouse.schools_count || 0, icon: SchoolIcon },
                    ]}
                    columns={2}
                />

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات المخزن</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <DetailFields columns={2}>
                                <DetailField>
                                    <DetailLabel>اسم المخزن</DetailLabel>
                                    <DetailValue value={warehouse.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>العنوان</DetailLabel>
                                    <DetailValue value={warehouse.address} />
                                </DetailField>
                            </DetailFields>

                            {/* <DetailFields columns={1}>
                                <DetailField className="col-span-full">
                                    <DetailLabel>المُراقبات</DetailLabel>
                                    <DetailValue variant="default">
                                        {warehouse.monitors && warehouse.monitors.length > 0 ? (
                                            <ul className="flex flex-col gap-3 list-disc list-inside">
                                                {warehouse.monitors.map((monitor) => (
                                                    <li key={monitor.uuid}>{monitor.name}</li>
                                                ))}
                                            </ul>
                                        ) : (
                                            "-"
                                        )}
                                    </DetailValue>
                                </DetailField>
                            </DetailFields> */}

                            {warehouse.has_coordinates && (
                                <>
                                    <DetailFields columns={2}>
                                        <DetailField>
                                            <DetailLabel>خط العرض</DetailLabel>
                                            <DetailValue value={warehouse.latitude} className="font-mono" />
                                        </DetailField>

                                        <DetailField>
                                            <DetailLabel>خط الطول</DetailLabel>
                                            <DetailValue value={warehouse.longitude} className="font-mono" />
                                        </DetailField>
                                    </DetailFields>

                                    <DetailFields columns={1}>
                                        <DetailField className="col-span-full">
                                            <DetailLabel>الموقع على الخريطة</DetailLabel>
                                            <LocationShowMap
                                                latitude={warehouse.latitude}
                                                longitude={warehouse.longitude}
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
                                <span>المُراقبات</span>
                            </CardTitle>
                        </CardHeader>
                        {monitorsData.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">اسم المُراقبة</TableHead>
                                            <TableHead scope="col" className="text-center">عدد مكاتب الخدمات التعليمية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد المدارس</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                            <TableHead scope="col" />
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {monitorsData.map((monitor: MonitorProps, index: number) => (
                                            <TableRow key={monitor.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{monitor.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={monitor.offices_count} fallback={0} />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={monitor.schools_count} fallback={0} />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={monitor.students_count} fallback={0} />
                                                </TableCell>
                                                <TableCellActions>
                                                    {monitor.canAny && (
                                                        <>
                                                            {monitor.can.view && (
                                                                <ViewDetailsLink
                                                                    href={showMonitor.url({ monitor: monitor })}
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
                                    links={monitorsLinks}
                                    meta={monitorsMeta}
                                />
                            </CardFooter>
                        )}
                    </Card>
                </section>
            </MainContainer>
        </>
    )
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المخازن',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المخزن',
            href: show.url({ warehouse: props.warehouse }),
        },
    ],
});
