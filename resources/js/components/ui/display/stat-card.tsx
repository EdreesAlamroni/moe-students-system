import React from "react";

import { cn } from "@/lib/utils";

import {
    Card,
    CardAction,
    CardDescription,
    CardHeader,
} from "@/components/ui/structure/card";

import { type LucideProps } from "lucide-react";

export type StatCardItem = {
    label: string;
    value: number | string;
    icon: React.ComponentType<Omit<LucideProps, "ref">>;
};

type StatCardProps = StatCardItem & {
    className?: string;
};

type StatCardsSectionProps = {
    items: StatCardItem[];
    columns?: 2 | 3 | 4;
    className?: string;
} & React.HTMLAttributes<HTMLElement>;

const columnClasses: Record<NonNullable<StatCardsSectionProps["columns"]>, string> = {
    2: "lg:grid-cols-2",
    3: "lg:grid-cols-3",
    4: "sm:grid-cols-2 lg:grid-cols-4",
};

function formatStatValue(value: number | string): string {
    const num = Number(value);

    if (Number.isFinite(num)) {
        return new Intl.NumberFormat("en-US").format(num);
    }

    return String(value ?? "");
}

export function StatCard({
    label,
    value,
    icon: Icon,
    className,
}: StatCardProps) {
    const formattedValue = formatStatValue(value);

    return (
        <Card
            size="sm"
            data-slot="stat-card"
            aria-label={`${label}: ${formattedValue}`}
            className={cn(
                "bg-linear-to-br from-card via-card to-primary/[0.06]",
                className,
            )}
        >
            <CardHeader className="gap-2">
                <CardDescription className="line-clamp-1 text-sm font-medium tracking-wide uppercase select-none">
                    {label}
                </CardDescription>

                <CardAction>
                    <span
                        className="inline-flex size-8 shrink-0 items-center justify-center bg-primary/[0.04] text-muted-foreground ring-1 ring-card/5"
                        aria-hidden
                    >
                        <Icon className="size-5 shrink-0 stroke-[1.5]" />
                    </span>
                </CardAction>

                <p className="text-xl font-medium font-mono tabular-nums tracking-tight text-foreground">
                    {formattedValue}
                </p>
            </CardHeader>
        </Card>
    );
}

export function StatCardsSection({
    items,
    columns = 3,
    className,
    ...props
}: StatCardsSectionProps) {
    return (
        <section
            className={cn(
                "grid grid-cols-1 gap-4",
                columnClasses[columns],
                className,
            )}
            {...props}
        >
            {items.map((item) => (
                <StatCard key={item.label} {...item} />
            ))}
        </section>
    );
}
