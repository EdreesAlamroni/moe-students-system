import { cn } from "@/lib/utils";

import { LoaderIcon } from "lucide-react";

export function LoadingData({ className }: { className?: string }) {
    return (
        <div className={cn("flex items-center justify-center flex-col gap-2 text-sm text-muted-foreground font-medium", className)}>
            <LoaderIcon className="animate-spin w-5 h-5 shrink-0" />
            <span>جارٍ تحميل البيانات</span>
        </div>
    );
}
