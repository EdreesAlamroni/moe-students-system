import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { CanPermissions, Enum, Paginated, School } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableBody, TableCell, TableCellActions, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { create, index, show } from "@/routes/education-services-office/schools";

type SchoolProps = School & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    schools: Paginated<SchoolProps>;
    types: Enum[];
    filter: {
        type?: string;
        name?: string;
    };
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ schools, types, filter, canAny, can }: PageProps) {
    const { data, links, ...meta } = schools;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <>
            <Head title="المدارس" />

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
                                    <span>إضافة مدرسة جديدة</span>
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
                                    <Select
                                        name="filter[type]"
                                        defaultValue={filter.type || undefined}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر نوع المدرسة" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {types.map((type) => (
                                                    <SelectItem
                                                        key={type.id}
                                                        value={type.id}
                                                    >
                                                        {type.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>

                                    <Input
                                        type="text"
                                        name="filter[name]"
                                        defaultValue={filter.name}
                                        placeholder="اسم المدرسة"
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
                                <span>المدارس</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="w-24 font-mono">#</TableHead>
                                            <TableHead scope="col">الرقم التسلسلي</TableHead>
                                            <TableHead scope="col">اسم المدرسة</TableHead>
                                            <TableHead scope="col">الفترة الدراسية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                            <TableHead scope="col" />
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((school: SchoolProps, index: number) => (
                                            <TableRow key={school.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell className="font-mono">{school.serial_number}</TableCell>
                                                <TableCell>
                                                    <div>{school.name}</div>
                                                    <div className="mt-2 text-xs text-muted-foreground">
                                                        {`مدرسة ${school.type.name}`}
                                                    </div>
                                                </TableCell>
                                                <TableCell>{school.academic_period.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={school.students_count} fallback={0} />
                                                </TableCell>
                                                <TableCellActions>
                                                    {school.canAny && (
                                                        <>
                                                            {school.can.view && (
                                                                <ViewDetailsLink
                                                                    href={show.url({ school: school })}
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
            title: 'المدارس',
            href: index.url(),
        },
    ],
});
