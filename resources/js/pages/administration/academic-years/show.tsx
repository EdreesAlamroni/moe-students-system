import React from 'react'

import { Head } from "@inertiajs/react";

import type { CanPermissions, AcademicYear } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Button } from "@/components/ui/actions/button";

import { index, show } from "@/routes/administration/academic-years";

import { CircleXIcon, NotepadTextIcon } from "lucide-react";

import { cn } from "@/lib/utils";

type AcademicYearProps = AcademicYear & {
    canAny: boolean;
    can: CanPermissions;
}

type PageProps = {
    academicYear: AcademicYearProps;
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ academicYear, canAny, can }: PageProps) {
    return (
        <>
            <Head title="عرض بيانات السنة الدراسية" />

            <MainContainer>
                {canAny && (
                    <ActionsSection>
                        {can.close && (
                            <Button
                                variant="destructive"
                            >
                                <CircleXIcon />
                                <span>إغلاق السنة الدراسية</span>
                            </Button>
                        )}
                    </ActionsSection>
                )}

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <NotepadTextIcon />
                                <span>عرض بيانات السنة الدراسية</span>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <DetailFields columns={3}>
                                <DetailField>
                                    <DetailLabel>السنة الدراسية</DetailLabel>
                                    <DetailValue value={academicYear.name} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>تاريخ بداية العام الدراسي</DetailLabel>
                                    <DetailValue value={academicYear.start_date} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>تاريخ انتهاء العام الدراسي</DetailLabel>
                                    <DetailValue value={academicYear.end_date} className="font-mono" />
                                </DetailField>

                                <DetailField>
                                    <DetailLabel>الحالة</DetailLabel>
                                    <DetailValue variant="plain">
                                        <span
                                            className={cn(
                                                "pill",
                                                academicYear.is_active ? "pill-green" : "pill-orange",
                                            )}
                                        >
                                            {academicYear.status}
                                        </span>
                                    </DetailValue>
                                </DetailField>
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
            title: 'السنوات الدراسية',
            href: index.url(),
        },
        {
            title: 'عرض بيانات السنة الدراسية',
            href: show.url({ academicYear: props.academicYear }),
        },
    ],
});
