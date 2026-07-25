import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { WarehouseSchoolDistributionItem } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";

import { TruckIcon } from "lucide-react";

const MIN_GROUP_WIDTH = 72;
const MAX_TICK_LENGTH = 16;

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

type SchoolProgressChartProps = {
    items?: WarehouseSchoolDistributionItem[];
    className?: string;
};

export default function SchoolProgressChart({ items, className }: SchoolProgressChartProps) {
    const data = items?.filter((item) => {
        return item.students_received + item.students_pending > 0;
    });

    return (
        <DashboardSectionCard
            title="تقدّم التوزيع حسب المدارس"
            description="الطلاب الذين استلموا الكُتب مقابل التسليمات المعلّقة لأكبر المدارس"
            icon={TruckIcon}
            reloadProps={["schoolDistribution"]}
            isLoading={!items}
            isEmpty={!data || data.length === 0}
            emptyText="لا توجد توزيعات كُتب مؤكَّدة للمدارس."
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
                        <ChartLegend content={<ChartLegendContent />} />
                        <Bar dataKey="students_received" stackId="progress" fill="var(--color-students_received)" maxBarSize={22} />
                        <Bar dataKey="students_pending" stackId="progress" fill="var(--color-students_pending)" maxBarSize={22} />
                    </BarChart>
                </ChartContainer>
            </div>
        </DashboardSectionCard>
    );
}
