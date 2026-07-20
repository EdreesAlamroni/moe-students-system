import React, { useEffect, useState } from 'react'

import { Form, Head, Link, router } from "@inertiajs/react";

import type { CanPermissions, EducationMonitor, Enum, Nationality, Paginated, Student } from "@/types";

import { cn } from "@/lib/utils";

import MainContainer from "@/components/ui/structure/main-container";
import { Skeleton } from "@/components/ui/structure/skeleton";
import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Table, TableBody, TableCell, TableCellActions, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import Field from "@/components/ui/controls/field";
import { Label } from "@/components/ui/controls/label";
import { Input } from "@/components/ui/controls/input";
import { EmptyOptionsInput } from "@/components/ui/controls/empty-options-input";
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/controls/select";

import { Button } from "@/components/ui/actions/button";
import ViewDetailsLink from "@/components/ui/actions/view-details-link";

import { Paginator } from "@/components/ui/navigation/paginator";

import FunnelIcon from "@/components/ui/icons/funnel-icon";
import { Building2Icon, ListIcon, RefreshCcwIcon, SearchIcon } from "lucide-react";

import { show } from "@/routes/administration/students";
import { index } from "@/routes/administration/students/unassigned-to-school";

type OrganizationOption = Pick<EducationMonitor, "id" | "name">;

type StudentProps = Student & {
    canAny: boolean;
    can: CanPermissions;
};

type PageProps = {
    students?: Paginated<StudentProps>;
    monitors: OrganizationOption[];
    nationalities?: Pick<Nationality, "id" | "name">[];
    registrationStatuses?: Enum[];
    education_monitor_id?: number | null;
    filter: {
        name?: string;
        registration_status?: string;
        nationality_id?: string;
        national_id?: string;
        family_registration_number?: string;
        passport_number?: string;
    };
};

const visitOptions = {
    preserveState: true,
    preserveScroll: true,
} as const;

function StudentsSectionSkeleton() {
    return (
        <>
            <section aria-busy="true" aria-label="جارٍ تحميل فلاتر البحث">
                <Card>
                    <CardHeader className="border-b">
                        <Skeleton className="h-5 w-40" />
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            {Array.from({ length: 6 }).map((_, index) => (
                                <Skeleton key={index} className="h-10 w-full" />
                            ))}
                        </div>
                    </CardContent>
                    <CardFooter className="border-t">
                        <div className="flex items-center gap-x-3">
                            <Skeleton className="h-10 w-24" />
                            <Skeleton className="h-10 w-36" />
                        </div>
                    </CardFooter>
                </Card>
            </section>

            <section aria-busy="true" aria-label="جارٍ تحميل قائمة الطلاب">
                <Card>
                    <CardHeader className="border-b">
                        <Skeleton className="h-5 w-24" />
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {Array.from({ length: 5 }).map((_, index) => (
                                <Skeleton key={index} className="h-14 w-full" />
                            ))}
                        </div>
                    </CardContent>
                </Card>
            </section>
        </>
    );
}

