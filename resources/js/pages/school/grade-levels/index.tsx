import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import MainContainer from "@/components/ui/structure/main-container";

import type { Enum, GradeLevel } from "@/types";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellNullableValue } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index } from "@/routes/school/grade-levels";

type PageProps = {
    gradeLevels: GradeLevel[];
    educationalStages: Enum[];
    filter: {
        name?: string;
        educational_stage?: string;
    };
}

export default function Index({ gradeLevels, educationalStages, filter }: PageProps) {
    return (
        <>
            <Head title="الصفوف الدراسية" />

            <MainContainer showAcademicYearNotice>
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
                                        <span className="font-mono">({gradeLevels.length})</span>
                                    </div>
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
                        {gradeLevels.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">الاسم</TableHead>
                                            <TableHead scope="col">المرحلة الدراسية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {gradeLevels.map((gradeLevel: GradeLevel, index: number) => (
                                            <TableRow key={gradeLevel.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{gradeLevel.name}</TableCell>
                                                <TableCell>{gradeLevel.educational_stage.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue value={gradeLevel.students_count} className="font-mono" fallback="0" />
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
                    </Card>
                </section>
            </MainContainer>
        </>
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
