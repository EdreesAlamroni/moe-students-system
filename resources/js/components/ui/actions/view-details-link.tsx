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

export default function ViewDetailsLink({ title = "عرض التفاصيل", href, showIcon = true, className = "" }: ViewDetailsLinkProps) {
    return (
        <Button
            variant="link"
            className={cn(
                "text-xs group cursor-pointer h-auto p-0",
                className,
            )}
            asChild
        >
            <Link href={href}>
                <span>{title}</span>
                {showIcon && <MoveLeftIcon className="w-4 h-4 group-hover:animate-pulse" />}
            </Link>
        </Button>
    );
}
