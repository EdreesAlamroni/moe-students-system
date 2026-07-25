import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { WarehouseSchoolDistributionItem } from "@/types";

import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";

import { SchoolIcon } from "lucide-react";

const MIN_GROUP_WIDTH = 72;
const MAX_TICK_LENGTH = 16;

const chartConfig = {
    students: {
        label: "الطلاب",
        color: "var(--chart-2)",
    },
} satisfies ChartConfig;

type SchoolStudentsChartProps = {
    items?: WarehouseSchoolDistributionItem[];
    className?: string;
};

export default function SchoolStudentsChart({ items, className }: SchoolStudentsChartProps) {
    return (
        <DashboardSectionCard
            title="الطلاب حسب المدارس"
            description="أكبر المدارس التابعة للمخزن من حيث عدد الطلاب المسجلين"
            icon={SchoolIcon}
            reloadProps={["schoolDistribution"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText="لا توجد مدارس تابعة لهذا المخزن."
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
