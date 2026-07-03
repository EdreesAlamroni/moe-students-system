import React from "react";

import { Link } from "@inertiajs/react";

import { VariantProps } from "class-variance-authority";

import { cn } from "@/lib/utils";

import { Icon } from "@/components/ui/display/icon";

import { Button, buttonVariants } from "@/components/ui/actions/button";

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alerts/alert-dialog";

import { ArchiveRestoreIcon, CheckCircleIcon, Trash2Icon } from "lucide-react";

export type ConfirmMode = "delete" | "restore" | "forceDelete";

const MODE_CONFIG: Record<ConfirmMode, {
    buttonTitle: string;
    title: string;
    description: string;
    cancelText: string;
    confirmText: string;
    method: "post" | "put" | "patch" | "delete";
    variant: VariantProps<typeof buttonVariants>["variant"];
    icon: React.ComponentType<React.SVGProps<SVGSVGElement>>;
}> = {
    delete: {
        buttonTitle: "حذف",
        title: "هل أنت متأكد من الحذف ؟",
        description: "لن تتمكن من التراجع عن هذا اﻹجراء.",
        cancelText: "إلغاء الأمر",
        confirmText: "تـأكـيـد",
        method: "delete",
        variant: "destructive",
        icon: Trash2Icon,
    },
    restore: {
        buttonTitle: "إستعادة",
        title: "هل أنت متأكد من الإستعادة ؟",
        description: "لن تتمكن من التراجع عن هذا اﻹجراء.",
        cancelText: "إلغاء الأمر",
        confirmText: "تـأكـيـد",
        method: "post",
        variant: "outline",
        icon: ArchiveRestoreIcon,
    },
    forceDelete: {
        buttonTitle: "حذف نهائي",
        title: "هل أنت متأكد من الحذف النهائي ؟",
        description: "لن تتمكن من التراجع عن هذا اﻹجراء.",
        cancelText: "إلغاء الأمر",
        confirmText: "تـأكـيـد",
        method: "delete",
        variant: "destructive",
        icon: Trash2Icon,
    },
}

export interface ConfirmationActionProps extends Omit<React.ComponentProps<typeof Button>, "onClick"> {
    title?: string;
    mode: ConfirmMode;
    href: string;
    method?: "post" | "put" | "patch" | "delete";
    variant?: VariantProps<typeof buttonVariants>["variant"];
    className?: string;
}

export function ConfirmationAction({
    title,
    mode,
    href,
    method,
    variant,
    className,
    ...buttonProps
}: ConfirmationActionProps) {
    const config = MODE_CONFIG[mode];

    const resolvedMethod = method ?? config.method;
    const resolvedVariant = variant ?? config.variant;

    return (
        <AlertDialog>
            <AlertDialogTrigger asChild>
                <Button variant={resolvedVariant} className={cn(className)} {...buttonProps}>
                    {config.icon && <Icon iconNode={config.icon} />}
                    {title ?? config.buttonTitle}
                </Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>{config.title}</AlertDialogTitle>
                    <AlertDialogDescription>{config.description}</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>{config.cancelText}</AlertDialogCancel>
                    <AlertDialogAction asChild>
                        <Link href={href} method={resolvedMethod} as="button">
                            <CheckCircleIcon />
                            {config.confirmText}
                        </Link>
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

export function ConfirmDeleteAction({ ...props }: Omit<ConfirmationActionProps, "mode">) {
    return (
        <ConfirmationAction {...props} mode="delete" />
    )
}

export function ConfirmRestoreAction({ ...props }: Omit<ConfirmationActionProps, "mode">) {
    return (
        <ConfirmationAction {...props} mode="restore" />
    )
}

export function ConfirmForceDeleteAction({ ...props }: Omit<ConfirmationActionProps, "mode">) {
    return (
        <ConfirmationAction {...props} mode="forceDelete" />
    )
}
