import React from "react";

import { Card, CardHeader } from "@/components/ui/structure/card";
import { Skeleton } from "@/components/ui/structure/skeleton";

import type { LucideIcon } from "lucide-react";

type InsightCardProps = {
    label: string;
    icon: LucideIcon;
    isLoading: boolean;
    value: React.ReactNode;
    detail: React.ReactNode;
    extra?: React.ReactNode;
};

export default function InsightCard({ label, icon: Icon, isLoading, value, detail, extra }: InsightCardProps) {
    return (
        <Card size="sm">
            <CardHeader className="gap-2">
                <div className="flex items-center gap-2 text-muted-foreground">
                    <Icon className="size-4 shrink-0 stroke-[1.5]" aria-hidden />
                    <span className="line-clamp-1 text-xs font-medium tracking-wide uppercase select-none">
                        {label}
                    </span>
                </div>

                {isLoading ? (
                    <div className="flex flex-col gap-2" role="status" aria-label={`جارٍ تحميل ${label}`}>
                        <Skeleton className="h-6 w-24" />
                        <Skeleton className="h-3 w-32" />
                    </div>
                ) : (
                    <>
                        <p className="truncate text-base font-medium tracking-tight text-foreground">
                            {value}
                        </p>
                        <p className="text-xs leading-relaxed text-muted-foreground">{detail}</p>
                        {extra}
                    </>
                )}
            </CardHeader>
        </Card>
    );
}
