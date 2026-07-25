import React from "react";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import { CrownIcon, GaugeIcon, ScaleIcon, UsersIcon  } from "lucide-react";
import type {LucideIcon} from "lucide-react";

import type { ClassroomOccupancyItem, DashboardSummary, GradeLevelDistributionItem } from "@/types";

import formatNumber from "./format-number";

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

type InsightCardProps = {
    label: string;
    icon: LucideIcon;
    isLoading: boolean;
    value: React.ReactNode;
    detail: React.ReactNode;
    extra?: React.ReactNode;
};

function InsightCard({ label, icon: Icon, isLoading, value, detail, extra }: InsightCardProps) {
    return (
        <Card size="sm">
            <CardHeader className="gap-2">
                <div className="flex items-center gap-2 text-muted-foreground">
                    <Icon className="size-4 shrink-0 stroke-[1.5]" aria-hidden />
                    <span className="line-clamp-1 text-xs font-medium tracking-wide uppercase select-none">
                        {label}
                    </span>
                </div>

                {isLoading ? (
                    <div className="flex flex-col gap-2" role="status" aria-label={`جارٍ تحميل ${label}`}>
                        <Skeleton className="h-6 w-24" />
                        <Skeleton className="h-3 w-32" />
                    </div>
                ) : (
                    <>
                        <p className="truncate text-lg font-medium tracking-tight text-foreground">
                            {value}
                        </p>
                        <p className="text-xs leading-relaxed text-muted-foreground">{detail}</p>
                        {extra}
                    </>
                )}
            </CardHeader>
        </Card>
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
