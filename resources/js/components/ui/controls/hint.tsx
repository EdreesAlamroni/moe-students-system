import * as React from "react"

import { InfoIcon } from "lucide-react"

import { cn } from "@/lib/utils"

type HintProps = React.ComponentProps<"p"> & {
    hideIcon?: boolean
}

function Hint({
    className,
    children,
    hideIcon = false,
    ...props
}: HintProps) {
    return (
        <p
            data-slot="hint"
            className={cn(
                "flex items-center gap-1.5 text-xs leading-relaxed text-muted-foreground",
                className,
            )}
            {...props}
        >
            {!hideIcon && (
                <InfoIcon
                    className="mt-px size-3.5 shrink-0 text-muted-foreground/70"
                    aria-hidden
                />
            )}
            <span className="min-w-0 text-pretty">{children}</span>
        </p>
    )
}

export { Hint }
