import React from "react";

import { Label, Pie, PieChart } from "recharts";

import { emptyStates } from "@/lib/arabic-labels";

import type { DashboardSummary } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { DonutChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import donutCenterLabel from "@/components/shared/dashboard/donut-center-label";

import { VenusAndMarsIcon } from "lucide-react";

const chartConfig = {
    males: {
        label: "الذكور",
        color: "var(--chart-2)",
    },
    females: {
        label: "الإناث",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig;

type GenderDistributionChartProps = {
    summary?: DashboardSummary;
    className?: string;
};

export default function GenderDistributionChart({ summary, className }: GenderDistributionChartProps) {
    const data = summary
        ? [
            { key: "males", students: summary.males, fill: "var(--color-males)" },
            { key: "females", students: summary.females, fill: "var(--color-females)" },
        ].filter((item) => item.students > 0)
        : [];

    return (
        <DashboardSectionCard
            title="توزيع الطلبة حسب الجنس"
            description="نسبة الذكور والإناث من إجمالي طلبة المدرسة"
            icon={VenusAndMarsIcon}
            reloadProps={["summary"]}
            isLoading={!summary}
            isEmpty={data.length === 0}
            emptyText={emptyStates.noEnrolledStudents()}
            skeleton={<DonutChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="mx-auto aspect-square max-h-72 w-full"
            >
                <PieChart accessibilityLayer>
                    <ChartTooltip
                        cursor={false}
                        content={<ChartTooltipContent nameKey="key" hideLabel />}
                    />
                    <Pie
                        data={data}
                        dataKey="students"
                        nameKey="key"
                        innerRadius={60}
                        strokeWidth={5}
                    >
                        <Label content={donutCenterLabel(summary?.students ?? 0, "إجمالي الطلبة")} />
                    </Pie>
                    <ChartLegend content={<ChartLegendContent nameKey="key" />} />
                </PieChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
