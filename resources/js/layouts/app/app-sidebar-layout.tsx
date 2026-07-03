import { AppContent } from '@/components/layouts/app-content';
import { AppShell } from '@/components/layouts/app-shell';
import { AppSidebar } from '@/components/layouts/sidebar/app-sidebar';
import { AppSidebarHeader } from '@/components/layouts/sidebar/app-sidebar-header';
import type { AppLayoutProps } from '@/types';

export default function AppSidebarLayout({
    children,
    breadcrumbs = [],
}: AppLayoutProps) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
