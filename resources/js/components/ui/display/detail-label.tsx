import * as React from "react"

import { cn } from "@/lib/utils"

type DetailLabelProps = React.ComponentProps<"dt">

function DetailLabel({ className, children, ...props }: DetailLabelProps) {
    return (
        <dt
            data-slot="detail-label"
            className={cn(
                "text-sm font-medium tracking-wide uppercase select-none",
                className,
            )}
            {...props}
        >
            {children}
        </dt>
    )
}

type DetailFieldDescriptionProps = React.ComponentProps<"p">

function DetailFieldDescription({
    className,
    children,
    ...props
}: DetailFieldDescriptionProps) {
    return (
        <p
            data-slot="detail-field-description"
            className={cn("text-xs leading-relaxed text-muted-foreground", className)}
            {...props}
        >
            {children}
        </p>
    )
}

export { DetailFieldDescription, DetailLabel }
