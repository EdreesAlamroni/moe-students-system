import React from "react";

import { cn } from "@/lib/utils";

import type { AcademicYear, Enum, GradeLevel, GroupedAcademicRecord, Student } from "@/types";

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/structure/card";

import { Badge } from "@/components/ui/display/badge";

import AcademicRecordAttemptDisplay from "@/components/features/school/academic-record/academic-record-attempt-display";
import AcademicRecordEntryForm from "@/components/features/school/academic-record/academic-record-entry-form";

import { CheckCircle2Icon, CircleDotIcon, RotateCcwIcon } from "lucide-react";

interface AcademicRecordGradeLevelCardProps {
    record: GroupedAcademicRecord;
    student?: Student;
    currentGradeLevel?: GradeLevel;
    selectableAcademicYears?: AcademicYear[];
    academicRecordStatuses?: Enum[];
    academicRecordRatings?: Enum[];
    showEntryForm?: boolean;
    isLast?: boolean;
}

export default function AcademicRecordGradeLevelCard({
    record,
    student,
    currentGradeLevel,
    selectableAcademicYears = [],
    academicRecordStatuses = [],
    academicRecordRatings = [],
    showEntryForm = false,
    isLast = false,
}: AcademicRecordGradeLevelCardProps) {
    const isCurrent = record.is_current === true;
    const isPassed = record.is_passed;

    return (
        <div className="relative flex gap-4">
            <div className="flex flex-col items-center">
                <span
                    className={cn(
                        "flex size-8 shrink-0 items-center justify-center mt-3 rounded-full border-2 bg-background",
                        isPassed && "border-green-500 text-green-600",
                        isCurrent && !isPassed && "border-primary text-primary",
                        !isPassed && !isCurrent && "border-muted-foreground/30 text-muted-foreground",
                    )}
                >
                    {isPassed ? (
                        <CheckCircle2Icon className="size-4" />
                    ) : isCurrent ? (
                        <CircleDotIcon className="size-4" />
                    ) : (
                        <RotateCcwIcon className="size-3.5" />
                    )}
                </span>
                {!isLast && (
                    <span className="mt-3 w-px flex-1 min-h-8 bg-border" aria-hidden="true" />
                )}
            </div>

            <Card
                className={cn(
                    "flex-1 rounded-none shadow-none ring-0 border",
                    isCurrent && "border-primary/30",
                    !isLast && "mb-6",
                )}
            >
                <CardHeader className="border-b">
                    <div className="flex items-center justify-between gap-3">
                        <CardTitle className="text-sm font-medium">
                            {record.grade_level.name}
                        </CardTitle>

                        {isCurrent && (
                            <Badge variant="secondary">قيد الإدخال</Badge>
                        )}
                    </div>
                </CardHeader>

                <CardContent className="flex flex-col gap-4">
                    {record.attempts.map((attempt, index) => (
                        <AcademicRecordAttemptDisplay
                            key={attempt.id}
                            attempt={attempt}
                            attemptNumber={index + 1}
                            showSeparator={index > 0}
                        />
                    ))}

                    {showEntryForm && isCurrent && student && currentGradeLevel && (
                        <AcademicRecordEntryForm
                            student={student}
                            record={record}
                            currentGradeLevel={currentGradeLevel}
                            selectableAcademicYears={selectableAcademicYears}
                            academicRecordStatuses={academicRecordStatuses}
                            academicRecordRatings={academicRecordRatings}
                        />
                    )}
                </CardContent>
            </Card>
        </div>
    );
}
