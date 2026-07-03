import { useEffect } from "react";

import { router } from "@inertiajs/react";
import { toast } from "sonner";

import { Toaster } from "@/components/ui/alerts/sonner";

import type { FlashMessage } from "@/types/ui";

const titles = {
    success: "تمت العملية بنجاح!",
    error: "عذراً، حدث خطأ!",
    warning: "تحذير!",
    info: "تنبيه!",
} as const;

function showFlash(flash: FlashMessage | null | undefined): void {
    if (!flash?.message) {
        return;
    }

    const { level, message } = flash;
    const title = titles[level as keyof typeof titles];

    if (title) {
        toast[level as keyof typeof titles](title, { description: message });
        return;
    }

    toast(message);
}

interface FlashProps {
    flash: FlashMessage;
}

export default function Flash({ flash }: FlashProps) {

    useEffect(() => {
        showFlash(flash);
    }, []);

    useEffect(() => {
        return router.on("success", (event) => {
            showFlash(event.detail.page.props.flash);
        });
    }, []);

    return <Toaster />;
}
