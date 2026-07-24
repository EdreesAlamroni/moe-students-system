import React from 'react';

import { Form, Head, Link, router, useForm } from '@inertiajs/react';

import type { Classroom, GradeLevel, Student } from '@/types';

import { cn } from '@/lib/utils';

import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/structure/card';
import MainContainer from '@/components/ui/structure/main-container';
import { Skeleton } from '@/components/ui/structure/skeleton';

import { Badge } from '@/components/ui/display/badge';
import EmptyState from '@/components/ui/display/empty-state';
import { Table, TableBody, TableCell, TableCellNullableValue, TableHead, TableHeader, TableRow } from '@/components/ui/display/table';

import { Checkbox } from '@/components/ui/controls/checkbox';
import { EmptyOptionsInput } from '@/components/ui/controls/empty-options-input';
import Field from '@/components/ui/controls/field';
import { Input } from '@/components/ui/controls/input';
import { Label } from '@/components/ui/controls/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/controls/select';

import { Button } from '@/components/ui/actions/button';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';
import ValidationErrors from '@/components/ui/alerts/validation-errors';

import FunnelIcon from '@/components/ui/icons/funnel-icon';
import { BookCheckIcon, BookTextIcon, CheckCircle2Icon, FilterIcon, LoaderIcon, RefreshCcwIcon, SearchIcon, UsersIcon } from 'lucide-react';

import { ConfirmBookDistributionDialog } from '@/components/shared/book-distributions/confirm-distribution-dialog';

import { index, store } from '@/routes/school/book-distributions';

const visitOptions = {
    preserveState: true,
    preserveScroll: true,
    replace: true,
} as const;

