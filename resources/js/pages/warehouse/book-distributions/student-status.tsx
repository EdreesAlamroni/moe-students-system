import React, { useEffect, useState } from 'react';

import { Form, Head, Link, router } from '@inertiajs/react';

import type { EducationMonitor, Enum, GradeLevel, Nationality, Paginated, School, Student } from '@/types';

import { cn } from '@/lib/utils';

import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from '@/components/ui/structure/card';
import MainContainer from '@/components/ui/structure/main-container';
import { Skeleton } from '@/components/ui/structure/skeleton';

import { Badge } from '@/components/ui/display/badge';
import EmptyState from '@/components/ui/display/empty-state';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/display/table';

import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import Field from '@/components/ui/controls/field';
import { Input } from '@/components/ui/controls/input';
import { Label } from '@/components/ui/controls/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

import { Paginator } from '@/components/ui/navigation/paginator';

import { Button } from '@/components/ui/actions/button';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';

import FunnelIcon from '@/components/ui/icons/funnel-icon';
import { Building2Icon, CheckCircle2Icon, LoaderIcon, RefreshCcwIcon, SearchIcon, UsersIcon } from 'lucide-react';

import { index, students } from '@/routes/warehouse/book-distributions';

type OrganizationOption = Pick<EducationMonitor | School, 'id' | 'name'>;

type StudentListFilter = {
    name?: string;
    registration_status?: string;
    nationality_id?: string;
    national_id?: string;
    family_registration_number?: string;
    passport_number?: string;
};

type StudentStatusPageProps = {
    monitors: OrganizationOption[];
    schools: OrganizationOption[];
    gradeLevels: Pick<GradeLevel, 'id' | 'name'>[];
    students?: Paginated<Student>;
    registrationStatuses?: Enum[];
    nationalities?: Pick<Nationality, 'id' | 'name'>[];
    selected: {
        education_monitor_id: number | null;
        school_id: number | null;
        grade_level_id: number | null;
    };
    filter: StudentListFilter;
};

const visitOptions = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
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
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
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

