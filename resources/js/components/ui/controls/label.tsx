import * as React from "react"

import { Label as LabelPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"

type LabelProps = React.ComponentProps<typeof LabelPrimitive.Root> & {
    hasError?: boolean
    required?: boolean
}

function Label({
    className,
    hasError = false,
    required = false,
    ...props
}: LabelProps) {
    return (
        <LabelPrimitive.Root
            data-slot="label"
            className={cn(
                "relative block text-sm font-medium tracking-wide uppercase select-none",
                "group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50",
                "peer-disabled:cursor-not-allowed peer-disabled:opacity-50",
                "peer-data-[slot=checkbox]:text-sm peer-data-[slot=checkbox]:font-normal peer-data-[slot=checkbox]:tracking-normal peer-data-[slot=checkbox]:normal-case peer-data-[slot=radio-group-item]:text-sm peer-data-[slot=radio-group-item]:font-normal peer-data-[slot=radio-group-item]:tracking-normal peer-data-[slot=radio-group-item]:normal-case peer-data-[slot=switch]:text-sm peer-data-[slot=switch]:font-normal peer-data-[slot=switch]:tracking-normal peer-data-[slot=switch]:normal-case",
                className,
                required && "after:absolute after:content-['*'] after:top-[-5px] after:text-destructive after:ms-1 after:text-sm after:font-normal after:align-middle",
            )}
            {...props}
        />
    )
}

export { Label }
