import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { EducationMonitorDistributionItem } from "@/types";

import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import horizontalBarTick from "@/components/shared/dashboard/horizontal-bar-tick";

import { SchoolIcon } from "lucide-react";

const ROW_HEIGHT = 40;
const LABEL_WIDTH = 200;

const chartConfig = {
    schools: {
        label: "المدارس",
        color: "var(--chart-3)",
    },
} satisfies ChartConfig;

type EducationMonitorSchoolsChartProps = {
    items?: EducationMonitorDistributionItem[];
    className?: string;
};

export default function EducationMonitorSchoolsChart({ items, className }: EducationMonitorSchoolsChartProps) {
    const data = items ? [...items].sort((a, b) => b.schools - a.schools) : undefined;

    return (
        <DashboardSectionCard
            title="توزيع المدارس حسب المُراقبات التعليمية"
            description="عدد المدارس التابعة لكل مُراقبة تربية وتعليم"
            icon={SchoolIcon}
            reloadProps={["educationMonitorDistribution"]}
            isLoading={!data}
            isEmpty={data?.length === 0}
            emptyText="لا توجد مُراقبات تعليمية مسجلة حالياً."
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="aspect-auto w-full"
                style={{ height: (data?.length ?? 0) * ROW_HEIGHT + 40 }}
            >
                <BarChart
                    accessibilityLayer
                    data={data}
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
                    <Bar dataKey="schools" fill="var(--color-schools)" maxBarSize={22} />
                </BarChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
