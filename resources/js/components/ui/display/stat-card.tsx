import React from "react";

import { cn } from "@/lib/utils";

import { Card, CardDescription, CardHeader, CardTitle } from "@/components/ui/structure/card";

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
        try {
            return new Intl.NumberFormat("en-US").format(num);
        } catch {
            return String(num);
        }
    }

    return String(value ?? 0);
}

export function StatCard({
    label,
    value,
    icon: Icon,
    className
}: StatCardProps) {
    return (
        <Card
            className={cn(
                "group relative overflow-hidden",
                // from-card to-muted-foreground/5 bg-linear-to-t shadow-sm
                className,
            )}
        >
            <CardHeader className="gap-1">
                <div className="flex items-start justify-between gap-3">
                    <CardTitle className="text-lg font-medium font-mono tracking-tight">
                        {formatStatValue(value)}
                    </CardTitle>
                    <span className="inline-flex size-8 shrink-0 items-center justify-center rounded-none bg-muted text-muted-foreground ring-1 ring-border/60 transition-colors group-hover:bg-muted/80">
                        <Icon className="size-4 shrink-0" aria-hidden />
                    </span>
                </div>
                <CardDescription className="line-clamp-1 font-medium text-foreground/80">
                    {label}
                </CardDescription>
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
                "*:data-[slot=card]:from-card *:data-[slot=card]:to-muted-foreground/5 *:data-[slot=card]:bg-linear-to-t *:data-[slot=card]:shadow-sm",
                columnClasses[columns],
                className
            )}
            {...props}
        >
            {items.map((item: StatCardItem, index: number) => (
                <StatCard key={index} {...item} />
            ))}
        </section>
    );
}
