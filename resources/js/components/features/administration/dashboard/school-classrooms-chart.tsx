import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { SchoolDistributionItem } from "@/types";

import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";

import { PresentationIcon } from "lucide-react";

/**
 * Minimum horizontal space reserved per school so the bars stay readable
 * and scroll horizontally instead of squeezing together.
 */
const MIN_GROUP_WIDTH = 72;

const MAX_TICK_LENGTH = 16;

const chartConfig = {
    classrooms: {
        label: "الفصول الدراسية",
        color: "var(--chart-4)",
    },
} satisfies ChartConfig;

type SchoolClassroomsChartProps = {
    items?: SchoolDistributionItem[];
    className?: string;
};

export default function SchoolClassroomsChart({ items, className }: SchoolClassroomsChartProps) {
    const data = items ? [...items].sort((a, b) => b.classrooms - a.classrooms) : undefined;

    return (
        <DashboardSectionCard
            title="توزيع الفصول الدراسية حسب المدارس"
            description="عدد الفصول الدراسية في أكبر المدارس للسنة الدراسية الحالية"
            icon={PresentationIcon}
            reloadProps={["schoolDistribution"]}
            isLoading={!data}
            isEmpty={data?.length === 0}
            emptyText="لا توجد مدارس مسجلة حالياً."
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
                            dataKey="name"
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
                            width={52}
                            tick={{ className: "font-mono" }}
                        />
                        <ChartTooltip content={<ChartTooltipContent />} />
                        <Bar dataKey="classrooms" fill="var(--color-classrooms)" maxBarSize={22} />
                    </BarChart>
                </ChartContainer>
            </div>
        </DashboardSectionCard>
    );
}
