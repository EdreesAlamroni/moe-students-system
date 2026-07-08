import React from 'react'

import { Form, Head, Link } from "@inertiajs/react";

import type { CanPermissions, GradeLevel, Paginated, Subject } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/display/actions-section";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellActions } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Input } from "@/components/ui/controls/input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { index, create, show } from "@/routes/administration/subjects";

type GradeLevelOption = Pick<GradeLevel, "id" | "name">;

type SubjectProps = Subject & {
    grade_level?: GradeLevelOption;
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    subjects: Paginated<SubjectProps>;
    gradeLevels: GradeLevelOption[];
    filter: {
        name?: string;
        code?: string;
        grade_level_id?: string;
    };
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ subjects, gradeLevels, filter, canAny, can }: PageProps) {
    const { data, links, ...meta } = subjects;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <MainContainer>
            <Head title="المقررات الدراسية" />

            {canAny && (
                <ActionsSection>
                    {can.create && (
                        <Button
                            variant="default"
                            asChild
                        >
                            <Link href={create.url()}>
                                <PlusIcon />
                                <span>إضافة مقرر دراسي جديد</span>
                            </Link>
                        </Button>
                    )}
                </ActionsSection>
            )}

            <section>
                <Form
                    action={index.url()}
                    method="GET"
                >
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle className="gap-x-1.5">
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
                                    defaultValue={filter.name}
                                    placeholder="اسم المقرر الدراسي"
                                />
                                <Select
                                    name="filter[grade_level_id]"
                                    defaultValue={filter.grade_level_id}
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
                        <CardTitle className="text-sm">
                            <ListIcon />
                            <span>المقررات الدراسية</span>
                        </CardTitle>
                    </CardHeader>
                    {data.length > 0 ? (
                        <CardTableContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                        <TableHead scope="col">اسم المقرر الدراسي</TableHead>
                                        <TableHead scope="col">الصف الدراسي</TableHead>
                                        <TableHead scope="col" />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {data.map((subject: SubjectProps, index: number) => (
                                        <TableRow key={subject.uuid}>
                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                            <TableCell>{subject.name}</TableCell>
                                            <TableCell>{subject.grade_level?.name}</TableCell>
                                            <TableCellActions>
                                                {subject.canAny && (
                                                    <>
                                                        {subject.can.view && (
                                                            <ViewDetailsLink
                                                                href={show.url({ subject: subject })}
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
            title: 'المقررات الدراسية',
            href: index.url(),
        },
    ],
});
