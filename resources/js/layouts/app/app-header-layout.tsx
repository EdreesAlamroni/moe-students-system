import { AppContent } from '@/components/layouts/app-content';
import { AppHeader } from '@/components/layouts/header/app-header';
import { AppShell } from '@/components/layouts/app-shell';

import type { AppLayoutProps } from '@/types';

export default function AppHeaderLayout({
    children,
    breadcrumbs,
}: AppLayoutProps) {
    return (
        <AppShell variant="header">
            <AppHeader breadcrumbs={breadcrumbs} />
            <AppContent variant="header">{children}</AppContent>
        </AppShell>
    );
}
