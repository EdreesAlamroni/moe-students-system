import React from 'react';

import { useForm } from '@inertiajs/react';

import type {
    ClassroomDistributionMethod,
    ClassroomRow,
    ListStudent,
} from '@/types/classroom-distribution';

import {
    Card,
    CardContent,
    CardDescription,
    CardFormFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/structure/card';

import EmptyState from '@/components/ui/display/empty-state';
import { Badge } from '@/components/ui/display/badge';
import { LoadingData } from '@/components/ui/display/loading-data';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/display/table';

import Field from '@/components/ui/controls/field';
import { Input } from '@/components/ui/controls/input';
import { Label } from '@/components/ui/controls/label';
import { Checkbox } from '@/components/ui/controls/checkbox';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/controls/select';

import {
    Alert,
    AlertDescription,
    AlertTitle,
} from '@/components/ui/alerts/alert';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alerts/alert-dialog';
import ValidationErrors from '@/components/ui/alerts/validation-errors';

import { Button } from '@/components/ui/actions/button';
import { ConfirmButton } from '@/components/ui/actions/submit-button';

import {
    AlertTriangleIcon,
    CheckCircle2Icon,
    SearchIcon,
    UserPlusIcon,
    UsersIcon,
} from 'lucide-react';

type ManualDistributionSectionProps = {
    method: ClassroomDistributionMethod;
    classrooms: ClassroomRow[];
    unassignedStudents: ListStudent[];
    selectedGradeLevelId: number;
    formsDisabled: boolean;
    loadingStudents: boolean;
};

function normalizeSearchQuery(query: string): string {
    return query.trim().toLocaleLowerCase('ar');
}

export default function ManualDistributionSection({
    method,
    classrooms,
    unassignedStudents,
    selectedGradeLevelId,
    formsDisabled,
    loadingStudents,
}: ManualDistributionSectionProps) {
    const form = useForm({
        grade_level_id: selectedGradeLevelId,
        student_ids: [] as number[],
        classroom_id: '',
    });

    const [dialogOpen, setDialogOpen] = React.useState(false);
    const [searchQuery, setSearchQuery] = React.useState('');

    const filteredStudents = React.useMemo(() => {
        const normalizedQuery = normalizeSearchQuery(searchQuery);

        if (normalizedQuery === '') {
            return unassignedStudents;
        }

        return unassignedStudents.filter((student) =>
            student.full_name.toLocaleLowerCase('ar').includes(normalizedQuery),
        );
    }, [unassignedStudents, searchQuery]);

    const toggleStudent = (studentId: number, checked: boolean): void => {
        const ids = form.data.student_ids;

        if (checked) {
            form.setData('student_ids', [...ids, studentId]);
        } else {
            form.setData(
                'student_ids',
                ids.filter((id) => id !== studentId),
            );
        }
    };

    const filteredStudentIds = React.useMemo(
        () => filteredStudents.map((student) => student.id),
        [filteredStudents],
    );

    const allFilteredSelected =
        filteredStudents.length > 0 &&
        filteredStudentIds.every((id) => form.data.student_ids.includes(id));

    const someFilteredSelected =
        filteredStudentIds.some((id) => form.data.student_ids.includes(id)) &&
        !allFilteredSelected;

    const toggleAll = (checked: boolean | 'indeterminate'): void => {
        if (checked === true) {
            form.setData('student_ids', [
                ...new Set([...form.data.student_ids, ...filteredStudentIds]),
            ]);
        } else {
            form.setData(
                'student_ids',
                form.data.student_ids.filter(
                    (id) => !filteredStudentIds.includes(id),
                ),
            );
        }
    };

    const selectedClassroom = classrooms.find(
        (classroom) => String(classroom.id) === form.data.classroom_id,
    );
    const selectedStudentCount = form.data.student_ids.length;
    const willExceedCapacity =
        selectedClassroom !== undefined &&
        selectedStudentCount > selectedClassroom.remaining_capacity;
    const overflowCount = selectedClassroom
        ? Math.max(
            0,
            selectedStudentCount - selectedClassroom.remaining_capacity,
        )
        : 0;

    const canOpenDialog =
        !formsDisabled &&
        unassignedStudents.length > 0 &&
        selectedStudentCount > 0 &&
        form.data.classroom_id !== '';

    const handleSubmit = (): void => {
        form.post(method.route, {
            preserveScroll: true,
            onFinish: () => setDialogOpen(false),
        });
    };

    return (
        <section>
            <Card>
                <CardHeader className="border-b">
                    <CardTitle>
                        <UserPlusIcon />
                        <span>توزيع يدوي</span>
                    </CardTitle>
                    <CardDescription>
                        اختر الطلاب ثم الفصل المستهدف. يُعرض فقط الطلاب
                        المسجّلين في هذا الصف الدراسي دون تعيين فصل دراسي للسنة
                        الدراسية الحالية.
                    </CardDescription>
                </CardHeader>
                <CardContent className="flex flex-col gap-6">
                    <ValidationErrors errors={form.errors} />

                    <div className="space-y-4">
                        <div className="flex flex-wrap items-center justify-between gap-x-3 gap-y-2">
                            <div className="flex items-center gap-x-2 text-sm font-medium">
                                <UsersIcon className="h-4 w-4 shrink-0" />
                                <span>قائمة الطلاب</span>
                                {!loadingStudents &&
                                    unassignedStudents.length > 0 && (
                                        <Badge
                                            variant="secondary"
                                            className="font-mono tabular-nums"
                                        >
                                            {unassignedStudents.length}
                                        </Badge>
                                    )}
                            </div>

                            {selectedStudentCount > 0 && (
                                <Badge
                                    variant="success"
                                    className="gap-1 font-normal"
                                >
                                    <span>المحددون</span>
                                    <span className="font-mono tabular-nums">
                                        {selectedStudentCount}
                                    </span>
                                </Badge>
                            )}
                        </div>

                        {loadingStudents ? (
                            <LoadingData className="py-6" />
                        ) : unassignedStudents.length === 0 ? (
                            <EmptyState
                                text="لا يوجد طلاب مسجّلون في هذا الصف الدراسي بانتظار تعيين فصل دراسي للسنة الدراسية الحالية."
                            />
                        ) : (
                            <>
                                <div className="relative max-w-md">
                                    <SearchIcon className="pointer-events-none absolute inset-s-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        type="search"
                                        value={searchQuery}
                                        onChange={(event) =>
                                            setSearchQuery(event.target.value)
                                        }
                                        placeholder="بحث بالاسم..."
                                        className="ps-9"
                                        disabled={formsDisabled}
                                        aria-label="بحث الطلاب بالاسم"
                                    />
                                </div>

                                {filteredStudents.length === 0 ? (
                                    <EmptyState text="لا توجد نتائج مطابقة لبحثك." />
                                ) : (
                                    <Table className="border">
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead
                                                    scope="col"
                                                    className="w-12 has-[[role=checkbox]]:pr-4!"
                                                >
                                                    <Checkbox
                                                        role="checkbox"
                                                        aria-label="تحديد جميع الطلاب"
                                                        checked={
                                                            allFilteredSelected
                                                                ? true
                                                                : someFilteredSelected
                                                                    ? 'indeterminate'
                                                                    : false
                                                        }
                                                        onCheckedChange={
                                                            toggleAll
                                                        }
                                                        disabled={formsDisabled}
                                                    />
                                                </TableHead>
                                                <TableHead
                                                    scope="col"
                                                    className="w-24 font-mono"
                                                >
                                                    #
                                                </TableHead>
                                                <TableHead scope="col">
                                                    رقم الطالب
                                                </TableHead>
                                                <TableHead scope="col">
                                                    اسم الطالب
                                                </TableHead>
                                                <TableHead scope="col">
                                                    الجنس
                                                </TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {filteredStudents.map(
                                                (
                                                    student: ListStudent,
                                                    index: number,
                                                ) => {
                                                    const isSelected =
                                                        form.data.student_ids.includes(
                                                            student.id,
                                                        );

                                                    return (
                                                        <TableRow
                                                            key={student.uuid}
                                                            data-state={
                                                                isSelected
                                                                    ? 'selected'
                                                                    : undefined
                                                            }
                                                        >
                                                            <TableCell className="has-[[role=checkbox]]:pr-4">
                                                                <Checkbox
                                                                    id={`student-${student.id}`}
                                                                    role="checkbox"
                                                                    aria-label={`تحديد الطالب ${student.full_name}`}
                                                                    checked={
                                                                        isSelected
                                                                    }
                                                                    onCheckedChange={(
                                                                        value,
                                                                    ) =>
                                                                        toggleStudent(
                                                                            student.id,
                                                                            value ===
                                                                            true,
                                                                        )
                                                                    }
                                                                    disabled={
                                                                        formsDisabled
                                                                    }
                                                                />
                                                            </TableCell>
                                                            <TableCell className="font-mono tabular-nums">
                                                                {index + 1}
                                                            </TableCell>
                                                            <TableCell className="font-mono tabular-nums">
                                                                {student.number}
                                                            </TableCell>
                                                            <TableCell>
                                                                <Label
                                                                    htmlFor={`student-${student.id}`}
                                                                    className="cursor-pointer font-normal"
                                                                >
                                                                    {
                                                                        student.full_name
                                                                    }
                                                                </Label>
                                                            </TableCell>
                                                            <TableCell className="text-muted-foreground">
                                                                {
                                                                    student
                                                                        .gender
                                                                        .name
                                                                }
                                                            </TableCell>
                                                        </TableRow>
                                                    );
                                                },
                                            )}
                                        </TableBody>
                                    </Table>
                                )}
                            </>
                        )}
                    </div>

                    {!loadingStudents && unassignedStudents.length > 0 && (
                        <Field className="max-w-md">
                            <Label htmlFor="manual_classroom_select">
                                الفصل الدراسي المستهدف
                            </Label>
                            <Select
                                value={form.data.classroom_id || undefined}
                                onValueChange={(value) =>
                                    form.setData('classroom_id', value)
                                }
                                disabled={
                                    formsDisabled || classrooms.length === 0
                                }
                            >
                                <SelectTrigger id="manual_classroom_select">
                                    <SelectValue placeholder="اختر الفصل الدراسي" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        {classrooms.map(
                                            (classroom: ClassroomRow) => (
                                                <SelectItem
                                                    key={classroom.id}
                                                    value={String(classroom.id)}
                                                >
                                                    <span className="font-mono">
                                                        {classroom.name}
                                                    </span>
                                                    {' — '}
                                                    <span className="text-xs text-muted-foreground">
                                                        مقاعد متاحة:{' '}
                                                        {
                                                            classroom.remaining_capacity
                                                        }
                                                    </span>
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                        </Field>
                    )}
                </CardContent>
                <CardFormFooter>
                    <Button
                        type="button"
                        variant="default"
                        disabled={!canOpenDialog}
                        onClick={() => setDialogOpen(true)}
                    >
                        <CheckCircle2Icon />
                        <span>مراجعة وتأكيد التوزيع</span>
                    </Button>

                    <AlertDialog open={dialogOpen} onOpenChange={setDialogOpen}>
                        <AlertDialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    تأكيد التعيين اليدوي
                                </AlertDialogTitle>
                                <AlertDialogDescription asChild>
                                    <div className="space-y-4 text-right text-sm text-foreground">
                                        <p>
                                            الفصل الدراسي المستهدف:{' '}
                                            <span className="font-mono font-medium">
                                                {selectedClassroom?.name}
                                            </span>
                                        </p>
                                        <div className="space-y-2 rounded-none border border-border bg-muted/50 p-4">
                                            <p className="text-sm font-medium text-foreground">
                                                مُلخص
                                            </p>
                                            <ul className="space-y-2 text-xs text-muted-foreground">
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        عدد الطلاب المحددين
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {selectedStudentCount}
                                                    </span>
                                                </li>
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        المقاعد المتاحة في الفصل
                                                        الدراسي
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {selectedClassroom?.remaining_capacity ??
                                                            0}
                                                    </span>
                                                </li>
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        عدد الطلاب الحالي في
                                                        الفصل الدراسي
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {selectedClassroom?.students_count ??
                                                            0}
                                                    </span>
                                                </li>
                                                <li className="flex flex-wrap justify-between gap-2">
                                                    <span>
                                                        سعة الفصل الدراسي
                                                    </span>
                                                    <span className="font-mono text-foreground tabular-nums">
                                                        {selectedClassroom?.capacity ??
                                                            0}
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>

                                        {willExceedCapacity ? (
                                            <Alert
                                                variant="warning"
                                                className="text-right"
                                            >
                                                <AlertTriangleIcon />
                                                <AlertTitle>
                                                    تنبيه السعة الإجمالية
                                                </AlertTitle>
                                                <AlertDescription>
                                                    <div>
                                                        عدد الطلاب المحددين
                                                        يتجاوز المقاعد المتاحة
                                                        بمقدار{' '}
                                                        <strong className="font-mono tabular-nums">
                                                            {overflowCount}
                                                        </strong>
                                                        . سيصبح عدد طلاب الفصل
                                                        الدراسي{' '}
                                                        <strong className="font-mono tabular-nums">
                                                            {(selectedClassroom?.students_count ??
                                                                0) +
                                                                selectedStudentCount}
                                                        </strong>{' '}
                                                        من أصل سعة الفصل الدراسي{' '}
                                                        <strong className="font-mono tabular-nums">
                                                            {
                                                                selectedClassroom?.capacity
                                                            }
                                                        </strong>
                                                        .
                                                    </div>
                                                </AlertDescription>
                                            </Alert>
                                        ) : (
                                            <p className="text-xs leading-relaxed text-muted-foreground">
                                                ضمن السعة المتاحة للفصل الدراسي؛
                                                لا يُتوقع تجاوز عدد المقاعد.
                                            </p>
                                        )}

                                        <p className="text-xs leading-relaxed text-muted-foreground">
                                            بالتأكيد، سيتم تعيين الطلاب المحددين
                                            في الفصل الدراسي فوراً ولا يمكن
                                            التراجع عنه من هذه الشاشة.
                                        </p>
                                    </div>
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel type="button">
                                    إلغاء الأمر
                                </AlertDialogCancel>
                                <AlertDialogAction asChild>
                                    <ConfirmButton
                                        type="button"
                                        processing={form.processing}
                                        onClick={handleSubmit}
                                    />
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardFormFooter>
            </Card>
        </section>
    );
}
