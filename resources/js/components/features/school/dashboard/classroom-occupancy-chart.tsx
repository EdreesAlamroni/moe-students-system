import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import { emptyStates } from "@/lib/arabic-labels";

import type { ClassroomOccupancyItem } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";

import { PresentationIcon } from "lucide-react";

/**
 * Minimum horizontal space reserved per classroom so large datasets
 * scroll horizontally instead of squeezing the bars together.
 */
const MIN_GROUP_WIDTH = 48;

const MAX_TICK_LENGTH = 16;

const chartConfig = {
    students: {
        label: "الطلبة",
        color: "var(--chart-2)",
    },
    capacity: {
        label: "السعة",
        color: "var(--chart-3)",
    },
} satisfies ChartConfig;

type ClassroomOccupancyChartProps = {
    items?: ClassroomOccupancyItem[];
    className?: string;
};

export default function ClassroomOccupancyChart({ items, className }: ClassroomOccupancyChartProps) {
    const data = items?.map((item) => ({
        ...item,
        label: `${item.grade_level} / ${item.name}`,
    }));

    return (
        <DashboardSectionCard
            title="توزيع الطلبة حسب الفصول الدراسية"
            description="عدد الطلبة الموزعين على كل فصل دراسي مقارنةً بسعته الاستيعابية"
            icon={PresentationIcon}
            reloadProps={["classroomOccupancy"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText={emptyStates.noClassroomsCurrently()}
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <div className="overflow-x-auto">
                <ChartContainer
                    config={chartConfig}
                    className="aspect-auto h-72 w-full"
                    style={{ minWidth: (data?.length ?? 0) * MIN_GROUP_WIDTH }}
                >
                    <BarChart
                        accessibilityLayer
                        data={data}
                        margin={{ top: 0, bottom: 0, left: 0, right: 0 }}
                    >
                        <CartesianGrid vertical={false} />
                        <XAxis
                            dataKey="label"
                            reversed
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            minTickGap={24}
                            tickFormatter={(value: string) =>
                                value.length > MAX_TICK_LENGTH
                                    ? `${value.slice(0, MAX_TICK_LENGTH)}…`
                                    : value
                            }
                        />
                        <YAxis
                            orientation="right"
                            tickLine={false}
                            axisLine={false}
                            tickMargin={8}
                            allowDecimals={false}
                            width={40}
                            tick={{ className: "font-mono" }}
                        />
                        <ChartTooltip content={<ChartTooltipContent />} />
                        <ChartLegend content={<ChartLegendContent />} />
                        <Bar dataKey="students" fill="var(--color-students)" maxBarSize={18} />
                        <Bar dataKey="capacity" fill="var(--color-capacity)" maxBarSize={18} />
                    </BarChart>
                </ChartContainer>
            </div>
        </DashboardSectionCard>
    );
}
