import React from "react";

import { cn } from "@/lib/utils";

import { DayPicker } from "react-day-picker";
import { format, parseISO, startOfDay } from "date-fns";
import { ar } from "date-fns/locale";

import { Calendar } from "@/components/ui/controls/calendar"

import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/overlay/popover"

import { CalendarIcon } from "lucide-react"

type DatePickerProps = {
    id?: string;
    name?: string;
    className?: string;
    hasError?: boolean;
    date?: Date | string;
    setDate?: (date: string | undefined) => void;
    placeholder?: string;
    min?: Date | string;
    max?: Date | string;
    startMonth?: Date;
    endMonth?: Date;
} & Omit<React.ComponentProps<typeof DayPicker>, "onSelect" | "selected" | "mode">

export default function DatePicker({
    id = "date-picker",
    name,
    className = "",
    hasError = false,
    date,
    setDate,
    placeholder = "اختر التاريخ",
    min,
    max,
    startMonth,
    endMonth,
    ...props
}: DatePickerProps) {
    const [open, setOpen] = React.useState(false);

    // Normalize incoming date to ISO (yyyy-MM-dd) regardless of type
    const toIso = React.useCallback((input?: Date | string): string => {
        if (!input) {
            return "";
        }
        if (input instanceof Date) {
            return format(input, "yyyy-MM-dd");
        }
        if (typeof input === "string") {
            const parsed = parseISO(input);
            return isNaN(parsed.getTime()) ? "" : format(parsed, "yyyy-MM-dd");
        }
        return "";
    }, []);

    // Internal value so UI updates instantly (even if parent doesn't re-render)
    const [value, setValue] = React.useState<string>(toIso(date));
    React.useEffect(() => {
        setValue(toIso(date));
    }, [date, toIso]);

    const handleDateSelect = (newDate: Date | undefined) => {
        const iso = newDate ? format(newDate, "yyyy-MM-dd") : "";
        setValue(iso);                         // update UI immediately
        setDate?.(iso || undefined);             // notify parent (unchanged)
        setOpen(false);
    };

    const selectedDate = React.useMemo<Date | undefined>(() => {
        if (!value) return undefined;
        const parsed = parseISO(value);        // parse ISO safely
        return isNaN(parsed.getTime()) ? undefined : parsed;
    }, [value]);

    const normalizedMin = React.useMemo<Date | undefined>(() => {
        if (!min) return undefined;
        const d = typeof min === "string" ? parseISO(min) : min;
        return startOfDay(d);
    }, [min]);

    const normalizedMax = React.useMemo<Date | undefined>(() => {
        if (!max) return undefined;
        const d = typeof max === "string" ? parseISO(max) : max;
        return startOfDay(d);
    }, [max]);

    const isDisabled = (d: Date): boolean => {
        const target = startOfDay(d);
        if (normalizedMin && target < normalizedMin) return true;
        if (normalizedMax && target > normalizedMax) return true;
        return false;
    };

    const navigationAnchor = selectedDate ?? new Date();

    // Navigation bounds for the dropdown (NOT the selection constraints)
    const navigationStartMonth = React.useMemo<Date>(() => {
        if (normalizedMin) {
            return new Date(
                normalizedMin.getFullYear(),
                normalizedMin.getMonth(),
                1,
            );
        }
        if (startMonth) {
            return startMonth;
        }

        return new Date(navigationAnchor.getFullYear() - 50, 0, 1);
    }, [normalizedMin, startMonth, navigationAnchor]);

    const navigationEndMonth = React.useMemo<Date>(() => {
        if (normalizedMax) {
            return new Date(
                normalizedMax.getFullYear(),
                normalizedMax.getMonth(),
                1,
            );
        }
        if (endMonth) {
            return endMonth;
        }

        return new Date(navigationAnchor.getFullYear() + 10, 11, 1);
    }, [normalizedMax, endMonth, navigationAnchor]);


    const display = value
        ? format(parseISO(value), "yyyy-MM-dd", { locale: ar })
        : (placeholder ?? "اختر التاريخ");

    return (
        <>
            {/* Hidden input so <Form> serializes the date without changing your page code */}
            {name && <input type="hidden" name={name} value={value} />}

            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                    <button
                        type="button"
                        id={id}
                        data-slot="date-picker-trigger"
                        aria-invalid={hasError || undefined}
                        className={cn(
                            "flex h-10 w-full min-w-0 items-center justify-between gap-1.5 px-2.5 py-2 bg-transparent text-sm rounded-none border border-input text-start outline-none transition-colors",
                            "focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary/50",
                            "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
                            "[&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-3.5",
                            "aria-invalid:border-destructive aria-invalid:ring-destructive/30",
                            className,
                        )}
                    >
                        <span className={cn("truncate", value ? "font-mono" : "text-muted-foreground")}>
                            {display}
                        </span>
                        <CalendarIcon className="text-muted-foreground" />
                    </button>
                </PopoverTrigger>
                <PopoverContent className="p-0" align="start">
                    <Calendar
                        id={id}
                        className="w-full [&_.rdp-weeks]:font-mono [&_.rdp-week]:gap-1! [&_.rdp-weekdays]:gap-1!"
                        mode="single"
                        selected={selectedDate}
                        onSelect={handleDateSelect}
                        captionLayout="dropdown"
                        locale={ar}
                        disabled={isDisabled}
                        defaultMonth={selectedDate ?? navigationAnchor}
                        startMonth={navigationStartMonth}
                        endMonth={navigationEndMonth}
                        {...props}
                    />
                </PopoverContent>
            </Popover>
        </>
    );
}
