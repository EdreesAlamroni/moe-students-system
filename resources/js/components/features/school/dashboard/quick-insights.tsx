import React from "react";

import {
    emptyStates,
    genderComparison,
    studentsDistributedAcross,
    studentsFromSeats,
    studentsInclusive,
} from "@/lib/arabic-labels";

import type { ClassroomOccupancyItem, DashboardSummary, GradeLevelDistributionItem } from "@/types";

import InsightCard from "@/components/shared/dashboard/insight-card";

import { CrownIcon, GaugeIcon, ScaleIcon, UsersIcon } from "lucide-react";

type QuickInsightsProps = {
    summary?: DashboardSummary;
    gradeLevels?: GradeLevelDistributionItem[];
    classrooms?: ClassroomOccupancyItem[];
};

export default function QuickInsights({ summary, gradeLevels, classrooms }: QuickInsightsProps) {
    return (
        <section
            aria-label="مؤشرات سريعة"
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >
            <GenderRatioInsight summary={summary} />
            <LargestGradeLevelInsight gradeLevels={gradeLevels} />
            <ClassroomUtilizationInsight classrooms={classrooms} />
            <AverageClassSizeInsight classrooms={classrooms} />
        </section>
    );
}

function GenderRatioInsight({ summary }: { summary?: DashboardSummary }) {
    const malesPercentage = summary && summary.students > 0
        ? Math.round((summary.males / summary.students) * 100)
        : 0;

    return (
        <InsightCard
            label="نسبة الذكور إلى الإناث"
            icon={ScaleIcon}
            isLoading={!summary}
            value={
                summary && summary.students > 0 ? (
                    <span className="font-mono tabular-nums">
                        ٪{malesPercentage} / ٪{100 - malesPercentage}
                    </span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && summary.students > 0
                    ? genderComparison(summary.males, summary.females)
                    : emptyStates.noEnrolledStudents()
            }
            extra={
                summary && summary.students > 0 ? (
                    <div
                        className="flex h-1.5 w-full overflow-hidden bg-muted"
                        role="img"
                        aria-label={`الذكور ٪${malesPercentage} والإناث ٪${100 - malesPercentage}`}
                    >
                        <span className="bg-chart-2" style={{ width: `${malesPercentage}%` }} />
                        <span className="bg-chart-1" style={{ width: `${100 - malesPercentage}%` }} />
                    </div>
                ) : undefined
            }
        />
    );
}

function LargestGradeLevelInsight({ gradeLevels }: { gradeLevels?: GradeLevelDistributionItem[] }) {
    const largest = gradeLevels?.reduce((candidate: GradeLevelDistributionItem | undefined, item) => {
        if (!candidate || item.students > candidate.students) {
            return item;
        }

        return candidate;
    }, undefined);

    return (
        <InsightCard
            label="أعلى صف دراسي كثافة"
            icon={CrownIcon}
            isLoading={!gradeLevels}
            value={largest?.name ?? "—"}
            detail={
                largest
                    ? `يضم ${studentsInclusive(largest.students)}`
                    : emptyStates.noGradeLevelEnrolledStudents()
            }
        />
    );
}

function ClassroomUtilizationInsight({ classrooms }: { classrooms?: ClassroomOccupancyItem[] }) {
    const students = classrooms?.reduce((total, classroom) => total + classroom.students, 0) ?? 0;
    const capacity = classrooms?.reduce((total, classroom) => total + classroom.capacity, 0) ?? 0;

    return (
        <InsightCard
            label="إشغال الفصول الدراسية"
            icon={GaugeIcon}
            isLoading={!classrooms}
            value={
                capacity > 0 ? (
                    <span className="font-mono tabular-nums">
                        ٪{Math.round((students / capacity) * 100)}
                    </span>
                ) : (
                    "—"
                )
            }
            detail={
                capacity > 0
                    ? studentsFromSeats(students, capacity)
                    : emptyStates.noClassroomsWithCapacity()
            }
        />
    );
}

function AverageClassSizeInsight({ classrooms }: { classrooms?: ClassroomOccupancyItem[] }) {
    const students = classrooms?.reduce((total, classroom) => total + classroom.students, 0) ?? 0;
    const count = classrooms?.length ?? 0;

    return (
        <InsightCard
            label="متوسط عدد الطلبة لكل فصل"
            icon={UsersIcon}
            isLoading={!classrooms}
            value={
                count > 0 ? (
                    <span className="font-mono tabular-nums">{Math.round(students / count)}</span>
                ) : (
                    "—"
                )
            }
            detail={
                count > 0
                    ? studentsDistributedAcross(students, count, "classrooms")
                    : emptyStates.noClassroomsCurrently()
            }
        />
    );
}
