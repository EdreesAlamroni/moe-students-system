import { useEffect } from "react";

import { MapContainer, Marker, TileLayer, useMap, useMapEvents } from "react-leaflet";
import L from "leaflet";
import type { LatLngExpression } from "leaflet";

import iconRetinaUrl from "leaflet/dist/images/marker-icon-2x.png";
import iconUrl from "leaflet/dist/images/marker-icon.png";
import shadowUrl from "leaflet/dist/images/marker-shadow.png";

import { LOCATION_MAP_ZOOM, TILE_LAYER_ATTRIBUTION, TILE_LAYER_URL } from "@/components/ui/maps/constants";

import { toLatLngExpression } from "@/components/ui/maps/utils";
import type { MapPosition } from "@/components/ui/maps/utils";

const defaultMarkerIcon = new L.Icon({
    iconUrl,
    iconRetinaUrl,
    shadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
});

L.Icon.Default.mergeOptions({
    iconRetinaUrl,
    iconUrl,
    shadowUrl,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41],
});

type MapViewport = {
    center: LatLngExpression;
    zoom: number;
};

type InteractiveLeafletMapProps = {
    viewport: MapViewport;
    markerPosition: [number, number] | null;
    onPositionChange: (position: MapPosition, focusMap?: boolean) => void;
};

function MapViewportController({
    center,
    zoom,
}: {
    center: LatLngExpression;
    zoom: number;
}) {
    const map = useMap();

    useEffect(() => {
        map.flyTo(center, zoom, { duration: 0.75 });
    }, [center, map, zoom]);

    return null;
}

function MapClickHandler({
    onPositionChange,
}: {
    onPositionChange: InteractiveLeafletMapProps["onPositionChange"];
}) {
    useMapEvents({
        click(event) {
            onPositionChange({
                latitude: event.latlng.lat,
                longitude: event.latlng.lng,
            });
        },
    });

    return null;
}

export function InteractiveLeafletMap({
    viewport,
    markerPosition,
    onPositionChange,
}: InteractiveLeafletMapProps) {
    return (
        <MapContainer
            center={viewport.center}
            zoom={viewport.zoom}
            scrollWheelZoom
            className="h-full w-full"
        >
            <TileLayer attribution={TILE_LAYER_ATTRIBUTION} url={TILE_LAYER_URL} />

            <MapViewportController center={viewport.center} zoom={viewport.zoom} />

            <MapClickHandler onPositionChange={onPositionChange} />

            {markerPosition ? (
                <Marker
                    draggable
                    icon={defaultMarkerIcon}
                    position={markerPosition}
                    eventHandlers={{
                        dragend(event) {
                            const { lat, lng } = event.target.getLatLng();

                            onPositionChange(
                                {
                                    latitude: lat,
                                    longitude: lng,
                                },
                                false,
                            );
                        },
                    }}
                />
            ) : null}
        </MapContainer>
    );
}

type ReadonlyLeafletMapProps = {
    center: [number, number];
};

function MapCenterController({ center }: { center: LatLngExpression }) {
    const map = useMap();

    useEffect(() => {
        map.setView(center, LOCATION_MAP_ZOOM);
    }, [center, map]);

    return null;
}

export function ReadonlyLeafletMap({ center }: ReadonlyLeafletMapProps) {
    return (
        <MapContainer
            center={center}
            zoom={LOCATION_MAP_ZOOM}
            scrollWheelZoom
            dragging
            className="h-full w-full"
        >
            <TileLayer attribution={TILE_LAYER_ATTRIBUTION} url={TILE_LAYER_URL} />
            <MapCenterController center={center} />
            <Marker icon={defaultMarkerIcon} position={toLatLngExpression({ latitude: center[0], longitude: center[1] })} />
        </MapContainer>
    );
}
