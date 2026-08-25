import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { SchoolDistributionItem } from "@/types";

import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";

import { UsersIcon } from "lucide-react";

/**
 * Minimum horizontal space reserved per school so the bars stay readable
 * and scroll horizontally instead of squeezing together.
 */
const MIN_GROUP_WIDTH = 72;

const MAX_TICK_LENGTH = 16;

const chartConfig = {
    students: {
        label: "الطلبة",
        color: "var(--chart-2)",
    },
} satisfies ChartConfig;

type SchoolStudentsChartProps = {
    items?: SchoolDistributionItem[];
    className?: string;
};

export default function SchoolStudentsChart({ items, className }: SchoolStudentsChartProps) {
    return (
        <DashboardSectionCard
            title="توزيع الطلبة حسب المدارس"
            description="أكبر المدارس في النظام من حيث عدد الطلبة المسجلين"
            icon={UsersIcon}
            reloadProps={["schoolDistribution"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText="لا توجد مدارس مسجلة حالياً."
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <div className="overflow-x-auto">
                <ChartContainer
                    config={chartConfig}
                    className="aspect-auto h-72 w-full"
                    style={{ minWidth: (items?.length ?? 0) * MIN_GROUP_WIDTH }}
                >
                    <BarChart
                        accessibilityLayer
                        data={items}
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
                        <Bar dataKey="students" fill="var(--color-students)" maxBarSize={22} />
                    </BarChart>
                </ChartContainer>
            </div>
        </DashboardSectionCard>
    );
}
