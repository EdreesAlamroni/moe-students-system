import React from "react";

import type {
    EducationServicesOfficeDashboardSummary,
    EducationServicesOfficeSchoolDistributionItem,
    GradeLevelDistributionItem,
} from "@/types";

import InsightCard from "@/components/shared/dashboard/insight-card";
import formatNumber from "@/components/shared/dashboard/format-number";

import {
    CrownIcon,
    PresentationIcon,
    ScaleIcon,
    SchoolIcon,
    UserRoundCheckIcon,
    UsersIcon,
} from "lucide-react";

type QuickInsightsProps = {
    summary?: EducationServicesOfficeDashboardSummary;
    schools?: EducationServicesOfficeSchoolDistributionItem[];
    gradeLevels?: GradeLevelDistributionItem[];
};

export default function QuickInsights({ summary, schools, gradeLevels }: QuickInsightsProps) {
    return (
        <section
            aria-label="مؤشرات سريعة"
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <GenderRatioInsight summary={summary} />
            <GradeLevelEnrollmentInsight summary={summary} />
            <LargestSchoolInsight schools={schools} />
            <LargestGradeLevelInsight gradeLevels={gradeLevels} />
            <AverageSchoolSizeInsight summary={summary} />
            <AverageClassSizeInsight summary={summary} />
        </section>
    );
}

function GenderRatioInsight({ summary }: { summary?: EducationServicesOfficeDashboardSummary }) {
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

function GradeLevelEnrollmentInsight({ summary }: { summary?: EducationServicesOfficeDashboardSummary }) {
    const enrolled = summary
        ? summary.students - summary.students_unenrolled_in_grade_level
        : 0;

    const enrollmentRate = summary && summary.students > 0
        ? Math.round((enrolled / summary.students) * 100)
        : 0;

    return (
        <InsightCard
            label="نسبة القيد بالصفوف الدراسية"
            icon={UserRoundCheckIcon}
            isLoading={!summary}
            value={
                summary && summary.students > 0 ? (
                    <span className="font-mono tabular-nums">٪{enrollmentRate}</span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && summary.students > 0
                    ? `${formatNumber(enrolled)} مقيدون و ${formatNumber(summary.students_unenrolled_in_grade_level)} غير مقيدين بالصفوف الدراسية`
                    : "لا يوجد طلاب مسجلون حالياً"
            }
        />
    );
}

function LargestSchoolInsight({ schools }: { schools?: EducationServicesOfficeSchoolDistributionItem[] }) {
    const largest = schools?.reduce((candidate: EducationServicesOfficeSchoolDistributionItem | undefined, item) => {
        if (!candidate || item.students > candidate.students) {
            return item;
        }

        return candidate;
    }, undefined);

    return (
        <InsightCard
            label="أكبر مدرسة"
            icon={SchoolIcon}
            isLoading={!schools}
            value={largest && largest.students > 0 ? largest.name : "—"}
            detail={
                largest && largest.students > 0
                    ? `يدرس بها ${formatNumber(largest.students)} طالباً وطالبة`
                    : "لا يوجد طلاب مسجلون بالمدارس"
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

function AverageSchoolSizeInsight({ summary }: { summary?: EducationServicesOfficeDashboardSummary }) {
    const average = summary && summary.schools > 0
        ? Math.round(summary.students / summary.schools)
        : 0;

    return (
        <InsightCard
            label="متوسط الطلاب لكل مدرسة"
            icon={UsersIcon}
            isLoading={!summary}
            value={
                summary && summary.schools > 0 ? (
                    <span className="font-mono tabular-nums">{formatNumber(average)}</span>
                ) : (
                    "—"
                )
            }
            detail={
                summary && summary.schools > 0
                    ? `${formatNumber(summary.students)} طالباً موزعون على ${formatNumber(summary.schools)} مدرسة`
                    : "لا توجد مدارس مسجلة حالياً"
            }
        />
    );
}

function AverageClassSizeInsight({ summary }: { summary?: EducationServicesOfficeDashboardSummary }) {
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
