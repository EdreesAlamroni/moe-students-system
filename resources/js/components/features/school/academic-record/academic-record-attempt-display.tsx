import React from "react";

import { cn } from "@/lib/utils";
import { academicRecordYearLabel } from "@/lib/academic-record-label";

import type { AcademicRecordAttempt } from "@/types";

import { Separator } from "@/components/ui/structure/separator";

import AcademicRecordStatusBadge from "@/components/features/school/academic-record/academic-record-status-badge";

import { CalendarDaysIcon, StarIcon } from "lucide-react";

interface AcademicRecordAttemptDisplayProps {
    attempt: AcademicRecordAttempt;
    attemptNumber: number;
    showSeparator?: boolean;
}

export default function AcademicRecordAttemptDisplay({
    attempt,
    attemptNumber,
    showSeparator = false,
}: AcademicRecordAttemptDisplayProps) {
    return (
        <div className="flex flex-col gap-4">
            {showSeparator && <Separator />}

            <div className="flex items-center justify-between gap-3">
                <p className="text-xs font-medium text-muted-foreground">
                    {academicRecordYearLabel(attemptNumber)}
                </p>
                <AcademicRecordStatusBadge status={attempt.status} />
            </div>

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div className="flex items-center gap-3">
                    <span
                        className={cn(
                            "flex size-9 shrink-0 items-center justify-center rounded-full",
                            "bg-muted/60 text-muted-foreground",
                        )}
                    >
                        <CalendarDaysIcon className="size-4" />
                    </span>

                    <div className="min-w-0 flex flex-col gap-0.5">
                        <span className="text-[13px] text-muted-foreground">السنة الدراسية</span>
                        <span className="truncate font-mono text-sm font-medium tracking-tight text-foreground">
                            {attempt.academic_year?.name ?? "-"}
                        </span>
                    </div>
                </div>

                {attempt.rating && (
                    <div className="flex items-center gap-3">
                        <span
                            className={cn(
                                "flex size-9 shrink-0 items-center justify-center rounded-full",
                                "bg-muted/60 text-muted-foreground",
                            )}
                        >
                            <StarIcon className="size-4" />
                        </span>

                        <div className="min-w-0 flex flex-col gap-0.5">
                            <span className="text-[13px] text-muted-foreground">التقدير</span>
                            <span className="truncate text-sm font-medium text-foreground">
                                {attempt.rating.name}
                            </span>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
