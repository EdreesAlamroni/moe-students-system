import React from 'react'

import { Head, Link } from "@inertiajs/react";

import type { CanPermissions, GradeLevel, School } from "@/types";

import MainContainer from "@/components/ui/structure/main-container";
import ActionsSection from "@/components/ui/structure/actions-section";
import { StatCardsSection } from "@/components/ui/display/stat-card";
import { Card, CardContent, CardHeader, CardTableContent, CardTitle } from "@/components/ui/structure/card";

import { DetailField, DetailFields } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";
import { Table, TableBody, TableCell, TableCellNullableValue, TableHead, TableHeader, TableRow } from "@/components/ui/display/table";
import EmptyState from "@/components/ui/display/empty-state";

import { Button } from "@/components/ui/actions/button";
import { ConfirmDeleteAction } from "@/components/ui/actions/confirmation-action";

import { CalendarRangeIcon, GraduationCapIcon, PresentationIcon, NotepadTextIcon, SquarePenIcon, UsersIcon } from "lucide-react";

import { destroy, edit, index, show } from "@/routes/administration/schools";

type PageProps = {
    school: School;
    gradeLevels: GradeLevel[];
    canAny: boolean;
    can: CanPermissions;
}

export default function Show({ school, gradeLevels, canAny, can }: PageProps) {
    const isPrivate = school.is_private === true;
    const hasOffice = !!school.office;
    const periods = school.periods ?? [];

    return (
        <>
            <Head title="عرض بيانات المدرسة" />

            <MainContainer showAcademicYearNotice>
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
                                    <DetailValue value={school.academic_period_label} />
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

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <CalendarRangeIcon />
                                <div className="flex items-center gap-x-1.5">
                                    <span>الفترات الدراسية</span>
                                    <span className="font-mono">({periods.length})</span>
                                </div>
                            </CardTitle>
                        </CardHeader>
                        {periods.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">الفترة الدراسية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الصفوف الدراسية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الفصول الدراسية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {periods.map((period, index) => (
                                            <TableRow key={period.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{period.academic_period.name}</TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue value={period.grade_levels_count} className="font-mono" fallback="0" />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue value={period.classrooms_count} className="font-mono" fallback="0" />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue value={period.students_count} className="font-mono" fallback="0" />
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardTableContent>
                        ) : (
                            <CardContent>
                                <EmptyState />
                            </CardContent>
                        )}
                    </Card>
                </section>

                <section>
                    <Card>
                        <CardHeader className="border-b">
                            <CardTitle>
                                <GraduationCapIcon />
                                <div className="flex items-center gap-x-1.5">
                                    <span>الصفوف الدراسية</span>
                                    <span className="font-mono">({gradeLevels.length})</span>
                                </div>
                            </CardTitle>
                        </CardHeader>
                        {gradeLevels.length > 0 ? (
                            <CardTableContent>
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead scope="col" className="font-mono w-24">#</TableHead>
                                            <TableHead scope="col">الاسم</TableHead>
                                            <TableHead scope="col">المرحلة الدراسية</TableHead>
                                            <TableHead scope="col">الفترة الدراسية</TableHead>
                                            <TableHead scope="col" className="text-center">عدد الطلاب</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {gradeLevels.map((gradeLevel: GradeLevel, index: number) => (
                                            <TableRow key={gradeLevel.uuid}>
                                                <TableCell className="font-mono">{index + 1}</TableCell>
                                                <TableCell>{gradeLevel.name}</TableCell>
                                                <TableCell>{gradeLevel.educational_stage.name}</TableCell>
                                                <TableCell>
                                                    <TableCellNullableValue value={gradeLevel.academic_period?.name} />
                                                </TableCell>
                                                <TableCell className="text-center">
                                                    <TableCellNullableValue value={gradeLevel.students_count} className="font-mono" fallback="0" />
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardTableContent>
                        ) : (
                            <CardContent>
                                <EmptyState />
                            </CardContent>
                        )}
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
