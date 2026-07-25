import { Text } from "recharts";

/**
 * Renders a category-axis label inside the right-hand gutter of a horizontal
 * bar chart, flush with the outer (start/right) edge and wrapping long names
 * onto two lines instead of overlapping the bars.
 *
 * Use with a category `<YAxis orientation="right" tickSize={0} width={labelWidth}>`.
 * Recharts places tick `x` at the inner edge of that band; this re-anchors to
 * the outer edge under the chart surface's LTR direction.
 */
export default function horizontalBarTick(labelWidth: number) {
    const edgePadding = 4;

    return function HorizontalBarTick({
        x,
        y,
        payload,
    }: {
        x?: string | number;
        y?: string | number;
        payload?: { value: string };
    }) {
        const labelX = Number(x ?? 0) + labelWidth - edgePadding;

        return (
            <Text
                x={labelX}
                y={y}
                width={labelWidth - edgePadding * 2}
                textAnchor="end"
                verticalAnchor="middle"
                maxLines={2}
                fontSize={12}
                className="fill-muted-foreground"
            >
                {payload?.value ?? ""}
            </Text>
        );
    };
}
