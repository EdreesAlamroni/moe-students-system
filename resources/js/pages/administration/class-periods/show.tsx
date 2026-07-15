import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, ClassPeriod } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { NotepadTextIcon, SquarePenIcon } from "lucide-react";

import { index, show, edit, destroy } from "@/routes/administration/class-periods";

type PageProps = {
    classPeriod: ClassPeriod;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ classPeriod, canAny, can }: PageProps) {
    return (
        <MainContainer>
            <Head title="عرض بيانات الحصة" />

            {canAny && (
                <ActionsSection>
                    {can.update && (
                        <Button
                            variant="outline"
                            asChild
                        >
                            <Link href={edit.url({ classPeriod: classPeriod })}>
                                <SquarePenIcon />
                                <span>تعديل بيانات الحصة</span>
                            </Link>
                        </Button>
                    )}

                    {can.delete && (
                        <ConfirmDeleteAction
                            title="حذف الحصة الدراسية"
                            href={destroy.url({ classPeriod: classPeriod })}
                        />
                    )}
                </ActionsSection>
            )}

            <section>
                <Card>
                    <CardHeader className="border-b">
                        <CardTitle>
                            <NotepadTextIcon />
                            <span>عرض بيانات الحصة</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col gap-6">
                        <DetailFields columns={2}>
                            <DetailField>
                                <DetailLabel>اسم الحصة</DetailLabel>
                                <DetailValue value={classPeriod.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>الفترة الدراسية</DetailLabel>
                                <DetailValue value={classPeriod.academic_period.name} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>وقت البداية</DetailLabel>
                                <DetailValue value={classPeriod.start_time} className="font-mono" />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>وقت النهاية</DetailLabel>
                                <DetailValue value={classPeriod.end_time} className="font-mono" />
                            </DetailField>
                        </DetailFields>

                        <DetailFields columns={3}>
                            <DetailField>
                                <DetailLabel>الترتيب</DetailLabel>
                                <DetailValue value={classPeriod.order} className="font-mono" />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>النوع</DetailLabel>
                                <DetailValue value={classPeriod.type} />
                            </DetailField>

                            <DetailField>
                                <DetailLabel>عدد الجداول المرتبطة</DetailLabel>
                                <DetailValue value={classPeriod.schedules_count} className="font-mono" />
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
            title: 'الحصص الدراسية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات الحصة',
            href: show.url({ classPeriod: props.classPeriod }),
        },
    ],
});
