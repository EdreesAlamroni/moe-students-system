import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { CanPermissions, Paginated, Warehouse } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellActions, TableCellNullableValue } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index, create, show } from "@/routes/administration/warehouses";

type WarehouseProps = Warehouse & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    warehouses: Paginated<WarehouseProps>;
    filter: {
        name?: string;
    };
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ warehouses, filter, canAny, can }: PageProps) {
    const { data, links, ...meta } = warehouses;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <>
            <Head title="المخازن" />

            <MainContainer showAcademicYearNotice>
                {canAny && (
                    <ActionsSection>
                        {can.create && (
                            <Button
                                variant="default"
                                asChild
                            >
                                <Link href={create.url()}>
                                    <PlusIcon />
                                    <span>إضافة مخزن جديد</span>
                                </Link>
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <section>
                    <Form
                        {...index.form()}
                    >
                        <Card>
                            <CardHeader className="border-b">
                                <CardTitle>
                                    <FunnelIcon />
                                    <div className="flex items-center gap-x-1.5">
                                        <span>فرز النتائج</span>
                                        <span className="font-mono">({meta.total})</span>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <Input
                                        type="text"
                                        name="filter[name]"
                                        defaultValue={filter.name}
                                        placeholder="اسم المخزن"
                                        autoComplete="off"
                                    />
                                </div>
                            </CardContent>
                            <CardFooter className="border-t">
                                <div className="flex items-center gap-x-3">
                                    <Button
                                        type="submit"
                                        variant="default"
                                    >
                                        <SearchIcon />
                                        <span>بحث</span>
                                    </Button>
                                    <Button
                                        type="reset"
                                        variant="outline"
                                        asChild
                                    >
                                        <Link href={index.url()}>
                                            <RefreshCcwIcon />
                                            <span>مسح حقول الفلتر</span>
                                        </Link>
                                    </Button>
                                </div>
                            </CardFooter>
                        </Card>
                    </Form>
                </section>

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <ListIcon />
                                <span>المخازن</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">اسم المخزن</TableHead>
                                            <TableHead scope="col" className="text-center">عدد المُراقبات</TableHead>
                                            <TableHead scope="col" className="text-center">عدد المدارس</TableHead>
                                            <TableHead scope="col" />
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((warehouse: WarehouseProps, index: number) => (
                                            <TableRow key={warehouse.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{warehouse.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={warehouse.monitors_count} fallback={0} />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={warehouse.schools_count} fallback={0} />
                                                </TableCell>
                                                <TableCellActions>
                                                    {warehouse.canAny && (
                                                        <>
                                                            {warehouse.can.view && (
                                                                <ViewDetailsLink
                                                                    href={show.url({ warehouse: warehouse })}
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
                                <EmptyState
                                    hasFilter={hasFilter}
                                />
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
            title: 'المخازن',
            href: index.url(),
        },
    ],
});
