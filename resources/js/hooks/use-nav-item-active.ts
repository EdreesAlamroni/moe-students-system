import { useCallback } from 'react';

import { usePage } from '@inertiajs/react';

import { useCurrentUrl } from '@/hooks/use-current-url';

import { matchesRoute } from '@/lib/matches-route';

import type { NavItem } from '@/types';

export function useNavItemActive() {
    const { routeName } = usePage<{ routeName: string | null }>().props;
    const { isCurrentOrParentUrl } = useCurrentUrl();

    return useCallback(
        ({ href, activeRoutes, excludedRoutes }: NavItem): boolean => {
            if (activeRoutes === false) {
                return false;
            }

            if (excludedRoutes && matchesRoute(excludedRoutes, routeName)) {
                return false;
            }

            if (activeRoutes) {
                return matchesRoute(activeRoutes, routeName);
            }

            return isCurrentOrParentUrl(href);
        },
        [isCurrentOrParentUrl, routeName],
    );
}