export default function Index({
    gradeLevels,
    classrooms,
    students,
    warehouseConfirmed,
    selected,
    filter,
    can,
}: {
    gradeLevels: Pick<GradeLevel, 'id' | 'name'>[];
    classrooms: Pick<Classroom, 'id' | 'name'>[];
    students: (Student & { classroomName: string | null })[];
    warehouseConfirmed: boolean;
    selected: {
        grade_level_id: number | null;
        classroom_id: number | null;
    };
    filter: {
        name?: string;
        distribution_status?: string;
    };
    can: {
        distribute: boolean;
    };
}) {
    const [isNavigating, setIsNavigating] = React.useState(false);
    const [pendingGradeLevelId, setPendingGradeLevelId] = React.useState<string>();
    const [pendingClassroomId, setPendingClassroomId] = React.useState<string>();
    const [confirmOpen, setConfirmOpen] = React.useState(false);

    const form = useForm<{ student_ids: number[] }>({ student_ids: [] });

    React.useEffect(() => {
        const removeStartListener = router.on('start', () => setIsNavigating(true));
        const removeFinishListener = router.on('finish', () => {
            setIsNavigating(false);
            setPendingGradeLevelId(undefined);
            setPendingClassroomId(undefined);
        });

        return () => {
            removeStartListener();
            removeFinishListener();
        };
    }, []);

    React.useEffect(() => {
        form.reset('student_ids');
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [students]);

    const gradeLevelId = selected.grade_level_id?.toString() ?? '';
    const classroomId = selected.classroom_id?.toString() ?? '';
    const gradeLevelPending = pendingGradeLevelId !== undefined && pendingGradeLevelId !== gradeLevelId;
    const classroomPending = pendingClassroomId !== undefined && pendingClassroomId !== classroomId;
    const activeGradeLevelId = gradeLevelPending ? pendingGradeLevelId : (gradeLevelId || pendingGradeLevelId);
    const activeClassroomId = classroomPending ? pendingClassroomId : (classroomId || pendingClassroomId);
    const isLoadingClassrooms = Boolean(activeGradeLevelId && gradeLevelPending);
    const hasGradeLevelSelection = Boolean(activeGradeLevelId);
    const showStudentSections = Boolean(activeGradeLevelId && warehouseConfirmed);
    const studentsStale = isNavigating && (gradeLevelPending || classroomPending);
    const studentsLoading = Boolean(hasGradeLevelSelection && studentsStale);
    const studentsReloading = Boolean(showStudentSections && isNavigating && !studentsStale);

    const eligibleStudents = students.filter((student) => !student.already_distributed);
    const selectedCount = form.data.student_ids.length;
    const allEligibleSelected = eligibleStudents.length > 0 && selectedCount === eligibleStudents.length;
    const someEligibleSelected = selectedCount > 0 && !allEligibleSelected;
    const hasFilter = Boolean(filter.name) || (Boolean(filter.distribution_status) && filter.distribution_status !== 'all');

    const selectedGradeLevelName = gradeLevels.find((gradeLevel) => gradeLevel.id === selected.grade_level_id)?.name ?? '';
    const selectedClassroomName = selected.classroom_id
        ? (classrooms.find((classroom) => classroom.id === selected.classroom_id)?.name ?? null)
        : null;

    function selectGradeLevel(value: string): void {
        setPendingGradeLevelId(value);
        setPendingClassroomId(undefined);

        router.get(index.url(), { grade_level_id: value }, visitOptions);
    }

    function selectClassroom(value: string): void {
        setPendingClassroomId(value);

        router.get(index.url(), {
            grade_level_id: activeGradeLevelId,
            classroom_id: value === 'all' ? undefined : value,
        }, visitOptions);
    }

    function toggleAll(checked: boolean | 'indeterminate'): void {
        form.setData('student_ids', checked === true ? eligibleStudents.map((student) => student.id) : []);
    }

    function toggleStudent(studentId: number, checked: boolean): void {
        const ids = form.data.student_ids;

        form.setData('student_ids', checked ? [...ids, studentId] : ids.filter((id) => id !== studentId));
    }

    function confirmDistribution(): void {
        form.transform((data) => ({
            ...data,
            grade_level_id: selected.grade_level_id,
            classroom_id: selected.classroom_id,
        }));

        form.post(store.url(), {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => setConfirmOpen(false),
        });
    }

    return (
        <>
            <Head title="توزيع الكُتب المدرسية" />

            <MainContainer showAcademicYearNotice>
                <Alert>
                    <BookTextIcon />
                    <AlertTitle>توزيع الكُتب المدرسية</AlertTitle>
                    <AlertDescription className="flex flex-col gap-1">
                        <span>اختر الصف الدراسي، ويمكنك اختيار الفصل الدراسي اختياريًا لعرض الطلاب.</span>
                        <span>يسمح بتسليم الكُتب لكل طالب مرة واحدة فقط خلال السنة الدراسية الحالية.</span>
                    </AlertDescription>
                </Alert>

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <FilterIcon />
                                <span>اختيار الصف الدراسي</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <Field>
                                    <Label
                                        htmlFor="grade_level_id"
                                        required
                                    >
                                        الصف الدراسي
                                    </Label>

                                    {gradeLevels.length > 0 ? (
                                        <Select
                                            value={activeGradeLevelId || undefined}
                                            disabled={isNavigating}
                                            onValueChange={selectGradeLevel}
                                        >
                                            <SelectTrigger id="grade_level_id">
                                                <SelectValue placeholder="اختر الصف الدراسي" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    {gradeLevels.map((gradeLevel) => (
                                                        <SelectItem
                                                            key={gradeLevel.id}
                                                            value={String(gradeLevel.id)}
                                                        >
                                                            {gradeLevel.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="grade_level_id"
                                            placeholder="لا توجد صفوف دراسية متاحة للاختيار"
                                        />
                                    )}
                                </Field>

                                <Field>
                                    <Label
                                        htmlFor="classroom_id"
                                    >
                                        الفصل الدراسي (اختياري)
                                    </Label>

                                    {!activeGradeLevelId ? (
                                        <EmptyOptionsInput
                                            id="classroom_id"
                                            placeholder="اختر الصف الدراسي أولاً"
                                        />
                                    ) : isLoadingClassrooms ? (
                                        <Select disabled open={false}>
                                            <SelectTrigger id="classroom_id" aria-busy="true">
                                                <span className="flex items-center gap-2 text-muted-foreground">
                                                    <LoaderIcon className="size-3.5 shrink-0 animate-spin" />
                                                    <span>جارٍ تحميل الفصول الدراسية…</span>
                                                </span>
                                            </SelectTrigger>
                                        </Select>
                                    ) : classrooms.length > 0 ? (
                                        <Select
                                            value={activeClassroomId || 'all'}
                                            disabled={isNavigating}
                                            onValueChange={selectClassroom}
                                        >
                                            <SelectTrigger id="classroom_id">
                                                <SelectValue placeholder="جميع الفصول" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="all">جميع الفصول</SelectItem>
                                                    {classrooms.map((classroom) => (
                                                        <SelectItem
                                                            key={classroom.id}
                                                            value={String(classroom.id)}
                                                        >
                                                            {classroom.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <EmptyOptionsInput
                                            id="classroom_id"
                                            placeholder="لا توجد فصول دراسية لهذا الصف"
                                        />
                                    )}
                                </Field>
                            </div>
                        </CardContent>
                    </Card>
                </section>

                {!hasGradeLevelSelection ? (
                    <section>
                        <Card>
                            <CardContent>
                                <EmptyState
                                    text="اختر الصف الدراسي للمتابعة"
                                    description="بعد اختيار الصف الدراسي، ستظهر فلاتر الطلاب وقائمة الطلاب."
                                />
                            </CardContent>
                        </Card>
                    </section>
                ) : !warehouseConfirmed ? (
                    <section>
                        <Card>
                            <CardContent>
                                <EmptyState
                                    text="لم يتم تأكيد استلام الكُتب"
                                    description="لم يتم تأكيد استلام الكُتب لهذا الصف الدراسي من المخزن بعد. يرجى التواصل مع المخزن لإتمام عملية الاستلام أولاً."
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
                            <Form
                                {...index.form()}
                            >
                                <input type="hidden" name="grade_level_id" value={selected.grade_level_id ?? ''} />
                                {selected.classroom_id && (
                                    <input type="hidden" name="classroom_id" value={selected.classroom_id} />
                                )}

                                <Card>
                                    <CardHeader className="border-b">
                                        <CardTitle>
                                            <FunnelIcon />
                                            <div className="flex items-center gap-x-1.5">
                                                <span>فلترة الطلاب</span>
                                                <span className="font-mono">({students.length})</span>
                                            </div>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                                            <Input
                                                type="text"
                                                name="filter[name]"
                                                defaultValue={filter.name}
                                                placeholder="اسم الطالب"
                                                autoComplete="off"
                                            />

                                            <Select
                                                name="filter[distribution_status]"
                                                defaultValue={filter.distribution_status || 'all'}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="حالة توزيع الكُتب" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectGroup>
                                                        <SelectItem value="all">جميع الحالات</SelectItem>
                                                        <SelectItem value="pending">لم يستلم</SelectItem>
                                                        <SelectItem value="distributed">تم الاستلام</SelectItem>
                                                    </SelectGroup>
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </CardContent>
                                    <CardFooter className="border-t">
                                        <div className="flex items-center gap-x-3">
                                            <Button type="submit" variant="default">
                                                <SearchIcon />
                                                <span>بحث</span>
                                            </Button>
                                            <Button type="reset" variant="outline" disabled={!hasFilter} asChild>
                                                <Link
                                                    href={index.url({
                                                        query: {
                                                            grade_level_id: selected.grade_level_id,
                                                            classroom_id: selected.classroom_id ?? undefined,
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

                        <section className="flex flex-col gap-6">
                            <ValidationErrors errors={form.errors} />

                            <Card className="gap-0">
                                <CardHeader className="gap-0 border-b">
                                    <CardTitle className="min-w-0 flex-1">
                                        <UsersIcon />
                                        <div className="flex min-w-0 flex-1 items-center justify-between gap-x-4">
                                            <div className="flex items-center gap-x-1.5">
                                                <span>قائمة الطلاب</span>
                                                {students.length > 0 && (
                                                    <span className="font-mono">({students.length})</span>
                                                )}
                                            </div>

                                            {selectedCount > 0 && (
                                                <span className="inline-flex shrink-0 items-center gap-x-2 text-xs font-normal text-muted-foreground">
                                                    <span>المحددون لاستلام الكُتب</span>
                                                    <span className="font-mono font-semibold tabular-nums text-foreground">
                                                        {selectedCount}
                                                    </span>
                                                </span>
                                            )}
                                        </div>
                                    </CardTitle>
                                </CardHeader>

                                {students.length > 0 ? (
                                    <CardContent className="p-0">
                                        <Table>
                                            <TableHeader>
                                                <TableRow>
                                                    <TableHead scope="col" className="w-12 has-[[role=checkbox]]:pr-4!">
                                                        <Checkbox
                                                            role="checkbox"
                                                            aria-label="تحديد جميع الطلاب المؤهلين"
                                                            disabled={eligibleStudents.length === 0 || form.processing}
                                                            checked={allEligibleSelected ? true : someEligibleSelected ? 'indeterminate' : false}
                                                            onCheckedChange={toggleAll}
                                                        />
                                                    </TableHead>
                                                    <TableHead scope="col" className="w-24 font-mono">#</TableHead>
                                                    <TableHead scope="col">رقم الطالب</TableHead>
                                                    <TableHead scope="col">اسم الطالب</TableHead>
                                                    <TableHead scope="col" className="text-center">الفصل الدراسي</TableHead>
                                                    <TableHead scope="col" className="text-center">حالة توزيع الكُتب</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {students.map((student, index) => {
                                                    const isSelected = form.data.student_ids.includes(student.id);

                                                    return (
                                                        <TableRow key={student.uuid} data-state={isSelected ? 'selected' : undefined}>
                                                            <TableCell className="has-[[role=checkbox]]:pr-4!">
                                                                <Checkbox
                                                                    id={`student-${student.id}`}
                                                                    role="checkbox"
                                                                    aria-label={`تحديد الطالب ${student.full_name}`}
                                                                    disabled={student.already_distributed || form.processing}
                                                                    checked={isSelected}
                                                                    onCheckedChange={(value) => toggleStudent(student.id, value === true)}
                                                                />
                                                            </TableCell>
                                                            <TableCell className="font-mono">{index + 1}</TableCell>
                                                            <TableCell className="font-mono">{student.number}</TableCell>
                                                            <TableCell>
                                                                <Label
                                                                    htmlFor={`student-${student.id}`}
                                                                    className={
                                                                        student.already_distributed
                                                                            ? 'font-normal text-muted-foreground'
                                                                            : 'cursor-pointer font-normal'
                                                                    }
                                                                >
                                                                    <div>{student.full_name}</div>
                                                                    <div className="mt-2 text-xs text-muted-foreground">
                                                                        <span>الجنس:</span>
                                                                        <span className="ms-1">{student.gender.name}</span>
                                                                    </div>
                                                                </Label>
                                                            </TableCell>
                                                            <TableCell className="text-center">
                                                                <TableCellNullableValue value={student.classroomName} className="font-mono" />
                                                            </TableCell>
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
                                                    );
                                                })}
                                            </TableBody>
                                        </Table>
                                    </CardContent>
                                ) : (
                                    <CardContent>
                                        <EmptyState hasFilter={hasFilter} />
                                    </CardContent>
                                )}

                                {can.distribute && students.length > 0 && (
                                    <CardFooter className="justify-end border-t">
                                        <Button
                                            type="button"
                                            disabled={selectedCount === 0 || form.processing}
                                            onClick={() => setConfirmOpen(true)}
                                        >
                                            <BookCheckIcon />
                                            <span>توزيع الكُتب للطلاب المحددين</span>
                                        </Button>

                                        <ConfirmBookDistributionDialog
                                            open={confirmOpen}
                                            onOpenChange={setConfirmOpen}
                                            context="school"
                                            gradeLevelName={selectedGradeLevelName}
                                            classroomName={selectedClassroomName}
                                            selectedCount={selectedCount}
                                            processing={form.processing}
                                            onConfirm={confirmDistribution}
                                        />
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
            title: 'توزيع الكُتب المدرسية',
            href: index.url(),
        },
    ],
});

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
                            {Array.from({ length: 3 }).map((_, index) => (
                                <Skeleton key={index} className="h-10 w-full" />
                            ))}
                        </div>
                    </CardContent>
                    <CardFooter className="border-t">
                        <Skeleton className="h-10 w-36" />
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
