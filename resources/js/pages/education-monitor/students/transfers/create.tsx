import React, { useEffect, useState } from 'react';

import { Form, Head, Link, useForm } from '@inertiajs/react';

import { codeInputConstraints, libyanNationalIdInputConstraints } from '@/lib/input-constraints';

import type { Student } from '@/types';

import MainContainer from '@/components/ui/structure/main-container';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/structure/card';
import { FormLayout } from '@/components/ui/structure/form-layout';

import { Table, TableBody, TableCell, TableCellActions, TableCellNullableValue, TableHead, TableHeader, TableRow } from '@/components/ui/display/table';
import EmptyState from '@/components/ui/display/empty-state';

import { Checkbox } from '@/components/ui/controls/checkbox';
import { Input } from '@/components/ui/controls/input';
import { Label } from '@/components/ui/controls/label';

import { Button } from '@/components/ui/actions/button';
import { ConfirmButton, CreateButton } from '@/components/ui/actions/submit-button';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alerts/alert-dialog';
import ValidationErrors from '@/components/ui/alerts/validation-errors';

import StudentDetailsDialog from '@/components/shared/students/student-details-dialog';

import FunnelIcon from '@/components/ui/icons/funnel-icon';
import { InfoIcon, ListIcon, RefreshCcwIcon, ReplyIcon, SearchIcon } from 'lucide-react';

import { index as studentsIndex } from '@/routes/education-monitor/students';
import { create, store } from '@/routes/education-monitor/students/transfers';

type PageProps = {
    students: Student[];
    filter: {
        name?: string;
        passport_number?: string;
        national_id?: string;
        family_registration_number?: string;
    };
};

const pageTitle = 'إضافة طالب مُنتقل';

