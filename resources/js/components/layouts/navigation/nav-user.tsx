import { usePage } from '@inertiajs/react';

import { useIsMobile } from '@/hooks/use-mobile';

import { SidebarMenu, SidebarMenuButton, SidebarMenuItem, useSidebar } from '@/components/layouts/sidebar/sidebar';

import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/navigation/dropdown-menu';
import { UserInfo } from '@/components/layouts/navigation/user-info';
import { UserMenuContent } from '@/components/layouts/navigation/user-menu-content';

import type { Auth } from "@/types";

import { ChevronsUpDown } from 'lucide-react';

export function NavUser() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();

    if (!auth.user) {
        return null;
    }

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="md"
                            className="group sidebar-accent-foreground data-[state=open]:bg-sidebar-accent group-data-[collapsible=icon]:p-0!"
                            data-test="sidebar-menu-button"
                        >
                            <UserInfo user={auth.user} showEmail={false} />
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="end"
                        side={
                            isMobile
                                ? 'bottom'
                                : state === 'collapsed'
                                    ? 'left'
                                    : 'bottom'
                        }
                    >
                        <UserMenuContent user={auth.user} />
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
