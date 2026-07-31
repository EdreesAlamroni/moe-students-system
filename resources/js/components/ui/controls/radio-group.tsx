"use client"

import * as React from "react"

import { RadioGroup as RadioGroupPrimitive } from "radix-ui"

import { cn } from "@/lib/utils"
import { useDirection } from "@/hooks/use-direction"

function RadioGroup({
    className,
    ...props
}: React.ComponentProps<typeof RadioGroupPrimitive.Root>) {
    return (
        <RadioGroupPrimitive.Root
            data-slot="radio-group"
            className={cn("grid gap-3", className)}
            {...props}
        />
    )
}

function RadioGroupItem({
    className,
    children,
    ...props
}: React.ComponentProps<typeof RadioGroupPrimitive.Item>) {
    const direction = useDirection();

    return (
        <RadioGroupPrimitive.Item
            dir={direction}
            data-slot="radio-group-item"
            className={cn(
                "group relative flex cursor-pointer select-none items-center gap-3 rounded-none border border-input bg-background p-4 text-start outline-none",
                "hover:border-primary/40",
                "focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/30",
                "data-[state=checked]:border-primary data-[state=checked]:bg-primary/5",
                "disabled:cursor-not-allowed disabled:opacity-50",
                className,
            )}
            {...props}
        >
            <span
                className={cn(
                    "flex size-4 shrink-0 items-center justify-center rounded-full border",
                    "border-border group-data-[state=checked]:border-primary",
                )}
            >
                <RadioGroupPrimitive.Indicator className="flex items-center justify-center">
                    <span className="block size-2 rounded-full bg-primary" />
                </RadioGroupPrimitive.Indicator>
            </span>

            <span className="text-xs font-semibold uppercase tracking-widest text-muted-foreground group-data-[state=checked]:text-foreground">
                {children}
            </span>
        </RadioGroupPrimitive.Item>
    )
}

export { RadioGroup, RadioGroupItem }
