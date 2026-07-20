import React from 'react'

import { Head } from "@inertiajs/react";

import type { Student } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { StudentClassroomField, StudentGradeLevelField } from "@/components/shared/students/student-enrollment-fields";

import { NotepadTextIcon } from "lucide-react";

import { index, show } from "@/routes/administration/students";

type PageProps = {
    student: Student;
};

export default function Show({ student }: PageProps) {
    return (
        <>
            <Head title="عرض بيانات الطالب" />

            <MainContainer showAcademicYearNotice>
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
                                <DetailField>
                                    <DetailLabel>المُراقبة</DetailLabel>
                                    <DetailValue value={student.monitor?.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>المدرسة</DetailLabel>
                                    <DetailValue value={student.school?.name} />
                                </DetailField>

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

                                <DetailField>
                                    <DetailLabel>الرقم الوطني</DetailLabel>
                                    <DetailValue value={student.national_id} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>رقم القيد</DetailLabel>
                                    <DetailValue value={student.family_registration_number} className="font-mono" />
                                </DetailField>
                            </DetailFields>
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
            href: index.url(),
        },
        {
            title: 'عرض بيانات الطالب',
            href: show.url({ student: props.student }),
        },
    ],
});
