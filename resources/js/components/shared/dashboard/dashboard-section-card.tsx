import React from "react";

import { router } from "@inertiajs/react";

import { cn } from "@/lib/utils";

import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import EmptyState from "@/components/ui/display/empty-state";

import { Button } from "@/components/ui/actions/button";

import { RefreshCcwIcon } from "lucide-react";
import type { LucideIcon } from "lucide-react";

type DashboardSectionCardProps = {
    title: string;
    description: string;
    icon: LucideIcon;
    /** Names of the deferred page props this section reloads (via a partial reload). */
    reloadProps: string[];
    isLoading: boolean;
    isEmpty: boolean;
    emptyText: string;
    skeleton: React.ReactNode;
    className?: string;
    children: React.ReactNode;
};

export default function DashboardSectionCard({
    title,
    description,
    icon: Icon,
    reloadProps,
    isLoading,
    isEmpty,
    emptyText,
    skeleton,
    className,
    children,
}: DashboardSectionCardProps) {
    const [isReloading, setIsReloading] = React.useState(false);

    const isBusy = isLoading || isReloading;

    const reload = (): void => {
        router.reload({
            only: reloadProps,
            onStart: () => setIsReloading(true),
            onFinish: () => setIsReloading(false),
        });
    };

    return (
        <Card className={className}>
            <CardHeader className="border-b">
                <CardTitle>
                    <Icon />
                    <span>{title}</span>
                </CardTitle>
                <CardDescription>{description}</CardDescription>
                <CardAction>
                    <Button
                        variant="ghost"
                        size="icon-sm"
                        onClick={reload}
                        disabled={isBusy}
                        aria-label={`تحديث بيانات ${title}`}
                    >
                        <RefreshCcwIcon className={cn(isBusy && "animate-spin")} />
                    </Button>
                </CardAction>
            </CardHeader>
            <CardContent aria-busy={isBusy} aria-live="polite">
                {isLoading ? skeleton : isEmpty ? <EmptyState text={emptyText} /> : children}
            </CardContent>
        </Card>
    );
}

export function DonutChartSkeleton() {
    return (
        <div className="flex flex-col items-center gap-6 py-4" role="status" aria-label="جارٍ تحميل الرسم البياني">
            <Skeleton className="size-44 rounded-full" />
            <div className="flex gap-4">
                <Skeleton className="h-3 w-16" />
                <Skeleton className="h-3 w-16" />
            </div>
        </div>
    );
}

export function BarChartSkeleton() {
    return (
        <div className="flex flex-col gap-4 py-4" role="status" aria-label="جارٍ تحميل الرسم البياني">
            {[85, 65, 90, 45, 70].map((width, index) => (
                <div key={index} className="flex items-center gap-3">
                    <Skeleton className="h-3 w-20 shrink-0" />
                    <Skeleton className="h-5" style={{ width: `${width}%` }} />
                </div>
            ))}
        </div>
    );
}
