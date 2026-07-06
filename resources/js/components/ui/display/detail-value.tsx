import * as React from "react"

import { cn } from "@/lib/utils"

function hasRenderableChildren(node: React.ReactNode): boolean {
    return React.Children.count(node) > 0
}

function isEmptyValue(val: unknown): boolean {
    if (val == null) {
        return true
    }

    if (typeof val === "string") {
        return val.trim().length === 0
    }

    if (typeof val === "number" || typeof val === "boolean") {
        return false
    }

    if (Array.isArray(val)) {
        return val.length === 0
    }

    if (typeof val === "object" && !React.isValidElement(val)) {
        return Object.keys(val as object).length === 0
    }

    return false
}

function shouldUseFontMonoFallback(fallback: React.ReactNode): boolean {
    if (typeof fallback === "number") {
        return true
    }

    if (typeof fallback !== "string") {
        return false
    }

    const trimmed = fallback.trim()

    return (
        trimmed === "-" ||
        trimmed === "0" ||
        (trimmed !== "" && !Number.isNaN(Number(trimmed)))
    )
}

type DetailValueProps = {
    value?: React.ReactNode
    fallback?: React.ReactNode
    fontMono?: boolean
    multiline?: boolean
    variant?: "default" | "plain"
    children?: React.ReactNode
} & Omit<React.ComponentProps<"dd">, "children">

function DetailValue({
    value,
    fallback = "-",
    fontMono = false,
    multiline = false,
    variant = "default",
    className,
    children,
    ...props
}: DetailValueProps) {
    const hasChildren = hasRenderableChildren(children)
    const isBlank = !hasChildren && isEmptyValue(value)
    const useFontMonoFallback = isBlank && shouldUseFontMonoFallback(fallback)
    const useFontMono = !hasChildren && (fontMono || useFontMonoFallback)
    const isPlain = variant === "plain"

    const content = hasChildren
        ? children
        : isBlank
            ? fallback
            : value

    return (
        <dd
            data-slot="detail-value"
            data-empty={isBlank ? "" : undefined}
            data-variant={variant}
            className={cn(
                "flex min-h-10 w-full min-w-0 text-sm text-foreground",
                isPlain
                    ? "items-center"
                    : cn(
                        "items-center gap-2 border border-input bg-muted/30 px-2.5 py-2",
                        multiline && "items-start whitespace-pre-wrap",
                    ),
                useFontMono && "font-mono",
                isBlank && !useFontMonoFallback && "text-muted-foreground",
                className,
            )}
            {...props}
        >
            {content}
        </dd>
    )
}

export { DetailValue }