export default function Create({ students, filter }: PageProps) {
    const [confirmOpen, setConfirmOpen] = useState(false);

    const form = useForm<{ student_ids: number[] }>({ student_ids: [] });

    const hasFilter = Object.values(filter).some(Boolean);
    const selectedIds = form.data.student_ids;
    const selectedCount = selectedIds.length;
    const allSelected = students.length > 0 && selectedCount === students.length;
    const someSelected = selectedCount > 0 && !allSelected;

    // Reset selection whenever search results change so stale IDs cannot be submitted.
    useEffect(() => {
        form.setData('student_ids', []);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [students]);

    function toggleAll(checked: boolean | 'indeterminate'): void {
        form.setData('student_ids', checked === true ? students.map((student) => student.id) : []);
    }

    function toggleStudent(studentId: number, checked: boolean): void {
        form.setData(
            'student_ids',
            checked ? [...selectedIds, studentId] : selectedIds.filter((id) => id !== studentId),
        );
    }

    function confirmTransfer(): void {
        form.post(store.url(), {
            preserveScroll: true,
            onFinish: () => setConfirmOpen(false),
        });
    }

    return (
        <>
            <Head title={pageTitle} />

            <MainContainer>
                <Alert>
                    <InfoIcon />
                    <AlertTitle>البحث عن الطلاب أولاً</AlertTitle>
                    <AlertDescription>
                        ابحث عن الطلاب، ثم اختر طالباً واحداً أو أكثر من القائمة. اضغط على «إضافة» لإضافة الطلاب
                        المحددين إلى المُراقبة. إذا لم تظهر أي نتائج، فقد يكون الطلاب مُسجلين بالفعل في مُراقبة أخرى،
                        أو أن بيانات البحث غير صحيحة.
                    </AlertDescription>
                </Alert>

                <FormLayout>
                    <section>
                        <Form {...create.form()}>
                            <Card>
                                <CardHeader className="border-b">
                                    <CardTitle>
                                        <FunnelIcon />
                                        <span>بحث عن طلاب مُنتقلين</span>
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                        <Input
                                            type="text"
                                            name="filter[name]"
                                            defaultValue={filter.name}
                                            placeholder="اسم الطالب"
                                            autoComplete="off"
                                        />

                                        <Input
                                            type="text"
                                            name="filter[passport_number]"
                                            defaultValue={filter.passport_number}
                                            placeholder="رقم جواز السفر"
                                            className="not-placeholder-shown:font-mono"
                                            autoComplete="off"
                                            lang="en"
                                            {...codeInputConstraints()}
                                        />

                                        <Input
                                            type="text"
                                            name="filter[national_id]"
                                            defaultValue={filter.national_id}
                                            placeholder="الرقم الوطني"
                                            className="not-placeholder-shown:font-mono"
                                            autoComplete="off"
                                            {...libyanNationalIdInputConstraints()}
                                        />

                                        <Input
                                            type="text"
                                            name="filter[family_registration_number]"
                                            defaultValue={filter.family_registration_number}
                                            placeholder="رقم القيد"
                                            className="not-placeholder-shown:font-mono"
                                            autoComplete="off"
                                            lang="en"
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
                                            <Link href={create.url()}>
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
                        <ValidationErrors errors={form.errors} />

                        <Card className={students.length > 0 ? 'gap-0' : ''}>
                            <CardHeader className="border-b">
                                <CardTitle className="min-w-0 flex-1">
                                    <ListIcon />
                                    <div className="flex min-w-0 flex-1 items-center justify-between gap-x-4">
                                        <div className="flex items-center gap-x-1.5">
                                            <span>قائمة الطلاب</span>
                                            {students.length > 0 && (
                                                <span className="font-mono">({students.length})</span>
                                            )}
                                        </div>

                                        {selectedCount > 0 && (
                                            <span
                                                className="inline-flex shrink-0 items-center gap-x-2 text-xs font-normal normal-case tracking-normal"
                                                aria-live="polite"
                                            >
                                                <span className="text-muted-foreground">الطلاب المحددون</span>
                                                <span className="font-mono tabular-nums text-foreground">
                                                    {selectedCount}
                                                </span>
                                            </span>
                                        )}
                                    </div>
                                </CardTitle>
                            </CardHeader>

                            {students.length > 0 ? (
                                <CardContent className="px-0">
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead scope="col" className="w-12 [&:has([role=checkbox])]:pr-4">
                                                    <Checkbox
                                                        aria-label="تحديد جميع الطلاب"
                                                        checked={allSelected ? true : someSelected ? 'indeterminate' : false}
                                                        disabled={form.processing}
                                                        onCheckedChange={toggleAll}
                                                    />
                                                </TableHead>
                                                <TableHead scope="col">اسم الطالب</TableHead>
                                                <TableHead scope="col">الجنسية</TableHead>
                                                <TableHead scope="col">الرقم الوطني</TableHead>
                                                <TableHead scope="col">الصف الدراسي</TableHead>
                                                <TableHead scope="col" />
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {students.map((student) => {
                                                const isSelected = selectedIds.includes(student.id);

                                                return (
                                                    <TableRow
                                                        key={student.uuid}
                                                        data-state={isSelected ? 'selected' : undefined}
                                                    >
                                                        <TableCell className="[&:has([role=checkbox])]:pr-4">
                                                            <Checkbox
                                                                id={`student-${student.id}`}
                                                                aria-label={`تحديد الطالب ${student.full_name}`}
                                                                checked={isSelected}
                                                                disabled={form.processing}
                                                                onCheckedChange={(value) =>
                                                                    toggleStudent(student.id, value === true)
                                                                }
                                                            />
                                                        </TableCell>
                                                        <TableCell>
                                                            <Label
                                                                htmlFor={`student-${student.id}`}
                                                                className="cursor-pointer font-normal"
                                                            >
                                                                {student.full_name}
                                                            </Label>
                                                            <div className="mt-2 text-xs text-muted-foreground">
                                                                <span>الجنس:</span>
                                                                <span className="ms-1">{student.gender.name}</span>
                                                            </div>
                                                        </TableCell>
                                                        <TableCell>
                                                            <TableCellNullableValue value={student.nationality?.name} />
                                                        </TableCell>
                                                        <TableCell>
                                                            {student.is_libyan ? (
                                                                <div className="font-mono">{student.national_id}</div>
                                                            ) : (
                                                                <div className="font-sans text-muted-foreground">أجنبي</div>
                                                            )}

                                                            {student.is_libyan ? (
                                                                <div className="mt-1.5 text-xs text-muted-foreground">
                                                                    <span>رقم القيد:</span>
                                                                    <span className="ms-1 font-mono">
                                                                        {student.family_registration_number}
                                                                    </span>
                                                                </div>
                                                            ) : (
                                                                student.passport_number && (
                                                                    <div className="mt-1.5 text-xs text-muted-foreground">
                                                                        <span>رقم جواز السفر:</span>
                                                                        <span className="ms-1 font-mono">
                                                                            {student.passport_number}
                                                                        </span>
                                                                    </div>
                                                                )
                                                            )}
                                                        </TableCell>
                                                        <TableCell>
                                                            <TableCellNullableValue value={student.grade_level?.name} />
                                                        </TableCell>
                                                        <TableCellActions>
                                                            <StudentDetailsDialog
                                                                student={student}
                                                                context="education-monitor"
                                                            />
                                                        </TableCellActions>
                                                    </TableRow>
                                                );
                                            })}
                                        </TableBody>
                                    </Table>
                                </CardContent>
                            ) : (
                                <CardContent>
                                    <EmptyState
                                        hasFilter={hasFilter}
                                        text="لم يتم البحث عن طلاب بعد."
                                    />
                                </CardContent>
                            )}

                            <CardFooter className="justify-end gap-x-4 border-t">
                                <Button variant="outline" className="flex items-center gap-x-2" asChild>
                                    <Link href={studentsIndex.url()}>
                                        <ReplyIcon />
                                        <span>إلغاء الأمر</span>
                                    </Link>
                                </Button>

                                <CreateButton
                                    type="button"
                                    disabled={selectedCount === 0 || form.processing}
                                    onClick={() => setConfirmOpen(true)}
                                />

                                <AlertDialog open={confirmOpen} onOpenChange={setConfirmOpen}>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>هل أنت متأكد من الإجراء ؟</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                <span>سيتم إضافة</span>
                                                <strong className="mx-1 inline-block font-mono">{selectedCount}</strong>
                                                <span>طالب/طالبة إلى المُراقبة، هل أنت متأكد من هذا الإجراء ؟</span>
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel type="button" disabled={form.processing}>
                                                إلغاء الأمر
                                            </AlertDialogCancel>
                                            <ConfirmButton
                                                type="button"
                                                processing={form.processing}
                                                disabled={form.processing}
                                                onClick={confirmTransfer}
                                            />
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </CardFooter>
                        </Card>
                    </section>
                </FormLayout>
            </MainContainer>
        </>
    );
}

Create.layout = () => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: studentsIndex.url(),
        },
        {
            title: pageTitle,
            href: create.url(),
        },
    ],
});
