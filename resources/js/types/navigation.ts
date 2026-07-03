import type { InertiaLinkProps } from '@inertiajs/react';

export type BreadcrumbItem = {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
};

export type NavItem = {
    key?: string;
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: string | null;
    /**
     * Route name pattern(s) that mark this item as active.
     * Supports Laravel-style wildcards (e.g. `school.students.*`).
     * Pass `false` to never mark the item as active.
     */
    activeRoutes?: string | string[] | false | null;
    /**
     * Route name pattern(s) that suppress the active state even when `activeRoutes` matches.
     */
    excludedRoutes?: string | string[] | null;
};

export type NavGroup = {
    title: string;
    items: NavItem[];
};

export type Navigation = {
    home: string | null;
    main: NavGroup[];
    account: {
        menu: NavItem[];
        tabs: NavItem[];
    };
};

export const emptyNavigation = (): Navigation => ({
    home: null,
    main: [],
    account: {
        menu: [],
        tabs: [],
    },
});
