import * as React from "react"

import { cn } from "@/lib/utils"

function Textarea({ className, hasError, ...props }: React.ComponentProps<"textarea"> & { hasError?: boolean }) {
    const resolvedHasError = hasError ?? false;

    return (
        <textarea
            data-slot="textarea"
            className={cn(
                "min-h-24 w-full min-w-0 px-2.5 py-3 bg-transparent text-sm border border-input outline-none transition-colors placeholder:text-muted-foreground",
                "focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary/50",
                "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
                "aria-invalid:border-destructive aria-invalid:ring-destructive/30",
                hasError && "aria-invalid:border-destructive aria-invalid:ring-destructive/30",
                className,
            )}
            {...props}
        />
    )
}

export { Textarea }
