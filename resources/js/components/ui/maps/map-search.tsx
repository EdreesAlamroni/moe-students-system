import { useCallback, useEffect, useRef, useState } from "react";

import { cn } from "@/lib/utils";

import type { LocationSearchResult } from "@/components/ui/maps/utils";

import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput } from "@/components/ui/controls/input-group";
import InputError from "@/components/ui/controls/input-error";

import { LoadingData } from "@/components/ui/display/loading-data";

import { LoaderIcon, MapPinIcon, SearchIcon, XIcon } from "lucide-react";

import { search } from "@/routes/locations";

type MapSearchProps = {
    onSelect: (result: LocationSearchResult) => void;
    className?: string;
    disabled?: boolean;
};

const EMPTY_SEARCH_STATE = {
    query: "",
    results: [] as LocationSearchResult[],
    isSearching: false,
    error: null as string | null,
    isOpen: false,
};

export function MapSearch({ onSelect, className, disabled = false }: MapSearchProps) {
    const [query, setQuery] = useState(EMPTY_SEARCH_STATE.query);
    const [results, setResults] = useState(EMPTY_SEARCH_STATE.results);
    const [isSearching, setIsSearching] = useState(EMPTY_SEARCH_STATE.isSearching);
    const [error, setError] = useState(EMPTY_SEARCH_STATE.error);
    const [isOpen, setIsOpen] = useState(EMPTY_SEARCH_STATE.isOpen);
    const [prevDisabled, setPrevDisabled] = useState(disabled);
    const containerRef = useRef<HTMLDivElement>(null);
    const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

    if (disabled !== prevDisabled) {
        setPrevDisabled(disabled);

        if (disabled) {
            setQuery(EMPTY_SEARCH_STATE.query);
            setResults(EMPTY_SEARCH_STATE.results);
            setIsSearching(EMPTY_SEARCH_STATE.isSearching);
            setError(EMPTY_SEARCH_STATE.error);
            setIsOpen(EMPTY_SEARCH_STATE.isOpen);
        }
    }

    const clearSearch = useCallback(() => {
        setQuery(EMPTY_SEARCH_STATE.query);
        setResults(EMPTY_SEARCH_STATE.results);
        setError(EMPTY_SEARCH_STATE.error);
        setIsOpen(EMPTY_SEARCH_STATE.isOpen);
        setIsSearching(EMPTY_SEARCH_STATE.isSearching);
    }, []);

    const resetShortQueryState = useCallback(() => {
        setResults(EMPTY_SEARCH_STATE.results);
        setError(EMPTY_SEARCH_STATE.error);
        setIsSearching(EMPTY_SEARCH_STATE.isSearching);
        setIsOpen(EMPTY_SEARCH_STATE.isOpen);
    }, []);

    useEffect(() => {
        if (disabled) {
            return;
        }

        if (debounceRef.current) {
            clearTimeout(debounceRef.current);
        }

        const trimmedQuery = query.trim();

        if (trimmedQuery.length < 2) {
            return;
        }

        debounceRef.current = setTimeout(async () => {
            try {
                const response = await fetch(search.url({ query: { query: trimmedQuery } }), {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (!response.ok) {
                    throw new Error("search_failed");
                }

                const payload = await response.json() as { results: LocationSearchResult[] };

                setResults(payload.results);
                setIsOpen(payload.results.length > 0);
            } catch {
                setResults([]);
                setError("تعذّر البحث عن الموقع. حاول مرة أخرى.");
                setIsOpen(false);
            } finally {
                setIsSearching(false);
            }
        }, 400);

        return () => {
            if (debounceRef.current) {
                clearTimeout(debounceRef.current);
            }
        };
    }, [disabled, query]);

    useEffect(() => {
        const handlePointerDown = (event: MouseEvent) => {
            if (!containerRef.current?.contains(event.target as Node)) {
                setIsOpen(false);
            }
        };

        document.addEventListener("mousedown", handlePointerDown);

        return () => {
            document.removeEventListener("mousedown", handlePointerDown);
        };
    }, []);

    return (
        <div ref={containerRef} className={cn("relative", className)}>
            <InputGroup aria-disabled={disabled}>
                <InputGroupAddon>
                    <SearchIcon className="size-3.5 shrink-0 opacity-60" />
                </InputGroupAddon>

                <InputGroupInput
                    type="search"
                    value={query}
                    disabled={disabled}
                    placeholder="ابحث عن موقع..."
                    autoComplete="off"
                    onChange={(event) => {
                        const value = event.target.value;

                        setQuery(value);

                        if (value.trim().length < 2) {
                            resetShortQueryState();
                        } else {
                            setIsSearching(true);
                            setError(null);
                            setIsOpen(false);
                        }
                    }}
                    onFocus={() => {
                        if (results.length > 0) {
                            setIsOpen(true);
                        }
                    }}
                />

                {isSearching ? (
                    <InputGroupAddon align="inline-end">
                        <LoaderIcon className="size-3.5 shrink-0 animate-spin opacity-60" />
                    </InputGroupAddon>
                ) : query ? (
                    <InputGroupAddon align="inline-end">
                        <InputGroupButton
                            size="icon-xs"
                            variant="ghost"
                            aria-label="مسح البحث"
                            onClick={clearSearch}
                        >
                            <XIcon />
                        </InputGroupButton>
                    </InputGroupAddon>
                ) : null}
            </InputGroup>

            <InputError message={error ?? undefined} />

            {isOpen && results.length > 0 ? (
                <div className="absolute inset-x-0 top-full z-20 mt-1 max-h-56 overflow-y-auto border border-border bg-popover shadow-sm">
                    <ul className="divide-y divide-border">
                        {results.map((result) => (
                            <li key={result.id}>
                                <button
                                    type="button"
                                    className="flex w-full items-start gap-2 px-3 py-2.5 text-start text-sm transition-colors hover:bg-muted/60"
                                    onClick={() => {
                                        onSelect(result);
                                        clearSearch();
                                    }}
                                >
                                    <MapPinIcon className="mt-0.5 size-3.5 shrink-0 text-primary" />
                                    <span className="line-clamp-2 text-foreground">{result.label}</span>
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            ) : null}

            {isSearching && query.trim().length >= 2 ? (
                <div className="absolute inset-x-0 top-full z-10 mt-1 border border-border bg-popover px-3 py-4">
                    <LoadingData className="py-2" />
                </div>
            ) : null}
        </div>
    );
}
