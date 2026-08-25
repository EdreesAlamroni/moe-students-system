import React from "react";

import {
    assignmentStatus,
    emptyStates,
    genderComparison,
    studentsDistributedAcross,
    studentsGroupedAcross,
    studentsInclusive,
} from "@/lib/arabic-labels";

import type {
    EducationMonitorDashboardSummary,
    EducationMonitorSchoolDistributionItem,
    EducationServicesOfficeDistributionItem,
    GradeLevelDistributionItem,
} from "@/types";

import InsightCard from "@/components/shared/dashboard/insight-card";
import formatNumber from "@/components/shared/dashboard/format-number";

import {
    BuildingIcon,
    CrownIcon,
    PresentationIcon,
    ScaleIcon,
    SchoolIcon,
    UserRoundXIcon,
} from "lucide-react";

type QuickInsightsProps = {
    summary?: EducationMonitorDashboardSummary;
    offices?: EducationServicesOfficeDistributionItem[];
    schools?: EducationMonitorSchoolDistributionItem[];
    gradeLevels?: GradeLevelDistributionItem[];
};

export default function QuickInsights({ summary, offices, schools, gradeLevels }: QuickInsightsProps) {
    return (
        <section
            aria-label="مؤشرات سريعة"
            className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >
            <GenderRatioInsight summary={summary} />
            <SchoolAssignmentInsight summary={summary} />
            <LargestOfficeInsight offices={offices} />
            <LargestSchoolInsight schools={schools} />
            <LargestGradeLevelInsight gradeLevels={gradeLevels} />
            <AverageClassSizeInsight summary={summary} />
        </section>
    );
}

function GenderRatioInsight({ summary }: { summary?: EducationMonitorDashboardSummary }) {
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
                    <span className="font-mono">-</span>
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

function SchoolAssignmentInsight({ summary }: { summary?: EducationMonitorDashboardSummary }) {
    const assigned = summary
        ? summary.students - summary.students_unassigned_to_school
        : 0;

    const assignmentRate = summary && summary.students > 0
        ? Math.round((assigned / summary.students) * 100)
        : 0;

    return (
        <InsightCard
            label="نسبة إسناد الطلبة إلى المدارس"
            icon={UserRoundXIcon}
            isLoading={!summary}
            value={
                summary && summary.students > 0 ? (
                    <span className="font-mono tabular-nums">٪{assignmentRate}</span>
                ) : (
                    <span className="font-mono">-</span>
                )
            }
            detail={
                summary && summary.students > 0
                    ? assignmentStatus(assigned, summary.students_unassigned_to_school)
                    : emptyStates.noEnrolledStudents()
            }
        />
    );
}

function LargestOfficeInsight({ offices }: { offices?: EducationServicesOfficeDistributionItem[] }) {
    const largest = offices?.reduce((candidate: EducationServicesOfficeDistributionItem | undefined, item) => {
        if (!candidate || item.students > candidate.students) {
            return item;
        }

        return candidate;
    }, undefined);

    return (
        <InsightCard
            label="أعلى مكتب خدمات تعليمية كثافة"
            icon={BuildingIcon}
            isLoading={!offices}
            value={largest && largest.students > 0 ? largest.name : <span className="font-mono">-</span>}
            detail={
                largest && largest.students > 0
                    ? studentsGroupedAcross(largest.students, largest.schools, "يضم")
                    : emptyStates.noAssignedStudents("مكاتب الخدمات التعليمية")
            }
        />
    );
}

function LargestSchoolInsight({ schools }: { schools?: EducationMonitorSchoolDistributionItem[] }) {
    const largest = schools?.reduce((candidate: EducationMonitorSchoolDistributionItem | undefined, item) => {
        if (!candidate || item.students > candidate.students) {
            return item;
        }

        return candidate;
    }, undefined);

    const detail =
        largest && largest.students > 0
            ? [
                `يدرس بها ${studentsInclusive(largest.students)}`,
                largest.office?.name ? `تابعة لـ ${largest.office.name}` : null,
            ]
                .filter(Boolean)
                .join(" - ")
            : emptyStates.noRegisteredStudentsInSchools();

    return (
        <InsightCard
            label="أعلى مدرسة كثافة"
            icon={SchoolIcon}
            isLoading={!schools}
            value={largest && largest.students > 0 ? largest.name : <span className="font-mono">-</span>}
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
            label="أعلى صف دراسي كثافة"
            icon={CrownIcon}
            isLoading={!gradeLevels}
            value={largest?.name ?? <span className="font-mono">-</span>}
            detail={
                largest
                    ? `يضم ${studentsInclusive(largest.students)}`
                    : emptyStates.noGradeLevelEnrolledStudents()
            }
        />
    );
}

function AverageClassSizeInsight({ summary }: { summary?: EducationMonitorDashboardSummary }) {
    const average = summary && summary.classrooms > 0
        ? Math.round(summary.students / summary.classrooms)
        : 0;

    const detail = summary && summary.classrooms > 0
        ? studentsDistributedAcross(summary.students, summary.classrooms, "classrooms")
        : emptyStates.noClassroomsForCurrentYear();

    return (
        <InsightCard
            label="متوسط عدد الطلبة لكل فصل"
            icon={PresentationIcon}
            isLoading={!summary}
            value={
                summary && summary.classrooms > 0 ? (
                    <span className="font-mono tabular-nums">{formatNumber(average)}</span>
                ) : (
                    <span className="font-mono">-</span>
                )
            }
            detail={detail}
        />
    );
}
