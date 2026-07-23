import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, GroupedAcademicRecord, Student } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import { Badge } from "@/components/ui/display/badge";
import EmptyState from "@/components/ui/display/empty-state";

import { Button } from "@/components/ui/actions/button";

import AcademicRecordStudentOverview from "@/components/features/school/academic-record/academic-record-student-overview";
import AcademicRecordGradeLevelCard from "@/components/features/school/academic-record/academic-record-grade-level-card";

import { CheckCircle2Icon, FileTextIcon, GraduationCapIcon, PlusIcon } from "lucide-react";

import { index as indexStudents, show as showStudent } from "@/routes/school/students";
import { show, create } from "@/routes/school/students/academic-record";

type PageProps = {
    student: Student;
    groupedRecords: GroupedAcademicRecord[];
    requiresAcademicRecord: boolean;
    isComplete: boolean;
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({
    student,
    groupedRecords,
    requiresAcademicRecord,
    isComplete,
    canAny,
    can,
}: PageProps) {
    const hasRecords = groupedRecords.some((record) => record.attempts.length > 0);
    const completedGradeLevels = groupedRecords.filter((record) => record.attempts.length > 0).length;
    const totalGradeLevels = groupedRecords.length;

    return (
        <>
            <Head title="السجل الدراسي" />

            <MainContainer>
                <section>
                    <header className="flex items-center justify-between gap-4 border-b pb-4">
                        <div className="flex items-center gap-3">
                            <FileTextIcon className="w-4 h-4 shrink-0" />
                            <div className="flex flex-col gap-1">
                                <h1 className="text-sm font-medium text-foreground">
                                    السجل الدراسي للطالب
                                </h1>
                                <p className="text-xs text-muted-foreground">
                                    سجل الصفوف الدراسية للطالب، مع السنة الدراسية ونتيجة كل سنة، ويُستخدم لاحتساب صفة القيد.
                                </p>
                            </div>
                        </div>

                        {isComplete && (
                            <Badge className="gap-1 border-green-200 bg-green-50 text-green-700 hover:bg-green-50">
                                <CheckCircle2Icon />
                                <span>مُكتمل</span>
                            </Badge>
                        )}
                    </header>
                </section>

                {canAny && (
                    <ActionsSection>
                        {can.createAcademicRecord && (
                            <Button
                                variant="default"
                                asChild
                            >
                                <Link href={create.url({ student: student })}>
                                    <PlusIcon />
                                    <span>إنشاء السجل الدراسي</span>
                                </Link>
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <section>
                    <AcademicRecordStudentOverview
                        student={student}
                        requiresAcademicRecord={requiresAcademicRecord}
                        isComplete={isComplete}
                        completedGradeLevels={completedGradeLevels}
                        totalGradeLevels={totalGradeLevels}
                    />
                </section>

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <FileTextIcon />
                                <span>السجل الدراسي</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            {!requiresAcademicRecord && (
                                <EmptyState
                                    icon={GraduationCapIcon}
                                    text="لا يُطلب سجل دراسي"
                                    description={
                                        student.grade_level
                                            ? `الطالب مسجّل في ${student.grade_level.name} — لا توجد صفوف دراسية سابقة تستدعي إدخال سجل.`
                                            : "لا توجد صفوف دراسية سابقة تستدعي إدخال سجل لهذا الطالب."
                                    }
                                />
                            )}

                            {requiresAcademicRecord && !hasRecords && (
                                <EmptyState
                                    icon={FileTextIcon}
                                    text="لم يُنشأ السجل الدراسي بعد"
                                    description="أدخل بيانات الصفوف الدراسية السابقة لإكمال السجل وحساب صفة القيد."
                                />
                            )}

                            {hasRecords && (
                                <div className="flex flex-col">
                                    {groupedRecords
                                        .filter((record) => record.attempts.length > 0)
                                        .map((record, index, records) => (
                                            <AcademicRecordGradeLevelCard
                                                key={record.grade_level.id}
                                                record={record}
                                                isLast={index === records.length - 1}
                                            />
                                        ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </section>
            </MainContainer>
        </>
    );
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'الطلاب',
            href: indexStudents.url(),
        },
        {
            title: 'عرض بيانات الطالب',
            href: showStudent.url({ student: props.student }),
        },
        {
            title: 'السجل الدراسي',
            href: show.url({ student: props.student }),
        }
    ],
});
