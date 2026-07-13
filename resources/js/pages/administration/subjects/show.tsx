import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, Subject } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { NotepadTextIcon, SquarePenIcon } from "lucide-react";

import { index, show, edit, destroy } from "@/routes/administration/subjects";

type PageProps = {
    subject: Subject;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ subject, canAny, can }: PageProps) {
    console.log(canAny, can);

    return (
        <MainContainer>
            <Head title="عرض بيانات المقرر الدراسي" />

            {canAny && (
                <ActionsSection>
                    {can.update && (
                        <Button
                            variant="outline"
                            asChild
                        >
                            <Link href={edit.url({ subject: subject })}>
                                <SquarePenIcon />
                                <span>تعديل بيانات المقرر</span>
                            </Link>
                        </Button>
                    )}

                    {can.delete && (
                        <ConfirmDeleteAction
                            title="حذف المقرر الدراسي"
                            href={destroy.url({ subject: subject })}
                        />
                    )}
                </ActionsSection>
            )}

            <section>
                <Card>
                    <CardHeader className="border-b">
                        <CardTitle>
                            <NotepadTextIcon />
                            <span>عرض بيانات المقرر الدراسي</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-6">
                        <DetailFields columns={2}>
                            <DetailField>
                                <DetailLabel>الصف الدراسي</DetailLabel>
                                <DetailValue value={subject.grade_level.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>اسم المقرر الدراسي</DetailLabel>
                                <DetailValue value={subject.name} />
                            </DetailField>
                        </DetailFields>

                        <DetailFields columns={3}>
                            <DetailField>
                                <DetailLabel>الرمز / الكود</DetailLabel>
                                <DetailValue value={subject.code} className="font-mono" />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>هل المقرر داخل المجموع ؟</DetailLabel>
                                <DetailValue value={subject.included_in_total_score_label} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>هل يحتاج إلى معمل ؟</DetailLabel>
                                <DetailValue value={subject.needs_lab_label} />
                            </DetailField>
                        </DetailFields>

                        <DetailFields columns={1}>
                            <DetailField className="col-span-full">
                                <DetailLabel>الوصف</DetailLabel>
                                <DetailValue
                                    value={subject.description}
                                    multiline
                                />
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
            title: 'المقررات الدراسية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات المقرر الدراسي',
            href: show.url({ subject: props.subject }),
        },
    ],
});
