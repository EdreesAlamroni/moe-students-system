import type { LabelProps } from "recharts";

import formatNumber from "./format-number";

/**
 * Renders a total value with a caption in the center of a donut chart.
 * Pass the result to the `content` prop of a recharts `<Label>` inside a `<Pie>`.
 */
export default function donutCenterLabel(total: number, caption: string) {
    return function DonutCenterLabel({ viewBox }: LabelProps) {
        if (!viewBox || !("cx" in viewBox) || !("cy" in viewBox)) {
            return null;
        }

        return (
            <text
                x={viewBox.cx}
                y={viewBox.cy}
                textAnchor="middle"
                dominantBaseline="middle"
            >
                <tspan
                    x={viewBox.cx}
                    y={viewBox.cy}
                    className="fill-foreground font-mono text-2xl font-semibold tabular-nums"
                >
                    {formatNumber(total)}
                </tspan>
                <tspan
                    x={viewBox.cx}
                    y={(viewBox.cy ?? 0) + 22}
                    className="fill-muted-foreground text-xs"
                >
                    {caption}
                </tspan>
            </text>
        );
    };
}
