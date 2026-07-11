import * as React from "react"

import { ChevronDownIcon } from "lucide-react"

import { cn } from "@/lib/utils"

import { Input } from "@/components/ui/controls/input"

type EmptyOptionsInputProps = Omit<
    React.ComponentProps<"input">,
    "type" | "value" | "defaultValue" | "disabled" | "readOnly" | "placeholder"
> & {
    placeholder?: string
}

function EmptyOptionsInput({
    placeholder = "لا توجد خيارات متاحة",
    className,
    ...props
}: EmptyOptionsInputProps) {
    return (
        <div className="relative opacity-50">
            <Input
                type="text"
                placeholder={placeholder}
                disabled
                readOnly
                tabIndex={-1}
                aria-disabled="true"
                className={cn("pe-8 disabled:opacity-100", className)}
                {...props}
            />
            <div
                className="pointer-events-none absolute inset-y-0 inset-e-0 flex items-center pe-3"
                aria-hidden="true"
            >
                <ChevronDownIcon className="size-3.5 text-muted-foreground" />
            </div>
        </div>
    )
}

export { EmptyOptionsInput }
