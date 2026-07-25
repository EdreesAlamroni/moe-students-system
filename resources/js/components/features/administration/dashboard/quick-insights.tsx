import React from "react";

import type {
    AdministrationDashboardSummary,
    EducationMonitorDistributionItem,
    GradeLevelDistributionItem,
    SchoolDistributionItem,
} from "@/types";

import InsightCard from "@/components/shared/dashboard/insight-card";
import formatNumber from "@/components/shared/dashboard/format-number";

import { CrownIcon, LandmarkIcon, PresentationIcon, ScaleIcon, SchoolIcon, UsersIcon } from "lucide-react";


type QuickInsightsProps = {
    summary?: AdministrationDashboardSummary;
    monitors?: EducationMonitorDistributionItem[];
    schools?: SchoolDistributionItem[];
    gradeLevels?: GradeLevelDistributionItem[];
};

export default function QuickInsights({ summary, monitors, schools, gradeLevels }: QuickInsightsProps) {
    return (
        <section
            aria-label="مؤشرات سريعة"
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <GenderRatioInsight summary={summary} />
            <LargestEducationMonitorInsight monitors={monitors} />
            <LargestSchoolInsight schools={schools} />
            <LargestGradeLevelInsight gradeLevels={gradeLevels} />
            <SchoolDistributionInsight summary={summary} />
            <AverageClassSizeInsight summary={summary} />
        </section>
    );
}

function GenderRatioInsight({ summary }: { summary?: AdministrationDashboardSummary }) {
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

function LargestEducationMonitorInsight({ monitors }: { monitors?: EducationMonitorDistributionItem[] }) {
    const largest = monitors?.reduce((candidate: EducationMonitorDistributionItem | undefined, item) => {
        if (!candidate || item.students > candidate.students) {
            return item;
        }

        return candidate;
    }, undefined);

    return (
        <InsightCard
            label="أكبر مُراقبة تعليمية"
            icon={LandmarkIcon}
            isLoading={!monitors}
            value={largest && largest.students > 0 ? largest.name : "—"}
            detail={
                largest && largest.students > 0
                    ? `تضم ${formatNumber(largest.students)} طالباً موزعين على ${formatNumber(largest.schools)} مدرسة`
                    : "لا يوجد طلاب مسندون إلى المُراقبات التعليمية"
            }
        />
    );
}

function LargestSchoolInsight({ schools }: { schools?: SchoolDistributionItem[] }) {
    const largest = schools?.reduce((candidate: SchoolDistributionItem | undefined, item) => {
        if (!candidate || item.students > candidate.students) {
            return item;
        }

        return candidate;
    }, undefined);

    const detail =
        largest && largest.students > 0
            ? [
                `يدرس بها ${formatNumber(largest.students)} طالباً وطالبة`,
                largest.monitor.name ? `تابعة لـ ${largest.monitor.name}` : null,
            ]
                .filter(Boolean)
                .join(" - ")
            : "لا يوجد طلاب مسجلون بالمدارس";

    return (
        <InsightCard
            label="أكبر مدرسة"
            icon={SchoolIcon}
            isLoading={!schools}
            value={largest && largest.students > 0 ? largest.name : "—"}
            detail={detail}
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

function SchoolDistributionInsight({ summary }: { summary?: AdministrationDashboardSummary }) {
    const averageStudentsPerSchool = summary && summary.schools > 0
        ? Math.round(summary.students / summary.schools)
        : 0;

    return (
        <InsightCard
            label="توزيع المدارس"
            icon={UsersIcon}
            isLoading={!summary}
            value={
                summary && summary.schools > 0 ? (
                    <span className="font-mono tabular-nums">{formatNumber(averageStudentsPerSchool)}</span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && summary.schools > 0
                    ? `متوسط الطلاب لكل مدرسة عبر ${formatNumber(summary.schools)} مدرسة و ${formatNumber(summary.education_monitors)} مُراقبة`
                    : "لا توجد مدارس مسجلة حالياً"
            }
        />
    );
}

function AverageClassSizeInsight({ summary }: { summary?: AdministrationDashboardSummary }) {
    const average = summary && summary.classrooms > 0
        ? Math.round(summary.students / summary.classrooms)
        : 0;

    return (
        <InsightCard
            label="متوسط الطلاب لكل فصل"
            icon={PresentationIcon}
            isLoading={!summary}
            value={
                summary && summary.classrooms > 0 ? (
                    <span className="font-mono tabular-nums">{formatNumber(average)}</span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && summary.classrooms > 0
                    ? `${formatNumber(summary.students)} طالباً موزعون على ${formatNumber(summary.classrooms)} فصل دراسي`
                    : "لا توجد فصول دراسية للسنة الدراسية الحالية"
            }
        />
    );
}
