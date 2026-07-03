import { Link, usePage } from "@inertiajs/react";

import { cn } from "@/lib/utils";

import { useDirection } from "@/hooks/use-direction";
import { useInitials } from "@/hooks/use-initials";
import { useNavItemActive } from "@/hooks/use-nav-item-active";
import { useNavigation } from "@/hooks/use-navigation";

import { isRtlDirection } from "@/lib/direction";

import AppLogo from "@/components/layouts/app-logo";
import AppLogoIcon from "@/components/layouts/app-logo-icon";

import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/components/layouts/sheet";
import { Breadcrumbs } from "@/components/layouts/breadcrumbs";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/display/avatar";
import { Icon } from "@/components/ui/display/icon";

import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from "@/components/ui/navigation/dropdown-menu";
import { NavigationMenu, NavigationMenuItem, NavigationMenuList, navigationMenuTriggerStyle } from "@/components/ui/navigation/navigation-menu";
import { UserMenuContent } from "@/components/layouts/navigation/user-menu-content";

import { Button } from "@/components/ui/actions/button";

import type { Auth, BreadcrumbItem } from "@/types";

import { Menu } from "lucide-react";

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

const activeItemStyles = "text-zinc-900";

export function AppHeader({ breadcrumbs = [] }: Props) {
    const page = usePage<{ auth: Auth; }>();
    const { auth } = page.props;
    const navigation = useNavigation();
    const isNavItemActive = useNavItemActive();
    const direction = useDirection();
    const isRtl = isRtlDirection(direction);

    const mainNavItems = navigation.main.flatMap((group) => group.items);

    const getInitials = useInitials();

    return (
        <>
            <div className="border-b border-sidebar-border/80">
                <div className="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                    {/* Mobile Menu */}
                    <div className="lg:hidden">
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="me-2 h-[34px] w-[34px]"
                                >
                                    <Menu className="h-5 w-5" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent
                                side={isRtl ? 'right' : 'left'}
                                className="flex h-full w-64 flex-col items-stretch justify-between bg-sidebar"
                            >
                                <SheetTitle className="sr-only">
                                    Navigation menu
                                </SheetTitle>
                                <SheetHeader className="flex justify-start text-left">
                                    <AppLogoIcon className="h-6 w-6 fill-current text-black" />
                                </SheetHeader>
                                <div className="flex h-full flex-1 flex-col space-y-4 p-4">
                                    <div className="flex h-full flex-col justify-between text-sm">
                                        <div className="flex flex-col space-y-4">
                                            {mainNavItems.map((item) => (
                                                <Link
                                                    key={item.key ?? item.title}
                                                    href={item.href}
                                                    className="flex items-center space-x-2 font-medium"
                                                >
                                                    <Icon iconNode={item.icon} className="h-5 w-5" />
                                                    <span>{item.title}</span>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>

                    {navigation.home ? (
                        <Link
                            href={navigation.home}
                            prefetch
                            className="flex items-center gap-x-2"
                        >
                            <AppLogo />
                        </Link>
                    ) : (
                        <span className="flex items-center gap-x-2">
                            <AppLogo />
                        </span>
                    )}

                    {/* Desktop Navigation */}
                    <div className="ms-6 hidden h-full items-center gap-x-6 lg:flex">
                        <NavigationMenu className="flex h-full items-stretch">
                            <NavigationMenuList className="flex h-full items-stretch space-x-2">
                                {mainNavItems.map((item) => (
                                    <NavigationMenuItem
                                        key={item.key ?? item.title}
                                        className="relative flex h-full items-center"
                                    >
                                        <Link
                                            href={item.href}
                                            className={cn(
                                                navigationMenuTriggerStyle(),
                                                isNavItemActive(item) && activeItemStyles,
                                                "h-9 cursor-pointer px-3",
                                            )}
                                        >
                                            {isRtl ? (
                                                <>
                                                    {item.title}
                                                    <Icon iconNode={item.icon} className="ms-2 h-4 w-4" />
                                                </>
                                            ) : (
                                                <>
                                                    <Icon iconNode={item.icon} className="me-2 h-4 w-4" />
                                                    {item.title}
                                                </>
                                            )}
                                        </Link>
                                        {isNavItemActive(item) && (
                                            <div className="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black"></div>
                                        )}
                                    </NavigationMenuItem>
                                ))}
                            </NavigationMenuList>
                        </NavigationMenu>
                    </div>

                    <div className="ms-auto flex items-center gap-x-2">
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    className="size-10 rounded-full p-1"
                                >
                                    <Avatar className="size-8 overflow-hidden rounded-full">
                                        <AvatarImage
                                            src={auth.user?.avatar}
                                            alt={auth.user?.name}
                                        />
                                        <AvatarFallback className="rounded-lg bg-zinc-200 text-black">
                                            {getInitials(auth.user?.name ?? "")}
                                        </AvatarFallback>
                                    </Avatar>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-56" align={isRtl ? 'start' : 'end'}>
                                {auth.user && (
                                    <UserMenuContent user={auth.user} />
                                )}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>
            </div>
            {breadcrumbs.length > 1 && (
                <div className="flex w-full border-b border-sidebar-border/70">
                    <div className="mx-auto flex h-12 w-full items-center justify-start px-4 text-zinc-500 md:max-w-7xl">
                        <Breadcrumbs breadcrumbs={breadcrumbs} />
                    </div>
                </div>
            )}
        </>
    );
}
