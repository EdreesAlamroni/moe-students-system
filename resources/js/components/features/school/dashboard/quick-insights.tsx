import React from "react";

import type { ClassroomOccupancyItem, DashboardSummary, GradeLevelDistributionItem } from "@/types";

import InsightCard from "@/components/shared/dashboard/insight-card";
import formatNumber from "@/components/shared/dashboard/format-number";

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
                    ? `${formatNumber(summary.males)} ذكور مقابل ${formatNumber(summary.females)} إناث`
                    : "لا يوجد طلاب مسجلون حالياً"
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
    const largest = gradeLevels?.reduce(
        (candidate: GradeLevelDistributionItem | undefined, item) =>
            !candidate || item.students > candidate.students ? item : candidate,
        undefined,
    );

    return (
        <InsightCard
            label="أكبر صف دراسي"
            icon={CrownIcon}
            isLoading={!gradeLevels}
            value={largest?.name ?? "—"}
            detail={
                largest
                    ? `يضم ${formatNumber(largest.students)} طالباً وطالبة`
                    : "لا يوجد طلاب مقيدون بالصفوف الدراسية"
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
                    ? `${formatNumber(students)} طالباً من أصل ${formatNumber(capacity)} مقعد`
                    : "لا توجد فصول دراسية بسعة محددة"
            }
        />
    );
}

function AverageClassSizeInsight({ classrooms }: { classrooms?: ClassroomOccupancyItem[] }) {
    const students = classrooms?.reduce((total, classroom) => total + classroom.students, 0) ?? 0;
    const count = classrooms?.length ?? 0;

    return (
        <InsightCard
            label="متوسط الطلاب لكل فصل"
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
                    ? `${formatNumber(students)} طالباً موزعون على ${formatNumber(count)} فصل دراسي`
                    : "لا توجد فصول دراسية حالياً"
            }
        />
    );
}
