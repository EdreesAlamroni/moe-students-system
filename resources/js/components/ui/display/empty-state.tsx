import React from "react";

import { cn } from "@/lib/utils";

import { InboxIcon, SearchXIcon, type LucideIcon } from "lucide-react";

const DEFAULT_TEXT = "عذرًا، لا توجد بيانات للعرض حاليًا.";
const DEFAULT_FILTER_TEXT = "عذرًا، لا توجد نتائج مطابقة للبحث.";

type EmptyStateProps = React.ComponentProps<"div"> & {
    hasFilter?: boolean;
    text?: string;
    textFilter?: string;
    description?: string;
    icon?: LucideIcon;
};

export default function EmptyState({
    hasFilter = false,
    text = DEFAULT_TEXT,
    textFilter = DEFAULT_FILTER_TEXT,
    description,
    icon,
    className,
    ...props
}: EmptyStateProps) {
    const message = hasFilter ? textFilter : text;
    const Icon = icon ?? (hasFilter ? SearchXIcon : InboxIcon);

    return (
        <div
            role="status"
            className={cn(
                "relative isolate flex flex-col items-center gap-3 py-6 text-center",
                className,
            )}
            {...props}
        >
            <div
                className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,var(--color-border)_1px,transparent_0)] bg-size-[16px_16px] opacity-35"
                aria-hidden
            />

            <div className="relative flex flex-col items-center gap-2">
                <div
                    className={cn(
                        "inline-flex size-9 items-center justify-center bg-card shadow-sm ring-1",
                        hasFilter
                            ? "text-muted-foreground ring-border/70"
                            : "text-primary ring-primary/15",
                    )}
                    aria-hidden
                >
                    <Icon className="size-[18px] shrink-0 stroke-[1.5]" />
                </div>
                <span
                    className={cn(
                        "h-px w-7",
                        hasFilter ? "bg-border/80" : "bg-primary/30",
                    )}
                    aria-hidden
                />
            </div>

            <div className="relative max-w-sm space-y-1 px-2">
                <p className="text-balance text-sm font-medium text-foreground">
                    {message}
                </p>
                {description ? (
                    <p className="text-balance text-xs leading-relaxed text-muted-foreground">
                        {description}
                    </p>
                ) : null}
            </div>
        </div>
    );
}
