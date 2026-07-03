import React from "react";

import { cn } from "@/lib/utils";

type ActionsSectionProps = {
    children: React.ReactNode;
} & React.HTMLAttributes<HTMLElement>;

export default function ActionsSection({ className, children, ...props }: ActionsSectionProps) {
    return (
        <section
            className={cn(
                "flex flex-wrap items-center justify-start gap-x-3 gap-y-2 md:justify-end",
                className,
            )}
            {...props}
        >
            {children}
        </section>
    );
}
