import { Link } from '@inertiajs/react';

import { useDirection } from '@/hooks/use-direction';
import { useNavigation } from '@/hooks/use-navigation';

import { isRtlDirection } from '@/lib/direction';

import AppLogo from '@/components/layouts/app-logo';

import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/layouts/sidebar/sidebar';

import { NavMain } from '@/components/layouts/navigation/nav-main';
import { NavFooter } from '@/components/layouts/navigation/nav-footer';
import { NavUser } from '@/components/layouts/navigation/nav-user';

import type { NavItem } from '@/types';

const footerNavItems: NavItem[] = [];

export function AppSidebar() {
    const navigation = useNavigation();
    const isRtl = isRtlDirection(useDirection());

    return (
        <Sidebar collapsible="icon" variant="floating" side={isRtl ? 'right' : 'left'}>
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="md" asChild className="group-data-[collapsible=icon]:p-0! gap-1!">
                            {navigation.home ? (
                                <Link href={navigation.home} prefetch>
                                    <AppLogo />
                                </Link>
                            ) : (
                                <span>
                                    <AppLogo />
                                </span>
                            )}
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain groups={navigation.main} />
            </SidebarContent>

            <SidebarFooter>
                {footerNavItems.length > 0 && <NavFooter items={footerNavItems} className="mt-auto" />}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
