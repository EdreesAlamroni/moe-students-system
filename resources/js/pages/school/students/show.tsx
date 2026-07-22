import React from 'react'

import { Head, Link, usePage } from "@inertiajs/react";

import type { CanPermissions, Classroom, GradeLevel, Paginated, Student, StudentTransfer } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardFooter, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { Table, TableHeader, TableBody, TableRow, TableHead, TableCell, TableCellNullableValue } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alerts/alert';

import { Button } from "@/components/ui/actions/button";

import { Paginator } from "@/components/ui/navigation/paginator";

import { EnrollInClassroom, EnrollInGradeLevel } from "@/components/features/school/students/enroll-student";
import { StudentClassroomField, StudentGradeLevelField } from "@/components/shared/students/student-enrollment-fields";
import TransferStudentOut from "@/components/shared/students/transfer-student-out";

import { ArrowRightLeftIcon, BookUserIcon, CircleAlertIcon, FileTextIcon, NotepadTextIcon, SquarePenIcon } from "lucide-react";

import { index, show, edit } from "@/routes/school/students";

type PageProps = {
    student: Student;
    gradeLevels?: GradeLevel[]
    classrooms?: Classroom[];
    transfers: Paginated<StudentTransfer>;
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({ student, gradeLevels, classrooms, transfers, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    const { data: transfersData, links: transfersLinks, ...transfersMeta } = transfers;

    const transfersHasPagination = transfersData.length > 0 && transfersMeta.last_page > 1;

    return (
        <>
            <Head title="عرض بيانات الطالب" />

            <MainContainer changeAcademicYearNotice>
                {(!student.has_enrollment && currentAcademicYear?.is_active) && (
                    <section>
                        <Alert variant="info">
                            <CircleAlertIcon />
                            <AlertTitle>
                                تنويه مهم
                            </AlertTitle>
                            <AlertDescription>
                                يرجى تسجيل الطالب في الصف الدراسي للسنة الدراسية الحالية لتتمكن من إضافة سجله الدراسي.
                            </AlertDescription>
                        </Alert>
                    </section>
                )}

                {canAny && (
                    <ActionsSection>
                        {can.enrollInGradeLevel && (
                            <EnrollInGradeLevel
                                student={student}
                                gradeLevels={gradeLevels}
                            />
                        )}

                        {can.enrollInClassroom && (
                            <EnrollInClassroom
                                student={student}
                                classrooms={classrooms}
                            />
                        )}

                        {can.viewAcademicRecord && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href="#">
                                    <FileTextIcon />
                                    <span>السجل الدراسي</span>
                                </Link>
                            </Button>
                        )}


                        {can.viewPsychosocialCard && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href="#">
                                    <BookUserIcon />
                                    <span>البطاقة الإجتماعية والنفسية</span>
                                </Link>
                            </Button>
                        )}

                        {can.update && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href={edit.url({ student: student })}>
                                    <SquarePenIcon />
                                    <span>تعديل بيانات الطالب</span>
                                </Link>
                            </Button>
                        )}

                        {(can.transferStudentOut) && (
                            <TransferStudentOut
                                student={student}
                                context="school"
                            />
                        )}
                    </ActionsSection>
                )}

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات الطالب</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <DetailFields columns={2}>
                                <StudentGradeLevelField student={student} />

                                <StudentClassroomField student={student} />

                                <DetailField>
                                    <DetailLabel>صفة القيد</DetailLabel>
                                    <DetailValue value={student.registration_status.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>صفة قيد الإمتحانات</DetailLabel>
                                    <DetailValue value={student.exam_enrollment_status?.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الاسم الأول للطالب</DetailLabel>
                                    <DetailValue value={student.first_name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الاسم الأب للطالب</DetailLabel>
                                    <DetailValue value={student.father_name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>اسم الجد للطالب</DetailLabel>
                                    <DetailValue value={student.grandfather_name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>اللقب للطالب</DetailLabel>
                                    <DetailValue value={student.surname} />
                                </DetailField>

                                <DetailField className="col-span-full">
                                    <DetailLabel>اسم الأم</DetailLabel>
                                    <DetailValue value={student.mother_name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الجنسية</DetailLabel>
                                    <DetailValue value={student.nationality?.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>رقم جواز السفر</DetailLabel>
                                    <DetailValue value={student.passport_number} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الجنس</DetailLabel>
                                    <DetailValue value={student.gender.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>تاريخ الميلاد</DetailLabel>
                                    <DetailValue value={student.date_of_birth} className="font-mono" />
                                </DetailField>

                                {student.is_libyan && (
                                    <>

                                        <DetailField>
                                            <DetailLabel>الرقم الوطني</DetailLabel>
                                            <DetailValue value={student.national_id} className="font-mono" />
                                        </DetailField>

                                        <DetailField>
                                            <DetailLabel>رقم القيد</DetailLabel>
                                            <DetailValue value={student.family_registration_number} className="font-mono" />
                                        </DetailField>
                                    </>
                                )}
                            </DetailFields>
                        </CardContent>
                    </Card>
                </section>

                <section>
                    <section>
                        <Card>
                            <CardHeader className="border-b">
                                <CardTitle>
                                    <ArrowRightLeftIcon />
                                    <div className="flex items-center gap-x-1.5">
                                        <span>سجل عمليات النقل</span>
                                        <span className="font-mono">({transfersMeta.total})</span>
                                    </div>
                                </CardTitle>
                            </CardHeader>
                            {transfersData.length > 0 ? (
                                <CardTableContent>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead scope="col" className="w-24 font-mono">#</TableHead>
                                                <TableHead scope="col">المدرسة المغادر منها</TableHead>
                                                <TableHead scope="col">المدرسة الملتحق بها</TableHead>
                                                <TableHead scope="col" className="text-center">السنة الدراسية عند المغادرة</TableHead>
                                                <TableHead scope="col" className="text-center">السنة الدراسية عند الالتحاق</TableHead>
                                                <TableHead scope="col" className="text-center">تاريخ المغادرة</TableHead>
                                                <TableHead scope="col" className="text-center">تاريخ الالتحاق</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {transfersData.map((transfer, index) => (
                                                <TableRow key={transfer.uuid}>
                                                    <TableCell className="font-mono">{index + 1}</TableCell>
                                                    <TableCell>
                                                        <TableCellNullableValue value={transfer.from_school.name} />
                                                        {transfer.from_school?.monitor && (
                                                            <div className="mt-2 text-xs text-muted-foreground">
                                                                <span>{transfer.from_school?.monitor?.name}</span>
                                                            </div>
                                                        )}
                                                    </TableCell>
                                                    <TableCell>
                                                        <TableCellNullableValue value={transfer.to_school?.name} />
                                                        {transfer.to_school?.monitor && (
                                                            <div className="mt-2 text-xs text-muted-foreground">
                                                                <span>{transfer.to_school?.monitor?.name}</span>
                                                            </div>
                                                        )}
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        <TableCellNullableValue value={transfer.left_academic_year.name} className="font-mono" />
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        <TableCellNullableValue value={transfer.joined_academic_year?.name} className="font-mono" />
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        <TableCellNullableValue value={transfer.left_school_at} className="font-mono" />
                                                    </TableCell>
                                                    <TableCell className="text-center">
                                                        <TableCellNullableValue value={transfer.joined_school_at} className="font-mono" />
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
                            {transfersHasPagination && (
                                <CardFooter className="border-t">
                                    <Paginator
                                        links={transfersLinks}
                                        meta={transfersMeta}
                                    />
                                </CardFooter>
                            )}
                        </Card>
                    </section>
                </section>
            </MainContainer>
        </>
    );
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: index.url(),
        },
        {
            title: 'عرض بيانات الطالب',
            href: show.url({ student: props.student }),
        },
    ],
});
