import React from "react";

import { cn } from "@/lib/utils";

import type { Enum } from "@/types";

import { Badge } from "@/components/ui/display/badge";

import { CheckCircleIcon, XCircleIcon } from "lucide-react";

interface AcademicRecordStatusBadgeProps {
    status: Enum;
    className?: string;
}

export default function AcademicRecordStatusBadge({ status, className }: AcademicRecordStatusBadgeProps) {
    const isPassed = status.id === "passed" || status.id === "promoted";

    return (
        <Badge
            variant="outline"
            className={cn(
                "gap-1",
                !isPassed && "border-red-200 bg-red-50 text-red-700 hover:bg-red-50",
                isPassed && "border-green-200 bg-green-50 text-green-700 hover:bg-green-50",
                className,
            )}
        >
            {isPassed ? (
                <CheckCircleIcon className="size-3 shrink-0" />
            ) : (
                <XCircleIcon className="size-3 shrink-0" />
            )}
            <span>{status.name}</span>
        </Badge>
    );
}
