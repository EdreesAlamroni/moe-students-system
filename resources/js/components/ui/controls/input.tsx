import * as React from "react"

import { cn } from "@/lib/utils"

function Input({ className, type, hasErrors = false, ...props }: React.ComponentProps<"input"> & { hasErrors?: boolean }) {
    return (
        <input
            type={type}
            data-slot="input"
            aria-invalid={hasErrors}
            className={cn(
                "h-10 w-full min-w-0 px-2.5 py-2 bg-transparent text-sm border border-input outline-none transition-colors placeholder:text-muted-foreground",
                "focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary/50",
                "disabled:cursor-not-allowed disabled:opacity-50",
                "file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground",
                "aria-invalid:border-destructive aria-invalid:ring-destructive/30",
                className
            )}
            {...props}
        />
    )
}

export { Input }
