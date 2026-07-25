import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { WarehouseEducationMonitorDistributionItem } from "@/types";

import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import horizontalBarTick from "@/components/shared/dashboard/horizontal-bar-tick";

import { UsersIcon } from "lucide-react";

const ROW_HEIGHT = 48;
const LABEL_WIDTH = 200;

const chartConfig = {
    students: {
        label: "الطلاب",
        color: "var(--chart-2)",
    },
} satisfies ChartConfig;

type EducationMonitorStudentsChartProps = {
    items?: WarehouseEducationMonitorDistributionItem[];
    className?: string;
};

export default function EducationMonitorStudentsChart({ items, className }: EducationMonitorStudentsChartProps) {
    return (
        <DashboardSectionCard
            title="الطلاب حسب المُراقبات التعليمية"
            description="عدد الطلاب المسندين إلى كل مُراقبة تربية وتعليم تابعة للمخزن"
            icon={UsersIcon}
            reloadProps={["educationMonitorDistribution"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText="لا توجد مُراقبات تعليمية مسندة إلى هذا المخزن."
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="aspect-auto w-full"
                style={{ height: (items?.length ?? 0) * ROW_HEIGHT + 40 }}
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
                    <Bar dataKey="students" fill="var(--color-students)" maxBarSize={22} />
                </BarChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
