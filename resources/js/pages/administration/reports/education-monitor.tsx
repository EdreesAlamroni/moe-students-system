import React from 'react'

import { Head } from "@inertiajs/react";

import type { CanPermissions, EducationMonitor, Paginated } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableBody, TableCell, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Button } from "@/components/ui/actions/button";

import { Paginator } from "@/components/ui/navigation/paginator";

import { ListIcon, PrinterIcon, } from "lucide-react";

import { index, print } from "@/routes/administration/reports/education-monitors";

type PageProps = {
    monitors: Paginated<EducationMonitor>;
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ monitors, canAny, can }: PageProps) {
    const { data, links, ...meta } = monitors;

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <>
            <Head title="تقرير المُراقبات" />

            <MainContainer showAcademicYearNotice>
                {canAny && (
                    <ActionsSection>
                        {can.print && (
                            <Button
                                variant="default"
                                disabled={data.length === 0}
                                asChild
                            >
                                {data.length > 0 ? (
                                    <a href={print.url()} target="_blank">
                                        <PrinterIcon />
                                        <span>طباعة التقرير</span>
                                    </a>
                                ) : (
                                    <span className="flex cursor-not-allowed items-center gap-1 opacity-50">
                                        <PrinterIcon />
                                        <span>طباعة التقرير</span>
                                    </span>
                                )}
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <ListIcon />
                                <span>تقرير المُراقبات</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">المُراقبة</TableHead>
                                            <TableHead scope="col" className="text-center">عدد مكاتب الخدمات التعليمية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد المدارس</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((monitor: EducationMonitor, index: number) => (
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
                                    links={links}
                                    meta={meta}
                                />
                            </CardFooter>
                        )}
                    </Card>
                </section>
            </MainContainer>
        </>
    )
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'تقرير المُراقبات',
            href: index.url(),
        },
    ],
});
