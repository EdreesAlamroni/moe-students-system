import { COORDINATE_DECIMALS } from "@/components/ui/maps/constants";

export type MapPosition = {
    latitude: number;
    longitude: number;
};

export type LocationSearchResult = {
    id: number;
    label: string;
    latitude: number;
    longitude: number;
};

export function formatCoordinate(value: number): string {
    return value.toFixed(COORDINATE_DECIMALS).replace(/\.?0+$/, "");
}

export function parseCoordinate(value: string | number | null | undefined): number | null {
    if (value === null || value === undefined || value === "") {
        return null;
    }

    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
}

export function parseMapPosition(
    latitude: string | number | null | undefined,
    longitude: string | number | null | undefined,
): MapPosition | null {
    const parsedLatitude = parseCoordinate(latitude);
    const parsedLongitude = parseCoordinate(longitude);

    if (parsedLatitude === null || parsedLongitude === null) {
        return null;
    }

    if (parsedLatitude < -90 || parsedLatitude > 90 || parsedLongitude < -180 || parsedLongitude > 180) {
        return null;
    }

    return {
        latitude: parsedLatitude,
        longitude: parsedLongitude,
    };
}

export function toLatLngExpression(position: MapPosition): [number, number] {
    return [position.latitude, position.longitude];
}

export function setInputValue(input: HTMLInputElement | null, value: string): void {
    if (!input || input.value === value) {
        return;
    }

    input.value = value;
    input.dispatchEvent(new Event("input", { bubbles: true }));
    input.dispatchEvent(new Event("change", { bubbles: true }));
}
