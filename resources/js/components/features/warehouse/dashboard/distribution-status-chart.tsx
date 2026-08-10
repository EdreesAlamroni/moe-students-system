import React from "react";

import { Label, Pie, PieChart } from "recharts";

import type { WarehouseDashboardSummary } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { DonutChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import donutCenterLabel from "@/components/shared/dashboard/donut-center-label";

import { BookOpenCheckIcon } from "lucide-react";

const chartConfig = {
    received: {
        label: "تم الاستلام",
        color: "var(--chart-2)",
    },
    pending: {
        label: "معلّق",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig;

type DistributionStatusChartProps = {
    summary?: WarehouseDashboardSummary;
    className?: string;
};

export default function DistributionStatusChart({ summary, className }: DistributionStatusChartProps) {
    const data = summary
        ? [
            { key: "received", students: summary.students_received, fill: "var(--color-received)" },
            { key: "pending", students: summary.students_pending, fill: "var(--color-pending)" },
        ].filter((item) => item.students > 0)
        : [];

    const total = summary
        ? summary.students_received + summary.students_pending
        : 0;

    return (
        <DashboardSectionCard
            title="حالة توزيع الكُتب"
            description="عدد الطلبة المستلمون مقابل المعلّقين لهذا العام الدراسي."

            icon={BookOpenCheckIcon}
            reloadProps={["summary"]}
            isLoading={!summary}
            isEmpty={data.length === 0}
            emptyText="لا توجد توزيعات كُتب مؤكَّدة للسنة الدراسية الحالية."
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
                        <Label content={donutCenterLabel(total, "عدد الطلبة")} />
                    </Pie>
                    <ChartLegend content={<ChartLegendContent nameKey="key" />} />
                </PieChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
