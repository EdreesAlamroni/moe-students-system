import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { AcademicRecordProgress, AcademicYear, Enum, GradeLevel, GroupedAcademicRecord, Student } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import { Button } from "@/components/ui/actions/button";

import AcademicRecordStudentOverview from "@/components/features/school/academic-record/academic-record-student-overview";
import AcademicRecordGradeLevelCard from "@/components/features/school/academic-record/academic-record-grade-level-card";

import { FileTextIcon, ReplyIcon } from "lucide-react";

import { index as indexStudents, show as showStudent } from "@/routes/school/students";
import { show, create } from "@/routes/school/students/academic-record";

type PageProps = {
    student: Student;
    groupedRecords: GroupedAcademicRecord[];
    currentGradeLevel?: GradeLevel;
    selectableAcademicYears: AcademicYear[];
    academicRecordStatuses: Enum[];
    academicRecordRatings: Enum[];
    progress: AcademicRecordProgress;
};

export default function Create({
    student,
    groupedRecords,
    currentGradeLevel,
    selectableAcademicYears,
    academicRecordStatuses,
    academicRecordRatings,
    progress,
}: PageProps) {
    const visibleRecords = groupedRecords.filter(
        (record) => record.attempts.length > 0 || record.is_current,
    );

    return (
        <>
            <Head title="إنشاء السجل الدراسي" />

            <MainContainer>
                <section>
                    <header className="flex items-center gap-3 border-b pb-4">
                        <FileTextIcon className="w-4 h-4 shrink-0" />
                        <div className="flex flex-col gap-1">
                            <h1 className="text-sm font-medium text-foreground">
                                إنشاء السجل الدراسي للطالب
                            </h1>
                            <p className="text-xs text-muted-foreground">
                                أدخل بيانات الصفوف الدراسية السابقة بالترتيب حتى يكتمل السجل.
                            </p>
                        </div>
                    </header>
                </section>

                <section>
                    <AcademicRecordStudentOverview
                        student={student}
                        requiresAcademicRecord
                        isComplete={false}
                        completedGradeLevels={progress.completed}
                        totalGradeLevels={progress.total}
                    />
                </section>

                <section>
                    <Card className="ring-0 shadow-none py-0">
                        <CardHeader className="border-b px-0">
                            <CardTitle>
                                <FileTextIcon />
                                <span>مسار الصفوف الدراسية</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col">
                            {visibleRecords.map((record, index) => (
                                <AcademicRecordGradeLevelCard
                                    key={record.grade_level.id}
                                    record={record}
                                    student={student}
                                    currentGradeLevel={currentGradeLevel}
                                    selectableAcademicYears={selectableAcademicYears}
                                    academicRecordStatuses={academicRecordStatuses}
                                    academicRecordRatings={academicRecordRatings}
                                    showEntryForm
                                    isLast={index === visibleRecords.length - 1}
                                />
                            ))}
                        </CardContent>
                    </Card>
                </section>

                <section className="ms-[4.25rem]">
                    <Button variant="outline" asChild>
                        <Link
                            href={show.url({ student: student })}
                            preserveScroll
                        >
                            <ReplyIcon />
                            <span>العودة إلى السجل الدراسي</span>
                        </Link>
                    </Button>
                </section>
            </MainContainer>
        </>
    );
}

Create.layout = (props: PageProps) => ({
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
        },
        {
            title: 'إنشاء السجل الدراسي',
            href: create.url({ student: props.student }),
        }
    ],
});
