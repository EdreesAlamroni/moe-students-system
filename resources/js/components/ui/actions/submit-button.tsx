import React from "react";

import { VariantProps } from "class-variance-authority";

import { cn } from "@/lib/utils";

import { Spinner } from "@/components/ui/display/spinner";

import { Button, buttonVariants } from "@/components/ui/actions/button";

import { CheckCircleIcon, LoaderCircleIcon } from "lucide-react";

type BaseSubmitButtonProps = {
    title?: string;
    processing?: boolean;
    className?: string;
    variant?: VariantProps<typeof buttonVariants>["variant"];
} & React.ButtonHTMLAttributes<HTMLButtonElement>

type SubmitButtonProps = BaseSubmitButtonProps & {
    mode?: "create" | "update" | "confirm" | "save";
}

export function SubmitButton({
    title,
    mode = "save",
    processing,
    className = "",
    variant = "default",
    ...props
}: SubmitButtonProps) {
    let defaultTitle;

    switch (mode) {
        case "create":
            defaultTitle = "إضـافـة";
            break;
        case "update":
            defaultTitle = "تـحـديـث";
            break;
        case "confirm":
            defaultTitle = "تـأكيد";
            break;
        case "save":
            defaultTitle = "حـفـظ";
            break;
        default:
            defaultTitle = "حـفـظ";
    }

    return (
        <Button
            variant={variant}
            type="submit"
            className={cn("flex items-center gap-x-2", className)}
            disabled={processing}
            {...props}
        >
            {processing ? (
                // <LoaderCircleIcon className="animate-spin" />
                <Spinner />
            ) : (
                <CheckCircleIcon />
            )}
            <span>{title || defaultTitle}</span>
        </Button>
    );
}

export function CreateButton({ className = "", ...props }: BaseSubmitButtonProps) {
    return <SubmitButton {...props} mode="create" className={className} />;
}

export function UpdateButton({ className = "", ...props }: BaseSubmitButtonProps) {
    return <SubmitButton {...props} mode="update" className={className} />;
}

export function ConfirmButton({ className = "", ...props }: BaseSubmitButtonProps) {
    return <SubmitButton {...props} mode="confirm" className={className} />;
}
