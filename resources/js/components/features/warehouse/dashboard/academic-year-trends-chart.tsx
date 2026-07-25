import React from "react";

import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts";

import type { WarehouseAcademicYearTrendItem } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { BarChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";

import { CalendarRangeIcon } from "lucide-react";

const MIN_GROUP_WIDTH = 88;

const chartConfig = {
    book_distributions: {
        label: "توزيعات الكُتب",
        color: "var(--chart-3)",
    },
    students_received: {
        label: "طلاب استلموا الكُتب",
        color: "var(--chart-2)",
    },
} satisfies ChartConfig;

type AcademicYearTrendsChartProps = {
    items?: WarehouseAcademicYearTrendItem[];
    className?: string;
};

export default function AcademicYearTrendsChart({ items, className }: AcademicYearTrendsChartProps) {
    return (
        <DashboardSectionCard
            title="اتجاهات التوزيع حسب السنة الدراسية"
            description="عدد توزيعات الكُتب والطلاب المستلمين عبر السنوات الدراسية"
            icon={CalendarRangeIcon}
            reloadProps={["academicYearTrends"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText="لا توجد بيانات توزيع كُتب عبر السنوات الدراسية."
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
                        <Bar dataKey="book_distributions" fill="var(--color-book_distributions)" maxBarSize={22} />
                        <Bar dataKey="students_received" fill="var(--color-students_received)" maxBarSize={22} />
                    </BarChart>
                </ChartContainer>
            </div>
        </DashboardSectionCard>
    );
}
