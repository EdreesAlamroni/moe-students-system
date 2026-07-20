import React from 'react'

import { Form, Head, Link, usePage } from "@inertiajs/react";

import type { CanPermissions, ClassPeriod, Enum, Paginated } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellActions } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/navigation/dropdown-menu";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ClockIcon, ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index, create, show } from "@/routes/administration/class-periods";

type ClassPeriodProps = ClassPeriod & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    classPeriods: Paginated<ClassPeriodProps>;
    academicPeriods: Enum[];
    filter: {
        name?: string;
        academic_period?: string;
    };
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ classPeriods, academicPeriods, filter, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const { data, links, ...meta } = classPeriods;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <>
            <Head title="الحصص الدراسية" />

            <MainContainer changeAcademicYearNotice>
                {(canAny && currentAcademicYear?.is_active) && (
                    <ActionsSection>
                        {can.create && (
                            <DropdownMenu>
                                <DropdownMenuTrigger asChild>
                                    <Button variant="default">
                                        <PlusIcon />
                                        <span>إضافة حصة جديدة</span>
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent className="min-w-56">
                                    {academicPeriods.map((period, index) => (
                                        <>
                                            <DropdownMenuItem key={period.id} asChild>
                                                <Link href={create.url(period.id)}>
                                                    <ClockIcon />
                                                    <span>إضافة حصة {period.name}</span>
                                                </Link>
                                            </DropdownMenuItem>

                                            {(index !== (academicPeriods.length - 1)) && <DropdownMenuSeparator />}
                                        </>
                                    ))}
                                </DropdownMenuContent>
                            </DropdownMenu>
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
                                        placeholder="اسم الحصة"
                                        autoComplete="off"
                                    />

                                    <Select
                                        name="filter[academic_period]"
                                        defaultValue={filter.academic_period || undefined}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر الفترة الدراسية" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {academicPeriods.map((period) => (
                                                    <SelectItem
                                                        key={period.id}
                                                        value={period.id}
                                                    >
                                                        {period.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
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
                                <span>الحصص الدراسية</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">اسم الحصة</TableHead>
                                            <TableHead scope="col">وقت البداية</TableHead>
                                            <TableHead scope="col">وقت النهاية</TableHead>
                                            <TableHead scope="col">الفترة الدراسية</TableHead>
                                            <TableHead scope="col" />
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((classPeriod: ClassPeriodProps, index: number) => (
                                            <TableRow key={classPeriod.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{classPeriod.name}</TableCell>
                                                <TableCell className="font-mono">{classPeriod.start_time}</TableCell>
                                                <TableCell className="font-mono">{classPeriod.end_time}</TableCell>
                                                <TableCell>{classPeriod.academic_period.name}</TableCell>
                                                <TableCellActions>
                                                    {classPeriod.canAny && (
                                                        <>
                                                            {classPeriod.can.view && (
                                                                <ViewDetailsLink
                                                                    href={show.url({ classPeriod: classPeriod })}
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
            title: 'الحصص الدراسية',
            href: index.url(),
        },
    ],
});
