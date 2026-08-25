import React from "react";

import { Label, Pie, PieChart } from "recharts";

import { emptyStates } from "@/lib/arabic-labels";

import type { NationalityDistributionItem } from "@/types";

import { ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import DashboardSectionCard, { DonutChartSkeleton } from "@/components/shared/dashboard/dashboard-section-card";
import donutCenterLabel from "@/components/shared/dashboard/donut-center-label";

import { FlagIcon } from "lucide-react";

type NationalityDistributionChartProps = {
    items?: NationalityDistributionItem[];
    className?: string;
};

export default function NationalityDistributionChart({ items, className }: NationalityDistributionChartProps) {
    items = items ?? [];

    const chartConfig = Object.fromEntries(
        items.map((item, index) => {
            return [
                `nationality-${index}`,
                { label: item.name, color: `var(--chart-${(index % 5) + 1})` },
            ];
        }),
    ) satisfies ChartConfig;

    const data = items.map((item, index) => ({
        key: `nationality-${index}`,
        students: item.students,
        fill: `var(--color-nationality-${index})`,
    }));

    const total = data.reduce((sum, item) => sum + item.students, 0);

    return (
        <DashboardSectionCard
            title="توزيع الطلبة حسب الجنسية"
            description="عدد الطلبة حسب الجنسيات المسجلة في المدرسة"
            icon={FlagIcon}
            reloadProps={["nationalityDistribution"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText={emptyStates.noEnrolledStudents()}
            skeleton={<DonutChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="mx-auto aspect-square max-h-72 w-full"
            >
                <PieChart accessibilityLayer>
                    <ChartTooltip
                        cursor={false}
                        content={<ChartTooltipContent nameKey="key" hideLabel />}
                    />
                    <Pie
                        data={data}
                        dataKey="students"
                        nameKey="key"
                        innerRadius={60}
                        strokeWidth={5}
                    >
                        <Label content={donutCenterLabel(total, "إجمالي الطلبة")} />
                    </Pie>
                    <ChartLegend
                        content={<ChartLegendContent nameKey="key" className="flex-wrap" />}
                    />
                </PieChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