export default function Index({
    students,
    monitors,
    nationalities,
    registrationStatuses,
    education_monitor_id,
    filter,
}: PageProps) {
    const [pendingMonitorId, setPendingMonitorId] = useState<string | undefined>();
    const [isNavigating, setIsNavigating] = useState(false);

    useEffect(() => {
        const removeStartListener = router.on("start", () => setIsNavigating(true));
        const removeFinishListener = router.on("finish", () => {
            setIsNavigating(false);
            setPendingMonitorId(undefined);
        });

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    const monitorId = education_monitor_id?.toString();
    const monitorPending = pendingMonitorId !== undefined && pendingMonitorId !== monitorId;
    const activeMonitorId = monitorId ?? pendingMonitorId;
    const studentsStale = isNavigating && monitorPending;
    const studentsLoading = Boolean(activeMonitorId && (!students || studentsStale));
    const studentsReloading = Boolean(activeMonitorId && students && isNavigating && !studentsStale);
    const hasStudentFilter = Object.values(filter).some(Boolean);
    const studentData = students?.data ?? [];
    const hasPagination = Boolean(students && studentData.length > 0 && students.last_page > 1);

    const handleMonitorChange = (value: string) => {
        setPendingMonitorId(value);

        router.get(index.url(), {
            education_monitor_id: value,
        }, visitOptions);
    };

    return (
        <>
            <Head title="الطلاب غير المسجّلين في مدارس" />

            <MainContainer showAcademicYearNotice>
                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <Building2Icon />
                                <span>اختيار الجهة التعليمية</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <Field>
                                    <Label htmlFor="education_monitor_id" required>
                                        المُراقبة
                                    </Label>

                                    {monitors.length > 0 ? (
                                        <Select
                                            value={activeMonitorId}
                                            disabled={isNavigating}
                                            onValueChange={handleMonitorChange}
                                        >
                                            <SelectTrigger id="education_monitor_id">
                                                <SelectValue placeholder="اختر المُراقبة" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {monitors.map((monitor) => (
                                                        <SelectItem
                                                            key={monitor.id}
                                                            value={monitor.id.toString()}
                                                        >
                                                            {monitor.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="education_monitor_id"
                                            placeholder="لا توجد مُراقبات متاحة للاختيار"
                                        />
                                    )}
                                </Field>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {!activeMonitorId && (
                    <section>
                        <Card>
                            <CardContent>
                                <EmptyState
                                    icon={Building2Icon}
                                    text="ابدأ باختيار الجهة التعليمية"
                                    description="اختر المُراقبة لعرض قائمة الطلاب غير المسجّلين في مدارس وتطبيق فلاتر البحث."
                                    className="[&>:last-child]:max-w-md"
                                />
                            </CardContent>
                        </Card>
                    </section>
                )}

                {activeMonitorId && studentsLoading && (
                    <StudentsSectionSkeleton />
                )}

                {activeMonitorId && !studentsLoading && (
                    <div
                        className={cn(
                            "relative space-y-6 transition-opacity duration-200",
                            studentsReloading && "pointer-events-none opacity-60",
                        )}
                        aria-busy={studentsReloading}
                    >
                        {studentsReloading && (
                            <div className="pointer-events-none absolute inset-x-0 top-0 z-10 flex justify-center pt-6"></div>
                        )}

                        <section>
                            <Form
                                {...index.form()}
                            >
                                <input type="hidden" name="education_monitor_id" value={activeMonitorId} />

                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>
                                            <FunnelIcon />
                                            <div className="flex items-center gap-x-1.5">
                                                <span>فرز النتائج</span>
                                                {students && (
                                                    <span className="font-mono">({students.total})</span>
                                                )}
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
                                                placeholder="الرقم الوطني"
                                                autoComplete="off"
                                            />

                                            <Input
                                                type="text"
                                                name="filter[family_registration_number]"
                                                defaultValue={filter.family_registration_number}
                                                placeholder="رقم القيد"
                                                autoComplete="off"
                                            />

                                            <Input
                                                type="text"
                                                name="filter[passport_number]"
                                                defaultValue={filter.passport_number}
                                                placeholder="رقم جواز السفر"
                                                autoComplete="off"
                                            />
                                        </div>
                                    </CardContent>
                                    <CardFooter className="border-t">
                                        <div className="flex items-center gap-x-3">
                                            <Button type="submit" variant="default">
                                                <SearchIcon />
                                                <span>بحث</span>
                                            </Button>
                                            <Button type="reset" variant="outline" asChild>
                                                <Link href={index.url({
                                                    query: {
                                                        education_monitor_id: activeMonitorId,
                                                    },
                                                })}
                                                >
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
                                {studentData.length > 0 ? (
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
                                                {studentData.map((student) => (
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
                                                                <div className="mt-1.5 text-xs text-muted-foreground">
                                                                    <span>رقم القيد:</span>
                                                                    <span className="font-mono ms-1">{student.family_registration_number}</span>
                                                                </div>
                                                            ) : (
                                                                student.passport_number && (
                                                                    <div className="mt-1.5 text-xs text-muted-foreground">
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
                                            hasFilter={hasStudentFilter}
                                        />
                                    </CardContent>
                                )}
                                {hasPagination && students && (
                                    <CardFooter className="border-t">
                                        <Paginator links={students.links} meta={students} />
                                    </CardFooter>
                                )}
                            </Card>
                        </section>
                    </div>
                )}
            </MainContainer>
        </>
    );
}

Index.layout = () => ({
    breadcrumbs: [
        {
            title: 'الطلاب غير المسجّلين في مدارس',
            href: index.url(),
        },
    ],
});
