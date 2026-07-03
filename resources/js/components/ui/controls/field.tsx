import React from "react";

import { cn } from "@/lib/utils";

type FieldProps = React.HTMLAttributes<HTMLDivElement> & {
    children: React.ReactNode;
}

export default function Field({ className = "", children, ...props }: FieldProps) {
    return (
        <div className={cn("flex flex-col gap-2", className)} {...props}>
            {children}
        </div>
    );
}
