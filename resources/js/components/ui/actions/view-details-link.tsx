import { Link } from "@inertiajs/react";

import { cn } from "@/lib/utils";

import { Button } from "@/components/ui/actions/button";

import { MoveLeftIcon } from "lucide-react";

interface ViewDetailsLinkProps {
    title?: string;
    href: string;
    showIcon?: boolean;
    className?: string;
}

export default function ViewDetailsLink({
    title = "عرض التفاصيل",
    href,
    showIcon = true,
    className,
}: ViewDetailsLinkProps) {
    return (
        <Button
            variant="link"
            className={cn(
                "group/details h-auto gap-1 p-0 text-xs font-medium normal-case tracking-normal",
                "no-underline decoration-primary/30 underline-offset-4",
                "transition-[color,text-decoration-color] duration-300 ease-out",
                "hover:underline hover:decoration-current",
                className,
            )}
            asChild
        >
            <Link href={href}>
                <span>{title}</span>
                {showIcon && (
                    <MoveLeftIcon
                        aria-hidden
                        className={cn(
                            "size-3.5 shrink-0 opacity-70 transition-opacity duration-300",
                            "group-hover/details:animate-gentle-drift group-hover/details:opacity-100",
                            "motion-reduce:group-hover/details:animate-none",
                        )}
                    />
                )}
            </Link>
        </Button>
    );
}
