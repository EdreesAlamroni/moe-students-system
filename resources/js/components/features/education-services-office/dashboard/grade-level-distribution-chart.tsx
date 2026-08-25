import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import { emptyStates } from "@/lib/arabic-labels";

import type { GradeLevelDistributionItem } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import horizontalBarTick from "@/components/shared/dashboard/horizontal-bar-tick";

import { GraduationCapIcon } from "lucide-react";

const ROW_HEIGHT = 48;
const LABEL_WIDTH = 160;

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

type GradeLevelDistributionChartProps = {
    items?: GradeLevelDistributionItem[];
    className?: string;
};

export default function GradeLevelDistributionChart({ items, className }: GradeLevelDistributionChartProps) {
    return (
        <DashboardSectionCard
            title="توزيع الطلبة حسب الصفوف الدراسية"
            description="عدد الطلبة المقيدين في كل صف دراسي ضمن نطاق المكتب للسنة الدراسية الحالية"
            icon={GraduationCapIcon}
            reloadProps={["gradeLevelDistribution"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText={emptyStates.noGradeLevelEnrolledStudentsCurrently()}
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="aspect-auto w-full"
                style={{ height: (items?.length ?? 0) * ROW_HEIGHT + 60 }}
            >
                <BarChart
                    accessibilityLayer
                    data={items}
                    layout="vertical"
                    margin={{ top: 0, bottom: 0, left: 0, right: 0 }}
                >
                    <CartesianGrid horizontal={false} />
                    <XAxis
                        type="number"
                        reversed
                        tickLine={false}
                        axisLine={false}
                        tickMargin={8}
                        allowDecimals={false}
                        tick={{ className: "font-mono" }}
                    />
                    <YAxis
                        type="category"
                        dataKey="name"
                        orientation="right"
                        tickLine={false}
                        tickSize={0}
                        axisLine={false}
                        width={LABEL_WIDTH}
                        tick={horizontalBarTick(LABEL_WIDTH)}
                    />
                    <ChartTooltip content={<ChartTooltipContent />} />
                    <ChartLegend content={<ChartLegendContent />} />
                    <Bar dataKey="males" stackId="students" fill="var(--color-males)" maxBarSize={22} />
                    <Bar dataKey="females" stackId="students" fill="var(--color-females)" maxBarSize={22} />
                </BarChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
