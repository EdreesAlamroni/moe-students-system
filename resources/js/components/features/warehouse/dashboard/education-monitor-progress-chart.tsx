import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { WarehouseEducationMonitorDistributionItem } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import horizontalBarTick from "@/components/shared/dashboard/horizontal-bar-tick";

import { LandmarkIcon } from "lucide-react";

const ROW_HEIGHT = 48;
const LABEL_WIDTH = 200;

const chartConfig = {
    students_received: {
        label: "تم الاستلام",
        color: "var(--chart-2)",
    },
    students_pending: {
        label: "معلّق",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig;

type EducationMonitorProgressChartProps = {
    items?: WarehouseEducationMonitorDistributionItem[];
    className?: string;
};

export default function EducationMonitorProgressChart({ items, className }: EducationMonitorProgressChartProps) {
    const data = items?.filter((item) => {
        return item.students_received + item.students_pending > 0
    });

    return (
        <DashboardSectionCard
            title="تقدّم التوزيع حسب المُراقبات"
            description="الطلاب الذين استلموا الكُتب مقابل التسليمات المعلّقة لكل مُراقبة تعليمية"
            icon={LandmarkIcon}
            reloadProps={["educationMonitorDistribution"]}
            isLoading={!items}
            isEmpty={!data || data.length === 0}
            emptyText="لا توجد توزيعات كُتب مؤكَّدة للمُراقبات التعليمية."
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="aspect-auto w-full"
                style={{ height: (data?.length ?? 0) * ROW_HEIGHT + 60 }}
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
                    <ChartLegend content={<ChartLegendContent />} />
                    <Bar dataKey="students_received" stackId="progress" fill="var(--color-students_received)" maxBarSize={22} />
                    <Bar dataKey="students_pending" stackId="progress" fill="var(--color-students_pending)" maxBarSize={22} />
                </BarChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
