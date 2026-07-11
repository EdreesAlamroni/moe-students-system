import { lazy, Suspense, useMemo } from "react";

import { cn } from "@/lib/utils";

import { BaseMap } from "@/components/ui/maps/base-map";
import { parseMapPosition, toLatLngExpression } from "@/components/ui/maps/utils";

import { Skeleton } from "@/components/ui/structure/skeleton";

import EmptyState from "@/components/ui/display/empty-state";

import { MapPinOffIcon } from "lucide-react";

const ReadonlyLeafletMap = lazy(async () => {
    const module = await import("@/components/ui/maps/leaflet-map");

    return { default: module.ReadonlyLeafletMap };
});

type LocationShowMapProps = {
    latitude?: string | number | null;
    longitude?: string | number | null;
    className?: string;
    heightClassName?: string;
};

function MapLoadingFallback() {
    return <Skeleton className="h-full w-full" aria-hidden />;
}

export function LocationShowMap({
    latitude,
    longitude,
    className,
    heightClassName,
}: LocationShowMapProps) {
    const position = useMemo(
        () => parseMapPosition(latitude, longitude),
        [latitude, longitude],
    );

    if (!position) {
        return (
            <EmptyState
                icon={MapPinOffIcon}
                text="لا يوجد موقع محفوظ على الخريطة."
                className={cn("border border-border bg-muted/20", className)}
            />
        );
    }

    const center = toLatLngExpression(position);

    return (
        <BaseMap className={className} heightClassName={heightClassName}>
            <Suspense fallback={<MapLoadingFallback />}>
                <ReadonlyLeafletMap center={center} />
            </Suspense>
        </BaseMap>
    );
}
