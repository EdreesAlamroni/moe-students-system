import { usePage } from '@inertiajs/react';

import { emptyNavigation } from '@/types/navigation';

import type { Navigation } from '@/types';

export function useNavigation(): Navigation {
    const { navigation } = usePage().props;

    return navigation ?? emptyNavigation();
}
