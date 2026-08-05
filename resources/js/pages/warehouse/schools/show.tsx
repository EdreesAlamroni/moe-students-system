import React from 'react'

import { Head } from "@inertiajs/react";

import type { School } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { StatCardsSection } from "@/components/ui/display/stat-card";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { SchoolPeriodsSummary } from '@/components/shared/schools/school-periods-summary';

import { GraduationCapIcon, PresentationIcon, NotepadTextIcon, UsersIcon } from "lucide-react";

import { index, show } from "@/routes/warehouse/schools";

type PageProps = {
    school: School;
}

export default function Show({ school }: PageProps) {
    const isPrivate = school.is_private === true;
    const hasOffice = !!school.office;

    return (
        <>
            <Head title="عرض بيانات المدرسة" />

            <MainContainer showAcademicYearNotice>
                <StatCardsSection
                    items={[
                        { label: "الصفوف الدراسية", value: school.grade_levels_count || 0, icon: GraduationCapIcon },
                        { label: "الفصول الدراسية", value: school.classrooms_count || 0, icon: PresentationIcon },
                        { label: "الطلاب", value: school.students_count || 0, icon: UsersIcon },
                    ]}
                    columns={3}
                />

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات المدرسة</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <DetailFields columns={2}>
                                <DetailField className={hasOffice ? undefined : "col-span-full"}>
                                    <DetailLabel>المُراقبة</DetailLabel>
                                    <DetailValue value={school.monitor?.name} />
                                </DetailField>

                                {hasOffice && (
                                    <DetailField>
                                        <DetailLabel>مكتب الخدمات التعليمية</DetailLabel>
                                        <DetailValue value={school.office?.name} />
                                    </DetailField>
                                )}

                                <DetailField>
                                    <DetailLabel>اسم المدرسة</DetailLabel>
                                    <DetailValue value={school.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الرقم التسلسلي</DetailLabel>
                                    <DetailValue value={school.serial_number} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>نوع المدرسة</DetailLabel>
                                    <DetailValue value={school.type?.name} />
                                </DetailField>

                                {/* TODO: Review this component and refactor it. */}
                                <DetailField>
                                    <DetailLabel>الفترات الدراسية</DetailLabel>
                                    <DetailValue>
                                        <SchoolPeriodsSummary
                                            periods={school.periods ?? []}
                                            showStudentCounts
                                        />
                                    </DetailValue>
                                </DetailField>

                                {isPrivate && (
                                    <DetailField className="col-span-full">
                                        <DetailLabel>اسم الشركة التعليمية</DetailLabel>
                                        <DetailValue value={school.educational_company_name} />
                                    </DetailField>
                                )}

                                {isPrivate && (
                                    <>
                                        <DetailField>
                                            <DetailLabel>فرع المدرسة</DetailLabel>
                                            <DetailValue value={school.branch_type?.name} />
                                        </DetailField>

                                        <DetailField>
                                            <DetailLabel>نوع المبنى</DetailLabel>
                                            <DetailValue value={school.building_type?.name} />
                                        </DetailField>
                                    </>
                                )}
                            </DetailFields>
                        </CardContent>
                    </Card>
                </section>
            </MainContainer>
        </>
    )
}

Show.layout = (props: PageProps) => ({
    breadcrumbs: [
        {
            title: 'المدارس',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المدرسة',
            href: show.url({ school: props.school }),
        },
    ],
});
