import { Bar, BarChart, CartesianGrid, Text, XAxis, YAxis } from "recharts";

import {
    ChartContainer,
    ChartLegend,
    ChartLegendContent,
    ChartTooltip,
    ChartTooltipContent,
} from "@/components/ui/display/chart";
import type { ChartConfig } from "@/components/ui/display/chart";

import { GraduationCapIcon } from "lucide-react";

import type { GradeLevelDistributionItem } from "@/types";

import DashboardSectionCard, { BarChartSkeleton } from "./dashboard-section-card";

const ROW_HEIGHT = 48;
const LABEL_WIDTH = 190;

const chartConfig = {
    males: {
        label: "الذكور",
        color: "var(--chart-2)",
    },
    females: {
        label: "الإناث",
        color: "var(--chart-1)",
    },
} satisfies ChartConfig;

type GradeLevelDistributionChartProps = {
    items?: GradeLevelDistributionItem[];
    className?: string;
};

/**
 * Renders each grade-level name inside the right-hand gutter with an RTL-aware
 * anchor, wrapping long names onto two lines instead of overlapping the bars.
 */
function GradeLevelTick({ x, y, payload }: { x?: number; y?: number; payload?: { value: string } }) {
    return (
        <Text
            x={x}
            y={y}
            width={LABEL_WIDTH - 12}
            textAnchor="end"
            verticalAnchor="middle"
            maxLines={2}
            fontSize={12}
            className="fill-muted-foreground"
        >
            {payload?.value ?? ""}
        </Text>
    );
}

export default function GradeLevelDistributionChart({ items, className }: GradeLevelDistributionChartProps) {
    return (
        <DashboardSectionCard
            title="توزيع الطلاب حسب الصفوف الدراسية"
            description="عدد الطلاب المقيدين في كل صف دراسي للسنة الدراسية الحالية"
            icon={GraduationCapIcon}
            reloadProps={["gradeLevelDistribution"]}
            isLoading={!items}
            isEmpty={items?.length === 0}
            emptyText="لا يوجد طلاب مقيدون بالصفوف الدراسية حالياً."
            skeleton={<BarChartSkeleton />}
            className={className}
        >
            <ChartContainer
                config={chartConfig}
                className="aspect-auto w-full"
                style={{ height: (items?.length ?? 0) * ROW_HEIGHT + 60 }}
            >
                <BarChart
                    accessibilityLayer
                    data={items}
                    layout="vertical"
                    margin={{ top: 4, bottom: 4, left: 12, right: 0 }}
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
                        axisLine={false}
                        width={LABEL_WIDTH}
                        tick={<GradeLevelTick />}
                    />
                    <ChartTooltip content={<ChartTooltipContent />} />
                    <ChartLegend content={<ChartLegendContent />} />
                    <Bar dataKey="males" stackId="students" fill="var(--color-males)" maxBarSize={22} />
                    <Bar dataKey="females" stackId="students" fill="var(--color-females)" maxBarSize={22} />
                </BarChart>
            </ChartContainer>
        </DashboardSectionCard>
    );
}
