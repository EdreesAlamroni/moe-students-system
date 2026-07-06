import React from "react";

import { cn } from "@/lib/utils";

type FormLayoutProps = React.HTMLAttributes<HTMLDivElement> & {
    children: React.ReactNode;
}

export function FormLayout({ children, className = "", ...props }: FormLayoutProps) {
    return (
        <div
            className={cn(
                "flex flex-col gap-(--card-spacing)",
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}

export function DialogFormLayout({ children, className = "", ...props }: FormLayoutProps) {
    return (
        <div
            className={cn(
                "flex flex-col gap-(--card-spacing)",
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}
