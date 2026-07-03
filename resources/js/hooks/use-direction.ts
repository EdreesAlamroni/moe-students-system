import * as React from 'react';

import { getDocumentDirection } from '@/lib/direction';

import type { Direction } from '@/types/ui';

export function useDirection(): Direction {
    const [direction, setDirection] = React.useState<Direction>(getDocumentDirection);

    React.useEffect(() => {
        // eslint-disable-next-line react-hooks/set-state-in-effect
        setDirection(getDocumentDirection());
    }, []);

    return direction;
}
