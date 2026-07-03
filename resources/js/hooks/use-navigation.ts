import { usePage } from '@inertiajs/react';

import { emptyNavigation } from '@/types/navigation';

import type { Navigation } from '@/types';

export function useNavigation(): Navigation {
    const { navigation } = usePage<{ navigation: Navigation }>().props;

    return navigation ?? emptyNavigation();
}
