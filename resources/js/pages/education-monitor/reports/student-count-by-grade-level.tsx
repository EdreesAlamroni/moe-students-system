import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { CanPermissions, Enum, GradeLevel, Paginated } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { Table, TableBody, TableCell, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PrinterIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index, print } from "@/routes/education-monitor/reports/student-count-by-grade-level";

type PageProps = {
    gradeLevels: Paginated<Pick<GradeLevel, 'id' | 'uuid' | 'name' | 'educational_stage' | 'students_count'>>;
    educationalStages: Enum[];
    filter: {
        educational_stage?: string;
    };
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ gradeLevels, educationalStages, filter, canAny, can }: PageProps) {
    const { data, ...meta } = gradeLevels;

    return (
        <>
            <Head title="إحصائية الطلاب حسب الصفوف الدراسية" />

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
                                        name="filter[educational_stage]"
                                        defaultValue={filter.educational_stage || undefined}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="اختر المرحلة التعليمية" />
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
                                <span>إحصائية الطلاب حسب الصفوف الدراسية</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">الصف الدراسي</TableHead>
                                            <TableHead scope="col">المرحلة التعليمية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((gradeLevel, index: number) => (
                                            <TableRow key={gradeLevel.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{gradeLevel.name}</TableCell>
                                                <TableCell>{gradeLevel.educational_stage.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue className="font-mono" value={gradeLevel.students_count} fallback={0} />
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
            title: 'إحصائية الطلاب حسب الصفوف الدراسية',
            href: index.url(),
        },
    ],
});
