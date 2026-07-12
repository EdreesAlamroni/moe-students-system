import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import MainContainer from "@/components/ui/structure/main-container";

import type { Enum, GradeLevel, Paginated } from "@/types";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index } from "@/routes/administration/grade-levels";

type PageProps = {
    gradeLevels: Paginated<GradeLevel>;
    educationalStages: Enum[];
    filter: {
        name?: string;
        educational_stage?: string;
    };
}

export default function Index({ gradeLevels, educationalStages, filter }: PageProps) {

    const { data, links, ...meta } = gradeLevels;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <MainContainer>
            <Head title="الصفوف الدراسية" />

            <section>
                <Form
                    action={index.url()}
                    method="GET"
                >
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle className="flex items-center text-sm gap-x-1.5">
                                <div className="flex items-center gap-x-3">
                                    <FunnelIcon />
                                    <span>فرز النتائج</span>
                                </div>
                                <span className="font-mono">({meta.total})</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <Input
                                    type="text"
                                    name="filter[name]"
                                    value={filter.name}
                                    placeholder="الاسم"
                                    autoComplete="off"
                                />

                                <Select
                                    name="filter[educational_stage]"
                                    defaultValue={filter.educational_stage || undefined}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="اختر المرحلة الدراسية" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {educationalStages.map((stage: Enum) => (
                                                <SelectItem
                                                    key={stage.id}
                                                    value={stage.id}
                                                >
                                                    {stage.name}
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
                            <span>الصفوف الدراسية</span>
                        </CardTitle>
                    </CardHeader>
                    {data.length > 0 ? (
                        <CardTableContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                        <TableHead scope="col">الاسم</TableHead>
                                        <TableHead scope="col">المرحلة الدراسية</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.map((gradeLevel: GradeLevel, index: number) => (
                                        <TableRow key={gradeLevel.uuid}>
                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                            <TableCell>{gradeLevel.name}</TableCell>
                                            <TableCell>{gradeLevel.educational_stage.name}</TableCell>
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
            title: 'الصفوف الدراسية',
            href: index.url(),
        },
    ],
});
