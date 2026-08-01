import React from 'react'

import { Head } from "@inertiajs/react";

import type { CanPermissions, GradeLevel } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import ActionsSection from "@/components/ui/structure/actions-section";

import { StatCardsSection } from "@/components/ui/display/stat-card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { NotepadTextIcon, PresentationIcon, UsersIcon } from "lucide-react";

import { index, show, destroy } from "@/routes/school/grade-levels";

type PageProps = {
    gradeLevel: GradeLevel;
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({ gradeLevel, canAny, can }: PageProps) {
    return (
        <>
            <Head title="عرض بيانات الصف الدراسي" />

            <MainContainer showAcademicYearNotice>
                {canAny && (
                    <ActionsSection>
                        {can.delete && (
                            <ConfirmDeleteAction
                                title="حذف الصف الدراسي"
                                href={destroy.url({ gradeLevel: gradeLevel })}
                            />
                        )}
                    </ActionsSection>
                )}

                <StatCardsSection
                    items={[
                        { label: "الفصول الدراسية", value: gradeLevel.classrooms_count ?? 0, icon: PresentationIcon },
                        { label: "الطلاب", value: gradeLevel.students_count ?? 0, icon: UsersIcon },
                    ]}
                    columns={2}
                />

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات الصف الدراسي</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <DetailFields columns={2}>
                                <DetailField>
                                    <DetailLabel>الاسم</DetailLabel>
                                    <DetailValue value={gradeLevel.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>المرحلة الدراسية</DetailLabel>
                                    <DetailValue value={gradeLevel.educational_stage.name} />
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
            title: 'الصفوف الدراسية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات الصف الدراسي',
            href: show.url({ gradeLevel: props.gradeLevel }),
        },
    ],
});
