import type { Direction } from '@/types/ui';

export const DEFAULT_DIRECTION: Direction = 'rtl';

export function getDocumentDirection(): Direction {
    if (typeof document === 'undefined') {
        return DEFAULT_DIRECTION;
    }

    return document.documentElement.dir === 'ltr' ? 'ltr' : 'rtl';
}

export function isRtlDirection(direction: Direction = getDocumentDirection()): boolean {
    return direction === 'rtl';
}
