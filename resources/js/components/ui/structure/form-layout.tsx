import React from "react";

import { cn } from "@/lib/utils";

type FormLayoutProps = React.HTMLAttributes<HTMLDivElement> & {
    children: React.ReactNode;
    size?: "default" | "sm";
}

export function FormLayout({ children, className = "", size = "sm", ...props }: FormLayoutProps) {
    return (
        <div
            data-slot="form-layout"
            data-size={size}
            className={cn(
                "flex flex-col gap-(--card-spacing) [--card-spacing:--spacing(8)] data-[size=sm]:[--card-spacing:--spacing(6)]",
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}

export function DialogFormLayout({ children, className = "", size = "sm", ...props }: FormLayoutProps) {
    return (
        <div
            data-slot="dialog-form-layout"
            data-size={size}
            className={cn(
                "flex flex-col gap-(--card-spacing) [--card-spacing:--spacing(8)] data-[size=sm]:[--card-spacing:--spacing(6)]",
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}
