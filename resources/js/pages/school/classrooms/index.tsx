import React from 'react'

import { Form, Head, Link, usePage } from "@inertiajs/react";

import type { CanPermissions, Classroom, Enum, GradeLevel, Paginated } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellActions, TableCellNullableValue } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { create, index, show } from "@/routes/school/classrooms";

type ClassroomProps = Classroom & {
    canAny: boolean;
    can: CanPermissions;
};

type PageProps = {
    classrooms: Paginated<ClassroomProps>;
    gradeLevels: GradeLevel[];
    classroomNames: Pick<Enum, "id" | "name">[];
    filter: {
        grade_level_id?: string;
        name?: string;
    };
    canAny: boolean;
    can: CanPermissions;
};

export default function Index({ classrooms, gradeLevels, classroomNames, filter, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const { data, links, ...meta } = classrooms;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <MainContainer changeAcademicYearNotice>
            <Head title="الفصول الدراسية" />

            {(canAny && currentAcademicYear?.is_active) && (
                <ActionsSection>
                    {can.create && (
                        <Button asChild>
                            <Link href={create.url()}>
                                <PlusIcon />
                                <span>إضافة فصل دراسي</span>
                            </Link>
                        </Button>
                    )}
                </ActionsSection>
            )}

            <section>
                <Form {...index.form()}>
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
                                <Select
                                    name="filter[grade_level_id]"
                                    defaultValue={filter.grade_level_id || undefined}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="اختر الصف الدراسي" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            {gradeLevels.map((gradeLevel) => (
                                                <SelectItem
                                                    key={gradeLevel.id}
                                                    value={gradeLevel.id.toString()}
                                                >
                                                    {gradeLevel.name}
                                                </SelectItem>
                                            ))}
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>

                                <Select
                                    name="filter[name]"
                                    defaultValue={filter.name || undefined}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="اختر اسم الفصل الدراسي" />
                                    </SelectTrigger>
                                    <SelectContent className="font-mono">
                                        <SelectGroup>
                                            {classroomNames.map((classroomName) => (
                                                <SelectItem
                                                    key={classroomName.id}
                                                    value={classroomName.id}
                                                >
                                                    {classroomName.name}
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
                            <span>الفصول الدراسية</span>
                        </CardTitle>
                    </CardHeader>
                    {data.length > 0 ? (
                        <CardTableContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                        <TableHead scope="col">الصف / الفصل الدراسي</TableHead>
                                        <TableHead scope="col">المرحلة الدراسية</TableHead>
                                        <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        <TableHead scope="col" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.map((classroom, index) => (
                                        <TableRow key={classroom.uuid}>
                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                            <TableCell>
                                                <div className="space-x-1">
                                                    <span>{classroom.grade_level?.name}</span>
                                                    <span>/</span>
                                                    <span className="font-mono">{classroom.name}</span>
                                                </div>
                                                <div className="mt-2 text-xs text-muted-foreground">
                                                    <span>الفصل الدراسي:</span>
                                                    <span className="ms-1 font-mono">{classroom.name}</span>
                                                </div>
                                            </TableCell>
                                            <TableCell>{classroom.grade_level?.educational_stage.name}</TableCell>
                                            <TableCell className="text-center">
                                                <TableCellNullableValue
                                                    value={classroom.students_count}
                                                    className="font-mono"
                                                    fallback="0"
                                                />
                                            </TableCell>
                                            <TableCellActions>
                                                {classroom.canAny && (
                                                    <>
                                                        {classroom.can.view && (
                                                            <ViewDetailsLink
                                                                href={show.url({ classroom: classroom })}
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
                            <EmptyState hasFilter={hasFilter} />
                        </CardContent>
                    )}
                    {hasPagination && (
                        <CardFooter className="border-t">
                            <Paginator links={links} meta={meta} />
                        </CardFooter>
                    )}
                </Card>
            </section>
        </MainContainer>
    );
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'الفصول الدراسية',
            href: index.url(),
        },
    ],
});
