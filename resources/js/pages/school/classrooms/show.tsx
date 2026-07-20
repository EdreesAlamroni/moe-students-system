import React from 'react'

import { Head, Link, usePage } from "@inertiajs/react";

import type { CanPermissions, Classroom } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Button } from "@/components/ui/actions/button";

import { CalendarDaysIcon, NotepadTextIcon, SquarePenIcon } from "lucide-react";

import { index, show, edit } from "@/routes/school/classrooms";

type PageProps = {
    classroom: Classroom;
    canViewSchedule: boolean;
    canAny: boolean;
    can: CanPermissions;
};

export default function Show({ classroom, canViewSchedule, canAny, can }: PageProps) {
    const { currentAcademicYear } = usePage().props;

    return (
        <>
            <Head title="عرض بيانات الفصل الدراسي" />

            <MainContainer showAcademicYearNotice>

                {canAny && (
                    <ActionsSection>
                        {(can.update && currentAcademicYear?.is_active) && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href={edit.url({ classroom: classroom })}>
                                    <SquarePenIcon />
                                    <span>تعديل بيانات الفصل الدراسي</span>
                                </Link>
                            </Button>
                        )}

                        {canViewSchedule && (
                            <Button
                                variant="outline"
                                asChild
                            >
                                <Link href="#">
                                    <CalendarDaysIcon />
                                    <span>جدول الحصص الدراسية</span>
                                </Link>
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات الفصل الدراسي</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="flex flex-col gap-6">
                            <DetailFields columns={2}>
                                <DetailField>
                                    <DetailLabel>المرحلة الدراسية</DetailLabel>
                                    <DetailValue value={classroom.grade_level?.educational_stage.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الصف الدراسي</DetailLabel>
                                    <DetailValue value={classroom.grade_level?.name} />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>اسم الفصل الدراسي</DetailLabel>
                                    <DetailValue value={classroom.name} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>السعة</DetailLabel>
                                    <DetailValue value={classroom.capacity} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>عدد الطلاب</DetailLabel>
                                    <DetailValue value={classroom.students_count ?? 0} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>عدد الحصص الدراسية</DetailLabel>
                                    <DetailValue value={classroom.schedules_count ?? 0} className="font-mono" />
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
            title: 'الفصول الدراسية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات الفصل الدراسي',
            href: show.url({ classroom: props.classroom }),
        },
    ],
});
