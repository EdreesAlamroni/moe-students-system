import React from "react";

import { cn } from "@/lib/utils";

import type { Student } from "@/types";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { Separator } from "@/components/ui/structure/separator";

import { DetailField } from "@/components/ui/display/detail-field";
import { DetailLabel } from "@/components/ui/display/detail-label";
import { DetailValue } from "@/components/ui/display/detail-value";

import { Badge } from "@/components/ui/display/badge";

import {
    CheckCircle2Icon,
    FlagIcon,
    GraduationCapIcon,
    LayersIcon,
    NotepadTextIcon,
    UserRoundIcon,
} from "lucide-react";

interface AcademicRecordStudentOverviewProps {
    student: Student;
    requiresAcademicRecord: boolean;
    isComplete: boolean;
    completedGradeLevels: number;
    totalGradeLevels: number;
}

export default function AcademicRecordStudentOverview({
    student,
    requiresAcademicRecord,
    isComplete,
    completedGradeLevels,
    totalGradeLevels,
}: AcademicRecordStudentOverviewProps) {
    const progressPercentage = totalGradeLevels > 0
        ? Math.round((completedGradeLevels / totalGradeLevels) * 100)
        : 0;

    return (
        <Card>
            <CardHeader className="border-b">
                <CardTitle>
                    <NotepadTextIcon />
                    <span>عرض بيانات الطالب</span>
                </CardTitle>
            </CardHeader>

            <CardContent className="p-0">
                <div className={cn(
                    "grid grid-cols-1",
                    requiresAcademicRecord && "lg:grid-cols-[1fr_minmax(240px,280px)]",
                )}>
                    <div className="flex flex-col justify-center gap-6 py-4 lg:py-0 px-6">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div className="flex items-start gap-3 min-w-0">
                                <span className="flex size-11 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <UserRoundIcon className="size-5" />
                                </span>

                                <div className="min-w-0 flex flex-col gap-1">
                                    <h2 className="text-sm font-medium leading-snug truncate">
                                        {student.full_name}
                                    </h2>
                                    <p className="text-xs text-muted-foreground">
                                        {student.nationality.name}
                                    </p>
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <Badge variant="outline" className="gap-1.5 font-normal">
                                    <GraduationCapIcon className="size-3.5 shrink-0" />
                                    <span>{student.grade_level?.name ?? "-"}</span>
                                </Badge>

                                <Badge
                                    variant="outline"
                                    className={cn(
                                        "gap-1.5 font-normal",
                                        isComplete && "border-green-200 bg-green-50 text-green-700 hover:bg-green-50",
                                    )}
                                >
                                    <FlagIcon className="size-3.5 shrink-0" />
                                    <span>{student.registration_status.name}</span>
                                </Badge>
                            </div>
                        </div>

                        <Separator />

                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {student.is_libyan ? (
                                <>
                                    <DetailField>
                                        <DetailLabel>الرقم الوطني</DetailLabel>
                                        <DetailValue value={student.national_id} className="font-mono" />
                                    </DetailField>

                                    <DetailField>
                                        <DetailLabel>رقم القيد</DetailLabel>
                                        <DetailValue value={student.family_registration_number} className="font-mono" />
                                    </DetailField>
                                </>
                            ) : (
                                <DetailField className="sm:col-span-2">
                                    <DetailLabel>رقم جواز السفر</DetailLabel>
                                    <DetailValue value={student.passport_number} className="font-mono" />
                                </DetailField>
                            )}
                        </div>
                    </div>

                    {requiresAcademicRecord && (
                        <aside className="flex flex-col justify-center gap-5 border-t lg:border-t-0 lg:border-s py-4 lg:py-0 px-6">
                            <div className="flex items-center gap-2">
                                <LayersIcon className="size-4 shrink-0 text-muted-foreground" />
                                <h3 className="text-sm font-medium">ملخص السجل</h3>
                            </div>

                            <div className="flex flex-col gap-1">
                                <div className="flex items-end justify-between gap-3">
                                    <span className="text-2xl font-semibold tabular-nums">
                                        {completedGradeLevels}
                                        <span className="text-base font-normal text-muted-foreground">
                                            {" / "}
                                            {totalGradeLevels}
                                        </span>
                                    </span>
                                    <span className="text-xs text-muted-foreground pb-1">صفوف مُدخلة</span>
                                </div>

                                <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                                    <div
                                        className={cn(
                                            "h-full rounded-full transition-all duration-500 ease-out",
                                            isComplete ? "bg-green-600" : "bg-primary",
                                        )}
                                        style={{ width: `${progressPercentage}%` }}
                                    />
                                </div>

                                <p className="text-xs text-muted-foreground mt-1">
                                    {progressPercentage}% من الصفوف السابقة
                                </p>
                            </div>

                            {isComplete && (
                                <Badge className="w-fit gap-1 border-green-200 bg-green-50 text-green-700 hover:bg-green-50">
                                    <CheckCircle2Icon />
                                    <span>اكتمل السجل</span>
                                </Badge>
                            )}
                        </aside>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
