import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { CanPermissions, Paginated, AcademicYear } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellActions } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";
import DatePicker from "@/components/ui/controls/date-picker";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index, create, show } from "@/routes/administration/academic-years";

type AcademicYearProps = AcademicYear & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    academicYears: Paginated<AcademicYearProps>;
    filter: {
        name?: string;
        start_date?: string;
        end_date?: string;
    }
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ academicYears, filter, canAny, can }: PageProps) {
    const { data, links, ...meta } = academicYears;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <MainContainer>
            <Head title="السنوات الدراسية" />

            {canAny && (
                <ActionsSection>
                    {can.create && (
                        <Button
                            variant="default"
                            asChild
                        >
                            <Link href={create.url()}>
                                <PlusIcon />
                                <span>إضافة سنة دراسية جديد</span>
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
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <Input
                                    type="text"
                                    name="filter[name]"
                                    defaultValue={filter.name || undefined}
                                    placeholder="السنة الدراسية"
                                    autoComplete="off"
                                />

                                <DatePicker
                                    id="start_date"
                                    name="filter[start_date]"
                                    placeholder="تاريخ بداية العام الدراسي"
                                    date={filter.start_date}
                                />

                                <DatePicker
                                    id="end_date"
                                    name="filter[end_date]"
                                    placeholder="تاريخ انتهاء العام الدراسي"
                                    date={filter.end_date}
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
                            <span>السنوات الدراسية</span>
                        </CardTitle>
                    </CardHeader>
                    {data.length > 0 ? (
                        <CardTableContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                        <TableHead scope="col">السنة الدراسية</TableHead>
                                        <TableHead scope="col" className="text-center">تاريخ بداية العام الدراسي</TableHead>
                                        <TableHead scope="col" className="text-center">تاريخ انتهاء العام الدراسي</TableHead>
                                        <TableHead scope="col" className="text-center">الحالة</TableHead>
                                        <TableHead scope="col" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.map((academicYear: AcademicYearProps, index: number) => (
                                        <TableRow key={academicYear.uuid}>
                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                            <TableCell className="font-mono">{academicYear.name}</TableCell>
                                            <TableCell className="text-center font-mono">{academicYear.start_date}</TableCell>
                                            <TableCell className="text-center font-mono">{academicYear.end_date}</TableCell>
                                            <TableCell className="text-center">
                                                <div className={["pill", academicYear.is_active ? "pill-green" : "pill-orange"].join(" ")}>
                                                    {academicYear.status}
                                                </div>
                                            </TableCell>
                                            <TableCellActions>
                                                {academicYear.canAny && (
                                                    <>
                                                        {academicYear.can.view && (
                                                            <ViewDetailsLink
                                                                href={show.url({ academicYear: academicYear })}
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
    )
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'السنوات الدراسية',
            href: index.url(),
        },
    ],
});
