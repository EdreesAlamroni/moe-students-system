import * as React from "react"

import { cn } from "@/lib/utils"

type DetailFieldProps = React.HTMLAttributes<HTMLDivElement> & {
    children: React.ReactNode
}

function DetailField({ className, children, ...props }: DetailFieldProps) {
    return (
        <div
            data-slot="detail-field"
            className={cn("group/detail-field flex min-w-0 flex-col gap-2", className)}
            {...props}
        >
            {children}
        </div>
    )
}

const columnClasses = {
    1: "",
    2: "md:grid-cols-2",
    3: "md:grid-cols-2 lg:grid-cols-3",
    4: "md:grid-cols-2 xl:grid-cols-4",
} as const

type DetailFieldsProps = React.ComponentProps<"dl"> & {
    columns?: keyof typeof columnClasses
}

function DetailFields({
    columns = 1,
    className,
    children,
    ...props
}: DetailFieldsProps) {
    return (
        <dl
            data-slot="detail-fields"
            className={cn(
                "grid grid-cols-1 gap-6",
                columnClasses[columns],
                className,
            )}
            {...props}
        >
            {children}
        </dl>
    )
}

export { DetailField, DetailFields }
