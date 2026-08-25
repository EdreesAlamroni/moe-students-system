import React from "react";

import {
    completionRateDetail,
    completionRateWithPendingDetail,
    emptyStates,
    studentsReceivedBooks,
    warehouseCoverageDetail,
} from "@/lib/arabic-labels";

import type {
    WarehouseDashboardSummary,
    WarehouseEducationMonitorDistributionItem,
    WarehouseSchoolDistributionItem,
} from "@/types";

import InsightCard from "@/components/shared/dashboard/insight-card";
import formatNumber from "@/components/shared/dashboard/format-number";

import {
    CircleCheckBigIcon,
    LandmarkIcon,
    SchoolIcon,
    TrendingDownIcon,
    TrendingUpIcon,
    WarehouseIcon,
} from "lucide-react";

type QuickInsightsProps = {
    summary?: WarehouseDashboardSummary;
    monitors?: WarehouseEducationMonitorDistributionItem[];
    schools?: WarehouseSchoolDistributionItem[];
};

export default function QuickInsights({ summary, monitors, schools }: QuickInsightsProps) {
    return (
        <section
            aria-label="مؤشرات سريعة"
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <CompletionOverviewInsight summary={summary} />
            <CoverageInsight summary={summary} />
            <TopMonitorInsight monitors={monitors} />
            <LowestMonitorInsight monitors={monitors} />
            <TopSchoolInsight schools={schools} />
            <LowestSchoolInsight schools={schools} />
        </section>
    );
}

function CompletionOverviewInsight({ summary }: { summary?: WarehouseDashboardSummary }) {
    const eligible = summary ? summary.students_received + summary.students_pending : 0;

    return (
        <InsightCard
            label="نظرة عامة على إنجاز التوزيع"
            icon={CircleCheckBigIcon}
            isLoading={!summary}
            value={
                summary && eligible > 0 ? (
                    <span className="font-mono tabular-nums">٪{summary.completion_rate}</span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && eligible > 0
                    ? studentsReceivedBooks(summary.students_received, eligible)
                    : "لا توجد توزيعات كُتب مؤكَّدة للسنة الدراسية الحالية"
            }
            extra={
                summary && eligible > 0 ? (
                    <div
                        className="flex h-1.5 w-full overflow-hidden bg-muted"
                        role="img"
                        aria-label={`نسبة الإنجاز ٪${summary.completion_rate}`}
                    >
                        <span className="bg-chart-2" style={{ width: `${summary.completion_rate}%` }} />
                        <span className="bg-chart-1" style={{ width: `${100 - summary.completion_rate}%` }} />
                    </div>
                ) : undefined
            }
        />
    );
}

function CoverageInsight({ summary }: { summary?: WarehouseDashboardSummary }) {
    const averageStudentsPerSchool = summary && summary.schools > 0
        ? Math.round(summary.students / summary.schools)
        : 0;

    return (
        <InsightCard
            label="تغطية المخزن"
            icon={WarehouseIcon}
            isLoading={!summary}
            value={
                summary && summary.education_monitors > 0 ? (
                    <span className="font-mono tabular-nums">
                        {formatNumber(summary.education_monitors)} / {formatNumber(summary.schools)}
                    </span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && summary.education_monitors > 0
                    ? warehouseCoverageDetail(
                        summary.education_monitors,
                        summary.schools,
                        averageStudentsPerSchool,
                    )
                    : emptyStates.noMonitorsAssigned()
            }
        />
    );
}

function TopMonitorInsight({ monitors }: { monitors?: WarehouseEducationMonitorDistributionItem[] }) {
    const top = bestByCompletion(monitors);

    return (
        <InsightCard
            label="أعلى مُراقبة إنجازاً"
            icon={TrendingUpIcon}
            isLoading={!monitors}
            value={top ? top.name : "—"}
            detail={
                top
                    ? completionRateDetail(top.completion_rate, top.students_received, top.students_pending)
                    : "لا توجد بيانات توزيع كُتب للمُراقبات التعليمية"
            }
        />
    );
}

function LowestMonitorInsight({ monitors }: { monitors?: WarehouseEducationMonitorDistributionItem[] }) {
    const lowest = worstByCompletion(monitors);

    return (
        <InsightCard
            label="أقل مُراقبة إنجازاً"
            icon={TrendingDownIcon}
            isLoading={!monitors}
            value={lowest ? lowest.name : "—"}
            detail={
                lowest
                    ? completionRateWithPendingDetail(lowest.completion_rate, lowest.students_pending)
                    : "لا توجد بيانات توزيع كُتب للمُراقبات التعليمية"
            }
        />
    );
}

function TopSchoolInsight({ schools }: { schools?: WarehouseSchoolDistributionItem[] }) {
    const top = bestByCompletion(schools);

    return (
        <InsightCard
            label="أعلى مدرسة إنجازاً"
            icon={SchoolIcon}
            isLoading={!schools}
            value={top ? top.name : "—"}
            detail={
                top
                    ? `أنجزت ${top.completion_rate}٪ وتابعة لـ ${top.monitor.name}`
                    : "لا توجد بيانات توزيع كُتب للمدارس"
            }
        />
    );
}

function LowestSchoolInsight({ schools }: { schools?: WarehouseSchoolDistributionItem[] }) {
    const lowest = worstByCompletion(schools);

    return (
        <InsightCard
            label="أقل مدرسة إنجازاً"
            icon={LandmarkIcon}
            isLoading={!schools}
            value={lowest ? lowest.name : "—"}
            detail={
                lowest
                    ? completionRateWithPendingDetail(lowest.completion_rate, lowest.students_pending)
                    : "لا توجد بيانات توزيع كُتب للمدارس"
            }
        />
    );
}

type CompletionItem = {
    name: string;
    completion_rate: number;
    students_received: number;
    students_pending: number;
};

function bestByCompletion<T extends CompletionItem>(items?: T[]): T | undefined {
    return items
        ?.filter((item) => item.students_received + item.students_pending > 0)
        .reduce<T | undefined>((candidate, item) => {
            if (
                !candidate ||
                item.completion_rate > candidate.completion_rate ||
                (item.completion_rate === candidate.completion_rate && item.students_received > candidate.students_received)
            ) {
                return item;
            }

            return candidate;
        }, undefined);
}

function worstByCompletion<T extends CompletionItem>(items?: T[]): T | undefined {
    return items
        ?.filter((item) => item.students_received + item.students_pending > 0)
        .reduce<T | undefined>((candidate, item) => {
            if (
                !candidate ||
                item.completion_rate < candidate.completion_rate ||
                (item.completion_rate === candidate.completion_rate && item.students_pending > candidate.students_pending)
            ) {
                return item;
            }

            return candidate;
        }, undefined)
}
