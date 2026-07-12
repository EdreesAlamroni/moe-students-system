import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, School } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/display/actions-section";
import { StatCardsSection } from "@/components/ui/display/stat-card";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { GraduationCapIcon, PresentationIcon, NotepadTextIcon, SquarePenIcon, UsersIcon } from "lucide-react";

import { destroy, edit, index, show } from "@/routes/administration/schools";

type PageProps = {
    school: School;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ school, canAny, can }: PageProps) {
    const isPrivate = school.is_private === true;
    const hasOffice = !!school.office;

    return (
        <MainContainer>
            <Head title="عرض بيانات المدرسة" />

            {canAny && (
                <ActionsSection>
                    {can.update && (
                        <Button
                            variant="outline"
                            asChild
                        >
                            <Link href={edit.url({ school: school })}>
                                <SquarePenIcon />
                                <span>تعديل بيانات المدرسة</span>
                            </Link>
                        </Button>
                    )}

                    {can.delete && (
                        <ConfirmDeleteAction
                            title="حذف المدرسة"
                            href={destroy.url({ school: school })}
                        />
                    )}
                </ActionsSection>
            )}

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

                            <DetailField>
                                <DetailLabel>الفترة الدراسية</DetailLabel>
                                <DetailValue value={school.academic_period?.name} />
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

                            <DetailField>
                                <DetailLabel>جنس الطلاب الدارسين بالمدرسة</DetailLabel>
                                <DetailValue value={school.students_gender?.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>المراحل الدراسية</DetailLabel>
                                <DetailValue className="gap-0">
                                    {school.educational_stages?.map((educationalStage, index, arr) => (
                                        <span key={index}>
                                            {educationalStage.stage.name}
                                            {index < arr.length - 1 && (
                                                <span className="font-mono mx-1">،</span>
                                            )}
                                        </span>
                                    ))}
                                </DetailValue>
                            </DetailField>
                        </DetailFields>
                    </CardContent>
                </Card>
            </section>
        </MainContainer>
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
