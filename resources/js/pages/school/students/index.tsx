import React from 'react'

import { Form, Head, Link, usePage } from "@inertiajs/react";

import { decimalInputConstraints, libyanNationalIdInputConstraints, passportNumberInputConstraints } from "@/lib/input-constraints";

import type { CanPermissions, Enum, Nationality, Paginated, Student } from "@/types";

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

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { ListIcon, PlusIcon, RefreshCcwIcon, SearchIcon, UserPlusIcon } from "lucide-react";

import { create, index, show } from "@/routes/school/students";
import { create as createTransfer } from "@/routes/school/students/transfers";

type StudentProps = Student & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    students: Paginated<StudentProps>;
    nationalities?: Pick<Nationality, "id" | "name">[];
    registrationStatuses?: Enum[];
    filter: {
        name?: string;
        registration_status?: string;
        nationality_id?: string;
        national_id?: string;
        family_registration_number?: string;
        passport_number?: string;
    };
    canAny: boolean;
    can: CanPermissions;
}

export default function Index({ students, nationalities, registrationStatuses, filter, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const { data, links, ...meta } = students;

    const hasFilter = Object.values(filter).some((value) => value);

    const hasPagination = data.length > 0 && meta.last_page > 1;

    return (
        <>
            <Head title="الطلاب" />

            <MainContainer showAcademicYearNotice>
                {(canAny && currentAcademicYear?.is_active) && (
                    <ActionsSection>
                        {can.addTransferredStudent && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href={createTransfer.url()}>
                                    <UserPlusIcon />
                                    <span>إضافة طالب مُنتقل</span>
                                </Link>
                            </Button>
                        )}

                        {can.create && (
                            <Button
                                variant="default"
                                asChild
                            >
                                <Link href={create.url()}>
                                    <PlusIcon />
                                    <span>إضافة طالب جديد</span>
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
                                        defaultValue={filter.name}
                                        placeholder="اسم الطالب"
                                        autoComplete="off"
                                    />

                                    <Select
                                        name="filter[registration_status]"
                                        defaultValue={filter.registration_status || undefined}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="صفة القيد" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {(registrationStatuses ?? []).map((status) => (
                                                    <SelectItem key={status.id} value={status.id}>
                                                        {status.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>

                                    <Select
                                        name="filter[nationality_id]"
                                        defaultValue={filter.nationality_id || undefined}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="الجنسية" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                {(nationalities ?? []).map((nationality) => (
                                                    <SelectItem
                                                        key={nationality.id}
                                                        value={nationality.id.toString()}
                                                    >
                                                        {nationality.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>

                                    <Input
                                        type="text"
                                        name="filter[national_id]"
                                        defaultValue={filter.national_id}
                                        className="not-placeholder-shown:font-mono"
                                        placeholder="الرقم الوطني"
                                        autoComplete="off"
                                        {...libyanNationalIdInputConstraints()}
                                    />

                                    <Input
                                        type="text"
                                        name="filter[family_registration_number]"
                                        defaultValue={filter.family_registration_number}
                                        className="not-placeholder-shown:font-mono"
                                        placeholder="رقم القيد"
                                        autoComplete="off"
                                        {...decimalInputConstraints({
                                            allowDecimal: false,
                                            allowNegative: false,
                                        })}
                                    />

                                    <Input
                                        type="text"
                                        name="filter[passport_number]"
                                        defaultValue={filter.passport_number}
                                        className="not-placeholder-shown:font-mono"
                                        placeholder="رقم جواز السفر"
                                        autoComplete="off"
                                        {...passportNumberInputConstraints()}
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
                                <span>الطلاب</span>
                            </CardTitle>
                        </CardHeader>
                        {data.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col">رقم الطالب</TableHead>
                                            <TableHead scope="col">اسم الطالب</TableHead>
                                            <TableHead scope="col">الرقم الوطني</TableHead>
                                            <TableHead scope="col">صفة القيد</TableHead>
                                            <TableHead scope="col" />
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {data.map((student) => (
                                            <TableRow key={student.uuid}>
                                                <TableCell className="font-mono">{student.number}</TableCell>
                                                <TableCell>
                                                    <div>{student.full_name}</div>
                                                    <div className="mt-2 text-xs text-muted-foreground">
                                                        <span>الجنس:</span>
                                                        <span className="ms-1">{student.gender.name}</span>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    {student.is_libyan ? (
                                                        <div className="font-mono">{student.national_id}</div>
                                                    ) : (
                                                        <div className="font-sans">{student.nationality?.name ?? "أجنبي"}</div>
                                                    )}

                                                    {student.is_libyan ? (
                                                        <div className="mt-2 text-xs text-muted-foreground">
                                                            <span>رقم القيد:</span>
                                                            <span className="font-mono ms-1">{student.family_registration_number}</span>
                                                        </div>
                                                    ) : (
                                                        student.passport_number && (
                                                            <div className="mt-2 text-xs text-muted-foreground">
                                                                <span>رقم جواز السفر:</span>
                                                                <span className="font-mono ms-1">{student.passport_number}</span>
                                                            </div>
                                                        )
                                                    )}
                                                </TableCell>
                                                <TableCell>{student.registration_status.name}</TableCell>
                                                <TableCellActions>
                                                    {student.canAny && student.can.view && (
                                                        <ViewDetailsLink href={show.url({ student })} />
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
            title: 'الطلاب',
            href: index.url(),
        },
    ],
});
