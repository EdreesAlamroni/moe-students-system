import { lazy, Suspense, useCallback, useEffect, useMemo, useState } from "react";
import type { LatLngExpression } from "leaflet";

import { cn } from "@/lib/utils";

import { BaseMap } from "@/components/ui/maps/base-map";
import { MapSearch } from "@/components/ui/maps/map-search";
import { DEFAULT_MAP_CENTER, DEFAULT_MAP_ZOOM, LOCATION_MAP_ZOOM } from "@/components/ui/maps/constants";
import { formatCoordinate, parseMapPosition, setInputValue, toLatLngExpression } from "@/components/ui/maps/utils";
import type { LocationSearchResult, MapPosition } from "@/components/ui/maps/utils";

import { Skeleton } from "@/components/ui/structure/skeleton";

import { Hint } from "@/components/ui/controls/hint";

const InteractiveLeafletMap = lazy(async () => {
    const module = await import("@/components/ui/maps/leaflet-map");

    return { default: module.InteractiveLeafletMap };
});

type LocationPickerProps = {
    latitudeInputId: string;
    longitudeInputId: string;
    initialLatitude?: string | number | null;
    initialLongitude?: string | number | null;
    className?: string;
};

function MapLoadingFallback() {
    return <Skeleton className="h-full w-full" aria-hidden />;
}

export function LocationPicker({
    latitudeInputId,
    longitudeInputId,
    initialLatitude = null,
    initialLongitude = null,
    className,
}: LocationPickerProps) {
    const initialPosition = useMemo(
        () => parseMapPosition(initialLatitude, initialLongitude),
        [initialLatitude, initialLongitude],
    );

    const [position, setPosition] = useState<MapPosition | null>(initialPosition);
    const [viewport, setViewport] = useState<{
        center: LatLngExpression;
        zoom: number;
    }>(() => ({
        center: initialPosition ? toLatLngExpression(initialPosition) : DEFAULT_MAP_CENTER,
        zoom: initialPosition ? LOCATION_MAP_ZOOM : DEFAULT_MAP_ZOOM,
    }));

    const syncInputs = useCallback((nextPosition: MapPosition | null) => {
        const latitudeInput = document.getElementById(latitudeInputId) as HTMLInputElement | null;
        const longitudeInput = document.getElementById(longitudeInputId) as HTMLInputElement | null;

        if (!nextPosition) {
            setInputValue(latitudeInput, "");
            setInputValue(longitudeInput, "");

            return;
        }

        setInputValue(latitudeInput, formatCoordinate(nextPosition.latitude));
        setInputValue(longitudeInput, formatCoordinate(nextPosition.longitude));
    }, [latitudeInputId, longitudeInputId]);

    const updatePosition = useCallback((nextPosition: MapPosition, focusMap = true) => {
        setPosition(nextPosition);
        syncInputs(nextPosition);

        if (focusMap) {
            setViewport({
                center: toLatLngExpression(nextPosition),
                zoom: LOCATION_MAP_ZOOM,
            });
        }
    }, [syncInputs]);

    useEffect(() => {
        const latitudeInput = document.getElementById(latitudeInputId) as HTMLInputElement | null;
        const longitudeInput = document.getElementById(longitudeInputId) as HTMLInputElement | null;

        if (!latitudeInput || !longitudeInput) {
            return;
        }

        const handleInputChange = () => {
            const nextPosition = parseMapPosition(latitudeInput.value, longitudeInput.value);

            if (!nextPosition) {
                setPosition(null);

                return;
            }

            setPosition(nextPosition);
            setViewport({
                center: toLatLngExpression(nextPosition),
                zoom: LOCATION_MAP_ZOOM,
            });
        };

        latitudeInput.addEventListener("input", handleInputChange);
        longitudeInput.addEventListener("input", handleInputChange);

        return () => {
            latitudeInput.removeEventListener("input", handleInputChange);
            longitudeInput.removeEventListener("input", handleInputChange);
        };
    }, [latitudeInputId, longitudeInputId]);

    useEffect(() => {
        syncInputs(initialPosition);
    }, [initialPosition, syncInputs]);

    const markerPosition = useMemo(
        () => (position ? toLatLngExpression(position) : null),
        [position],
    );

    const handleSearchSelect = useCallback((result: LocationSearchResult) => {
        updatePosition({
            latitude: result.latitude,
            longitude: result.longitude,
        });
    }, [updatePosition]);

    return (
        <div className={cn("flex flex-col gap-3", className)}>
            <MapSearch onSelect={handleSearchSelect} />

            <BaseMap>
                <Suspense fallback={<MapLoadingFallback />}>
                    <InteractiveLeafletMap
                        viewport={viewport}
                        markerPosition={markerPosition}
                        onPositionChange={updatePosition}
                    />
                </Suspense>
            </BaseMap>

            <Hint>
                ابحث عن موقع، أو انقر على الخريطة، أو اسحب العلامة لتحديد الإحداثيات بدقة.
            </Hint>
        </div>
    );
}
