import { createInertiaApp } from "@inertiajs/react";

import AppLayout from "@/layouts/app-layout";
import AuthLayout from "@/layouts/auth-layout";
import AccountSettingsLayout from "@/layouts/account-settings/layout";

import { TooltipProvider } from "@/components/ui/overlay/tooltip";

import Flash from "./components/ui/alerts/flash";

const appName = import.meta.env.VITE_APP_NAME || "وزارة التربية والتعليم";

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === "welcome":
                return null;
            case name.startsWith("auth/"):
                return AuthLayout;
            case name.startsWith("account-settings/"):
                return [AppLayout, AccountSettingsLayout];
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app, { page }) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}

                <Flash flash={page.props.flash} />
            </TooltipProvider>
        );
    },
    progress: {
        color: "oklch(0.38 0.14 266)",
    },
});
