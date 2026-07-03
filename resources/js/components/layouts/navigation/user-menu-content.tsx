import { Fragment } from 'react';

import { Link, router } from '@inertiajs/react';

import { useDirection } from '@/hooks/use-direction';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { useNavigation } from '@/hooks/use-navigation';

import { Icon } from '@/components/ui/display/icon';
import { UserInfo } from '@/components/layouts/navigation/user-info';

import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/navigation/dropdown-menu';

import type { NavItem, User } from '@/types';

import { welcome } from "@/routes";

type Props = {
    user: User;
};

function MenuLinkItem({
    item,
    onClick,
}: {
    item: NavItem;
    onClick?: () => void;
}) {
    return (
        <DropdownMenuItem asChild>
            <Link
                className="block w-full cursor-pointer"
                href={item.href}
                prefetch
                onClick={onClick}
            >
                <Icon iconNode={item.icon} className="ms-2" />
                {item.title}
            </Link>
        </DropdownMenuItem>
    );
}

export function UserMenuContent({ user }: Props) {
    const cleanup = useMobileNavigation();
    const { account } = useNavigation();
    const direction = useDirection();

    const menuItems = account.menu.filter((item) => item.key !== 'logout');
    const logoutItem = account.menu.find((item) => item.key === 'logout');

    const handleLogout = () => {
        cleanup();
        router.flushAll();
    };

    return (
        <>
            <DropdownMenuLabel className="p-0 font-normal" dir={direction}>
                <div className="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                    <UserInfo user={user} showEmail={false} />
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            {menuItems.length > 0 && (
                <DropdownMenuGroup dir={direction}>
                    {menuItems.map((item, index) => (
                        <Fragment key={item.key ?? item.title}>
                            {index > 0 && <DropdownMenuSeparator />}
                            <MenuLinkItem item={item} onClick={cleanup} />
                        </Fragment>
                    ))}
                </DropdownMenuGroup>
            )}
            {logoutItem && (
                <>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild dir={direction}>
                        <Link
                            className="block w-full cursor-pointer"
                            href={logoutItem.href}
                            method="post"
                            as="button"
                            onClick={handleLogout}
                            data-test="logout-button"
                            onSuccess={() => window.location.replace(welcome.url())}
                        >
                            <Icon iconNode={logoutItem.icon} className="ms-2" />
                            {logoutItem.title}
                        </Link>
                    </DropdownMenuItem>
                </>
            )}
        </>
    );
}