export default function StudentStatusPage({
    monitors,
    schools,
    gradeLevels,
    students: paginatedStudents,
    registrationStatuses,
    nationalities,
    selected,
    filter,
}: StudentStatusPageProps) {
    const [isNavigating, setIsNavigating] = useState(false);
    const [pendingMonitorId, setPendingMonitorId] = useState<string>();
    const [pendingSchoolId, setPendingSchoolId] = useState<string>();
    const [pendingGradeLevelId, setPendingGradeLevelId] = useState<string>();

    useEffect(() => {
        const removeStartListener = router.on('start', () => setIsNavigating(true));
        const removeFinishListener = router.on('finish', () => {
            setIsNavigating(false);
            setPendingMonitorId(undefined);
            setPendingSchoolId(undefined);
            setPendingGradeLevelId(undefined);
        });

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    const monitorId = selected.education_monitor_id?.toString() ?? '';
    const schoolId = selected.school_id?.toString() ?? '';
    const gradeLevelId = selected.grade_level_id?.toString() ?? '';
    const monitorPending = pendingMonitorId !== undefined && pendingMonitorId !== monitorId;
    const schoolPending = pendingSchoolId !== undefined && pendingSchoolId !== schoolId;
    const gradeLevelPending = pendingGradeLevelId !== undefined && pendingGradeLevelId !== gradeLevelId;
    const activeMonitorId = monitorId || pendingMonitorId;
    const activeSchoolId = monitorPending ? pendingSchoolId : (schoolId || pendingSchoolId);
    const activeGradeLevelId = gradeLevelPending ? pendingGradeLevelId : (gradeLevelId || pendingGradeLevelId);
    const isLoadingSchools = Boolean(activeMonitorId && monitorPending);
    const isLoadingGradeLevels = Boolean(activeSchoolId && schoolPending);
    const showStudentSections = Boolean(activeGradeLevelId);
    const studentsStale = isNavigating && (monitorPending || schoolPending || gradeLevelPending);
    const studentsLoading = Boolean(activeGradeLevelId && (!paginatedStudents || studentsStale));
    const studentsReloading = Boolean(activeGradeLevelId && paginatedStudents && isNavigating && !studentsStale);
    const hasFilter = Object.values(filter).some(Boolean);

    const rows = paginatedStudents?.data ?? [];
    const hasPagination = Boolean(paginatedStudents && rows.length > 0 && paginatedStudents.last_page > 1);

    function selectMonitor(value: string): void {
        setPendingMonitorId(value);
        setPendingSchoolId(undefined);
        setPendingGradeLevelId(undefined);

        router.get(students.url(), { education_monitor_id: value }, visitOptions);
    }

    function selectSchool(value: string): void {
        setPendingSchoolId(value);
        setPendingGradeLevelId(undefined);

        router.get(students.url(), {
            education_monitor_id: activeMonitorId,
            school_id: value,
        }, visitOptions);
    }

    function selectGradeLevel(value: string): void {
        setPendingGradeLevelId(value);

        router.get(students.url(), {
            education_monitor_id: activeMonitorId,
            school_id: activeSchoolId,
            grade_level_id: value,
        }, visitOptions);
    }

    return (
        <>
            <Head title="حالة توزيع الكُتب المدرسية للطلاب" />

            <MainContainer showAcademicYearNotice>
                <Alert>
                    <SearchIcon />
                    <AlertTitle>حالة توزيع الكُتب المدرسية للطلاب</AlertTitle>
                    <AlertDescription className="flex flex-col gap-1">
                        <span>
                            اختر المُراقبة والمدرسة والصف الدراسي لعرض قائمة الطلاب وحالة استلام كل طالب للكُتب من المدرسة.
                        </span>
                        <span>تُعرض حالة الاستلام (تم الاستلام / لم يستلم) لكل طالب في الصف المحدد.</span>
                    </AlertDescription>
                </Alert>

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <Building2Icon />
                                <span>اختيار الجهة التعليمية</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                                <Field>
                                    <Label htmlFor="education_monitor_id" required>
                                        المُراقبة
                                    </Label>

                                    {monitors.length > 0 ? (
                                        <Select
                                            value={activeMonitorId || undefined}
                                            disabled={isLoadingSchools || isLoadingGradeLevels}
                                            onValueChange={selectMonitor}
                                        >
                                            <SelectTrigger id="education_monitor_id">
                                                <SelectValue placeholder="اختر المُراقبة" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {monitors.map((monitor) => (
                                                        <SelectItem key={monitor.id} value={String(monitor.id)}>
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

                                <Field>
                                    <Label htmlFor="school_id" required>
                                        المدرسة
                                    </Label>

                                    {!activeMonitorId ? (
                                        <EmptyOptionsInput id="school_id" placeholder="اختر المُراقبة أولاً" />
                                    ) : isLoadingSchools ? (
                                        <Select disabled open={false}>
                                            <SelectTrigger id="school_id" aria-busy="true">
                                                <span className="flex items-center gap-2 text-muted-foreground">
                                                    <LoaderIcon className="size-3.5 shrink-0 animate-spin" />
                                                    <span>جارٍ تحميل المدارس…</span>
                                                </span>
                                            </SelectTrigger>
                                        </Select>
                                    ) : schools.length > 0 ? (
                                        <Select
                                            value={activeSchoolId || undefined}
                                            disabled={isLoadingSchools || isLoadingGradeLevels}
                                            onValueChange={selectSchool}
                                        >
                                            <SelectTrigger id="school_id">
                                                <SelectValue placeholder="اختر المدرسة" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {schools.map((school) => (
                                                        <SelectItem key={school.id} value={String(school.id)}>
                                                            {school.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="school_id"
                                            placeholder="لا توجد مدارس متاحة لهذه المُراقبة"
                                        />
                                    )}
                                </Field>

                                <Field>
                                    <Label htmlFor="grade_level_id" required>
                                        الصف الدراسي
                                    </Label>

                                    {!activeSchoolId ? (
                                        <EmptyOptionsInput id="grade_level_id" placeholder="اختر المدرسة أولاً" />
                                    ) : isLoadingGradeLevels ? (
                                        <Select disabled open={false}>
                                            <SelectTrigger id="grade_level_id" aria-busy="true">
                                                <span className="flex items-center gap-2 text-muted-foreground">
                                                    <LoaderIcon className="size-3.5 shrink-0 animate-spin" />
                                                    <span>جارٍ تحميل الصفوف الدراسية…</span>
                                                </span>
                                            </SelectTrigger>
                                        </Select>
                                    ) : gradeLevels.length > 0 ? (
                                        <Select
                                            value={activeGradeLevelId || undefined}
                                            disabled={isLoadingGradeLevels}
                                            onValueChange={selectGradeLevel}
                                        >
                                            <SelectTrigger id="grade_level_id">
                                                <SelectValue placeholder="اختر الصف الدراسي" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {gradeLevels.map((gradeLevel) => (
                                                        <SelectItem key={gradeLevel.id} value={String(gradeLevel.id)}>
                                                            {gradeLevel.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="grade_level_id"
                                            placeholder="لا توجد صفوف دراسية مُضافة لهذه المدرسة"
                                        />
                                    )}
                                </Field>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {!showStudentSections ? (
                    <section>
                        <Card>
                            <CardContent>
                                <EmptyState
                                    icon={Building2Icon}
                                    text={
                                        activeSchoolId
                                            ? 'اختر الصف الدراسي للمتابعة'
                                            : activeMonitorId
                                                ? 'اختر المدرسة للمتابعة'
                                                : 'ابدأ باختيار الجهة التعليمية'
                                    }
                                    description={
                                        activeSchoolId
                                            ? 'بعد اختيار الصف الدراسي، ستظهر فلاتر البحث وقائمة الطلاب وحالة توزيع الكُتب.'
                                            : activeMonitorId
                                                ? 'بعد اختيار المدرسة، ستظهر الصفوف الدراسية المتاحة.'
                                                : 'اختر المُراقبة ثم المدرسة والصف الدراسي لعرض حالة توزيع الكُتب للطلاب.'
                                    }
                                />
                            </CardContent>
                        </Card>
                    </section>
                ) : studentsLoading ? (
                    <div className="flex flex-col gap-6">
                        <StudentsSectionSkeleton />
                    </div>
                ) : (
                    <div
                        className={cn(
                            'relative flex flex-col gap-6 transition-opacity duration-200',
                            studentsReloading && 'pointer-events-none opacity-60',
                        )}
                        aria-busy={studentsReloading}
                    >
                        <section>
                            <Form {...students.form()}>
                                <input type="hidden" name="education_monitor_id" value={activeMonitorId} />
                                <input type="hidden" name="school_id" value={activeSchoolId} />
                                <input type="hidden" name="grade_level_id" value={activeGradeLevelId} />

                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>
                                            <FunnelIcon />
                                            <div className="flex items-center gap-x-1.5">
                                                <span>فرز النتائج</span>
                                                {paginatedStudents && (
                                                    <span className="font-mono">({paginatedStudents.total})</span>
                                                )}
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
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
                                                                value={String(nationality.id)}
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
                                                <Link
                                                    href={students.url({
                                                        query: {
                                                            education_monitor_id: activeMonitorId,
                                                            school_id: activeSchoolId,
                                                            grade_level_id: activeGradeLevelId,
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
                            <Card className="gap-0">
                                <CardHeader className="gap-0 border-b">
                                    <CardTitle className="min-w-0 flex-1">
                                        <UsersIcon />
                                        <div className="flex min-w-0 flex-1 items-center gap-x-1.5">
                                            <span>قائمة الطلاب</span>
                                            {rows.length > 0 && (
                                                <span className="font-mono">({paginatedStudents!.total})</span>
                                            )}
                                        </div>
                                    </CardTitle>
                                </CardHeader>

                                {rows.length > 0 ? (
                                    <>
                                        <CardTableContent>
                                            <Table>
                                                <TableHeader>
                                                    <TableRow>
                                                        <TableHead scope="col" className="w-24 font-mono">
                                                            #
                                                        </TableHead>
                                                        <TableHead scope="col">رقم الطالب</TableHead>
                                                        <TableHead scope="col">اسم الطالب</TableHead>
                                                        <TableHead scope="col">الجنس</TableHead>
                                                        <TableHead scope="col" className="text-center">حالة توزيع الكُتب</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {rows.map((student, index) => (
                                                        <TableRow key={student.uuid}>
                                                            <TableCell className="font-mono">
                                                                {(paginatedStudents?.from ?? 0) + index}
                                                            </TableCell>
                                                            <TableCell className="font-mono">{student.number}</TableCell>
                                                            <TableCell>{student.full_name}</TableCell>
                                                            <TableCell>{student.gender.name}</TableCell>
                                                            <TableCell>
                                                                <div className="flex justify-center">
                                                                    {student.already_distributed ? (
                                                                        <Badge variant="success">
                                                                            <CheckCircle2Icon data-icon="inline-start" />
                                                                            تم الاستلام
                                                                        </Badge>
                                                                    ) : (
                                                                        <Badge variant="outline">لم يستلم</Badge>
                                                                    )}
                                                                </div>
                                                            </TableCell>
                                                        </TableRow>
                                                    ))}
                                                </TableBody>
                                            </Table>
                                        </CardTableContent>

                                        {hasPagination && (
                                            <CardFooter className="border-t">
                                                <Paginator links={paginatedStudents!.links} meta={paginatedStudents!} />
                                            </CardFooter>
                                        )}
                                    </>
                                ) : (
                                    <CardContent>
                                        <EmptyState
                                            hasFilter={hasFilter}
                                        />
                                    </CardContent>
                                )}
                            </Card>
                        </section>
                    </div>
                )}
            </MainContainer>
        </>
    );
}

StudentStatusPage.layout = () => ({
    breadcrumbs: [
        {
            title: 'توزيع الكُتب المدرسية',
            href: index.url(),
        },
        {
            title: 'حالة توزيع الكُتب للطلاب',
            href: students.url(),
        },
    ],
});
