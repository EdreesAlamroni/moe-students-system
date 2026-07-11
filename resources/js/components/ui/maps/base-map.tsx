import type { ReactNode } from "react";

import { cn } from "@/lib/utils";

type BaseMapProps = {
    children?: ReactNode;
    className?: string;
    heightClassName?: string;
};

export function BaseMap({
    children,
    className,
    heightClassName = "h-[320px] md:h-[400px]",
}: BaseMapProps) {
    return (
        <div
            className={cn(
                "leaflet-map overflow-hidden border border-border bg-muted/30",
                heightClassName,
                className,
            )}
        >
            {children}
        </div>
    );
}
